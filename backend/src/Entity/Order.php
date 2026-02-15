<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Shop::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Shop $shop;

    #[ORM\Column(type: Types::TEXT)]
    private string $number;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $total;

    #[ORM\Column(type: Types::TEXT)]
    private string $customerName;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getNumber(): string { return $this->number; }
    public function setNumber(string $number): self { $this->number = $number; return $this; }

    public function getTotal(): string { return $this->total; }
    public function setTotal(string $total): self { $this->total = $total; return $this; }

    public function getCustomerName(): string { return $this->customerName; }
    public function setCustomerName(string $customerName): self {
        $this->customerName = $customerName;
        return $this;
    }

    public function getShop(): Shop { return $this->shop; }
    public function setShop(Shop $shop): self { $this->shop = $shop; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
