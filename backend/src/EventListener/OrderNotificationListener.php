<?php

namespace App\EventListener;

use App\Entity\Order;
use App\Service\TelegramNotificationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Order::class)]
class OrderNotificationListener
{
    public function __construct(
        private TelegramNotificationService $notificationService
    ) {}

    public function postPersist(Order $order): void
    {
        // Если отправка упадет, транзакция заказа уже зафиксирована, данные не пропадут
        $this->notificationService->sendOrderNotification($order);
    }
}
