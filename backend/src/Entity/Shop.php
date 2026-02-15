<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'shops')] // без этого создаст таблицу shop в единственном числе
class Shop {
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    // mappedBy указывает на свойство $shop в сущности TelegramIntegration
    #[ORM\OneToOne(mappedBy: 'shop', cascade: ['persist', 'remove'])]
    private  ?TelegramIntegration $telegramIntegration = null;

    public function __construct()
    {

    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getTelegramIntegration(): ?TelegramIntegration { return $this->telegramIntegration; }
}