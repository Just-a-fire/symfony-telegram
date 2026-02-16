<?php

namespace App\Controller;

use App\Service\TelegramNotificationService;
use App\Dto\TelegramConnectDto;
use App\Entity\Shop;
use App\Entity\Order;
use App\Entity\TelegramIntegration;
use App\Entity\TelegramSendLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class TelegramController extends AbstractController
{
    #[Route('/shops/{shopId}/telegram/connect', methods: ['POST'])]
    public function connect(
        int $shopId,
        #[MapRequestPayload] TelegramConnectDto $dto,
        EntityManagerInterface $em,
        HttpClientInterface $httpClient 
    ): JsonResponse {
        // Проверяем токен через Telegram API
        try {
            $response = $httpClient->request('GET', "https://api.telegram.org/bot{$dto->botToken}/getMe");
            
            if ($response->getStatusCode() !== 200) {
                // Отключение исключений при ошибках HTTP
                return $this->json([
                    'status' => 'not_connected',
                    'error' => 'Invalid Telegram Bot Token',
                    'details' => $response->toArray(false)
                ], 400);
            }
        } catch (\Exception $e) {
            return $this->json(['status' => 'not_connected', 'error' => 'Could not verify token with Telegram'], 502);
        }
        
        $shop = $em->getRepository(Shop::class)->find($shopId);
        if (!$shop) {
            return $this->json(['status' => 'not_connected', 'error' => 'Shop not found'], 404);
        }
        
        $integration = $shop->getTelegramIntegration() ?? new TelegramIntegration();
        $integration->setShop($shop);
        $integration->setBotToken($dto->botToken);
        $integration->setChatId($dto->chatId);
        $integration->setEnabled($dto->enabled);

        $em->persist($integration);
        $em->flush();

        return $this->json([
            'status' => 'connected',
            'bot_info' => $response->toArray()['result'] // Возвращаем инфо о боте
        ]);
    }
    
    #[Route('/shops/{shopId}/telegram/status', methods: ['GET'])]
    public function status(int $shopId, EntityManagerInterface $em): JsonResponse 
    {
        $shop = $em->getRepository(Shop::class)->find($shopId);
        $tg = $shop?->getTelegramIntegration();

        if (!$tg) return $this->json(['error' => 'Telegram Integration is not configured'], 404);

        $sevenDaysAgo = new \DateTimeImmutable('-7 days');

        $stats = $em->getRepository(TelegramSendLog::class)->createQueryBuilder('l')
            ->select('SUM(CASE WHEN l.status = :sent THEN 1 ELSE 0 END) as sent')
            ->addSelect('SUM(CASE WHEN l.status = :failed THEN 1 ELSE 0 END) as failed')
            ->addSelect('MAX(l.sentAt) as lastSent')
            ->where('l.shop = :shop')
            ->andWhere('l.sentAt >= :date')
            ->setParameter('shop', $shop)
            ->setParameter('date', $sevenDaysAgo)
            ->setParameter('sent', \App\Enum\SendStatus::SENT->value)
            ->setParameter('failed', \App\Enum\SendStatus::FAILED->value)
            ->getQuery()->getSingleResult();

        return $this->json([
            'enabled' => $tg->isEnabled(),
            'chatId' => substr($tg->getChatId(), 0, 3) . '***' . substr($tg->getChatId(), -2),
            'lastSentAt' => $stats['lastSent'],
            'sentCount' => (int)$stats['sent'],
            'failedCount' => (int)$stats['failed'],
        ]);
    }

    #[Route('/shops/{shopId}/telegram/logs', methods: ['GET'])]
    public function getLogs(int $shopId, EntityManagerInterface $em): JsonResponse 
    {
        $logs = $em->getRepository(TelegramSendLog::class)->findBy(
            ['shop' => $shopId],
            ['sentAt' => 'DESC'],
            10 // последние 10 записей
        );

        return $this->json($logs, 200, [],  [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) { // исправляем циклические ссылки
                return $object->getId();
            },
        ]);
    }
}
