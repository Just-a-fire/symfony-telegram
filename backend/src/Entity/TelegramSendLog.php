<?php

namespace App\Entity;

use App\Enum\SendStatus;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: 'telegram_send_log')]
#[ORM\UniqueConstraint(name: 'uniq_shop_order', columns: ['shop_id', 'order_id'])]
class TelegramSendLog
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Shop $shop;

    #[ORM\OneToOne] // OneToOne, так как по нашей схеме unique(shop, order)
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

    #[ORM\Column(type: 'send_status_enum')]
    private SendStatus $status;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $error = null;

    #[ORM\Column]
    private \DateTimeImmutable $sentAt;

    public function __construct() {
        $this->sentAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getShop(): Shop { return $this->shop; }
    public function setShop(Shop $shop): self { $this->shop = $shop; return $this; }

    public function getOrder(): Order { return $this->order; }
    public function setOrder(Order $order): self { $this->order = $order; return $this; }

    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): self { $this->message = $message; return $this; }

    public function getStatus(): SendStatus { return $this->status; }
    public function setStatus(SendStatus $status): self { $this->status = $status; return $this; }

    public function getError(): string { return $this->error; }
    public function setError(string $error): self { $this->error = $error; return $this; }

    public function getSendAt(): \DateTimeImmutable { return $this->sentAt; }

}
