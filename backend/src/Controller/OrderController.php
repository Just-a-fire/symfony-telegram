<?php

namespace App\Controller;

use App\Dto\OrderCreateDto;
use App\Entity\Shop;
use App\Entity\Order;
use App\Service\TelegramNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

final class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/OrderController.php',
        ]);
    }

    #[Route('/shops/{shopId}/orders', methods: ['POST'])]
    public function create(
        int $shopId,
        #[MapRequestPayload] OrderCreateDto $dto,
        EntityManagerInterface $em,
        TelegramNotificationService $tgService
    ): JsonResponse {
        $shop = $em->getRepository(Shop::class)->find($shopId);
        if (!$shop) return $this->json(['error' => 'Shop not found'], 404);

        $order = new Order();
        $order->setShop($shop);
        $order->setNumber($dto->number);
        $order->setTotal((string)$dto->total);
        $order->setCustomerName($dto->customerName);

        $em->persist($order);
        $em->flush();

        $deliveryStatus = 'skipped';
        $integration = $shop->getTelegramIntegration();

        if ($integration && $integration->isEnabled()) {
            $log = $tgService->sendOrderNotification($order);
            $deliveryStatus = $log->getStatus()->value;
        }

        return $this->json([
            'order' => [
                'id' => $order->getId(),
                'number' => $order->getNumber(),
                'total' => $order->getTotal(),
            ],
            'deliveryStatus' => $deliveryStatus
        ]);
    }
}
