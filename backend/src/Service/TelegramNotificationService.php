<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\TelegramSendLog;
use App\Enum\SendStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TelegramNotificationService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private EntityManagerInterface $em
    ) {}

    public function sendOrderNotification(Order $order): ?TelegramSendLog
    {
        $existingLog = $this->em->getRepository(TelegramSendLog::class)
            ->findOneBy(['shop' => $order->getShop(), 'order' => $order]);
        
        if ($existingLog) return $existingLog;

        $shop = $order->getShop();
        $integration = $shop->getTelegramIntegration();

        if (!$integration || !$integration->isEnabled()) {
            return null;
        }

        $text = "Новый заказ: #{$order->getNumber()}\nСумма: {$order->getTotal()}\nКлиент: {$order->getCustomerName()}";
        $url = "https://api.telegram.org/bot{$integration->getBotToken()}/sendMessage";

        try {
            $response = $this->httpClient->request('POST', $url, [
                'json' => [
                    'chat_id' => $integration->getChatId(),
                    'text' => $text,
                ]
            ]);

            $log = new TelegramSendLog();
            $log->setShop($shop);
            $log->setOrder($order);
            $log->setMessage($text);

            if ($response->getStatusCode() === 200) {
                $log->setStatus(SendStatus::SENT);
            } else {
                $log->setStatus(SendStatus::FAILED);
                $log->setError($response->getContent(false));
            }
        } catch (\Exception $e) {
            $log = ($log ?? new TelegramSendLog())
                ->setShop($shop)
                ->setOrder($order)
                ->setMessage($text)
                ->setStatus(SendStatus::FAILED)
                ->setError($e->getMessage());
        }

        $this->em->persist($log);
        $this->em->flush();
        
        return $log;
    }
}
