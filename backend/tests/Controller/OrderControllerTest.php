<?php

namespace App\Tests\Controller;

use App\Entity\Shop;
use App\Entity\TelegramIntegration;
use App\Entity\TelegramSendLog;
use App\Enum\SendStatus;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OrderControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        if (empty($_ENV['BOT_TOKEN']) || empty($_ENV['CHAT_ID'])) {
            $this->markTestSkipped('Пропуск: BOT_TOKEN и CHAT_ID не настроены в .env.test или .env.test.local');
        }

        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        
        // Очистка БД перед каждым тестом (в идеале использовать DAMADoctrineTestBundle)
        $this->entityManager->createQuery('DELETE FROM App\Entity\TelegramSendLog')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Order')->execute();
    }

    private function createTestShopWithIntegration(): Shop
    {
        $shop = new Shop();
        $shop->setName('Test Shop');
        $this->entityManager->persist($shop);

        $ti = new TelegramIntegration();
        $ti->setShop($shop);
        $ti->setBotToken($_ENV['BOT_TOKEN']);
        $ti->setChatId($_ENV['CHAT_ID']);
        $ti->setEnabled(true);
        $this->entityManager->persist($ti);
        
        $this->entityManager->flush();
        return $shop;
    }

    /**
     * Тест 1: Успешная отправка и лог SENT
     */
    public function testOrderCreationSendsTelegramAndLogsSent(): void
    {
        $shop = $this->createTestShopWithIntegration();
        
        // Мокаем успешный ответ Telegram
        $mockResponse = new MockResponse(json_encode(['ok' => true]));
        $this->client->getContainer()->set(HttpClientInterface::class, new MockHttpClient($mockResponse));

        $payload = json_encode(['number' => 'A-1012', 'total' => '1000', 'customerName' => 'Test User']);

        $this->client->request('POST', "/shops/{$shop->getId()}/orders", [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        $this->assertResponseIsSuccessful();
        
        // Проверяем наличие лога в БД
        $log = $this->entityManager->getRepository(TelegramSendLog::class)->findOneBy(['shop' => $shop]);
        $this->assertNotNull($log);
        $this->assertEquals(SendStatus::SENT, $log->getStatus());
    }

    /**
     * Тест 2: Идемпотентность (нет дублей логов и повторных отправок)
     */
    public function testOrderIdempotencyNoDuplicateLogs(): void
    {
        $shop = $this->createTestShopWithIntegration();
        
        // Мокаем ответ (ожидаем только один вызов)
        $mockResponse = new MockResponse(json_encode(['ok' => true]));
        $mockHttpClient = new MockHttpClient([$mockResponse, $mockResponse]); // второй ответ не должен понадобиться
        $this->client->getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        $payload = json_encode(['number' => 'IDEM-1', 'total' => '5000', 'customerName' => 'User']);

        // Первый запрос
        $this->client->request('POST', "/shops/{$shop->getId()}/orders", [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        $orderId = json_decode($this->client->getResponse()->getContent(), true)['order']['id'];

        // Второй запрос (эмуляция повтора) вручную через сервис, так как контроллер создаст НОВЫЙ заказ с новым ID. 
        // Если идемпотентность завязана на пару (shop, order), то повторный вызов сервиса для того же заказа не должен создать лог.
        $order = $this->entityManager->getRepository(\App\Entity\Order::class)->find($orderId);
        $tgService = $this->client->getContainer()->get(\App\Service\TelegramNotificationService::class);
        $tgService->sendOrderNotification($order);

        $logs = $this->entityManager->getRepository(TelegramSendLog::class)->findBy(['order' => $order]);
        $this->assertCount(1, $logs, 'Должен быть только один лог для одного заказа');
    }

    /**
     * Тест 3: Ошибка Telegram API -> лог FAILED, но заказ создан
     */
    public function testTelegramErrorLogsFailedButOrderExists(): void
    {
        $shop = $this->createTestShopWithIntegration();

        $mockResponse = new MockResponse(json_encode(['ok' => false]), [
            'status_code' => 401,
            'http_code' => 401 // Для уверенности в некоторых версиях
        ]);

        $mockHttpClient = new MockHttpClient($mockResponse);
        $this->client->getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        $payload = json_encode(['number' => 'ERR-404', 'total' => '2000', 'customerName' => 'Bad Token User']);

        $this->client->request('POST', "/shops/{$shop->getId()}/orders", [], [], ['CONTENT_TYPE' => 'application/json'], $payload);

        // очищаем EntityManager, чтобы он перечитал данные из реальной БД, а не брал их из своего внутреннего кэша
        $this->entityManager->clear();

        // Заказ должен быть создан (200 или 201 от нашего API)
        $this->assertResponseIsSuccessful();
        
        // Проверяем лог со статусом FAILED
        $log = $this->entityManager->getRepository(TelegramSendLog::class)->findOneBy(['shop' => $shop]);
        $this->assertEquals(SendStatus::FAILED, $log->getStatus());
        $this->assertNotNull($log->getError());
    }
}
