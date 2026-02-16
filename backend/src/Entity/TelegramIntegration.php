<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'telegram_integrations')]
#[ORM\UniqueConstraint(columns: ['shop_id'])]
#[ORM\HasLifecycleCallbacks] // PreUpdate
class TelegramIntegration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $botToken;

    #[ORM\Column(type: Types::TEXT)]
    private string $chatId;

    #[ORM\Column]
    private bool $enabled = true;
    
    // JoinColumn создает shop_id, делает его UNIQUE и NOT NULL
    #[ORM\OneToOne(inversedBy: 'telegramIntegration')]
    #[ORM\JoinColumn(name: 'shop_id', referencedColumnName: 'id', nullable: false)]
    private ?Shop $shop = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    
    public function getShop(): Shop { return $this->shop; }
    public function setShop(Shop $shop): static {
        $this->shop = $shop;
        // Устанавливаем обратную связь
        if ($shop !== null && $shop->getTelegramIntegration() !== $this) {
            $shop->setTelegramIntegration($this);
        }
        return $this;
    }

    public function getBotToken(): string { return $this->botToken; }
    public function setBotToken(string $botToken): static { $this->botToken = $botToken; return $this; } // проверка в контроллере через Telegram API

    public function getChatId(): string { return $this->chatId; }
    public function setChatId(string $chatId): static { $this->chatId = $chatId; return $this; }

    public function isEnabled(): bool { return $this->enabled; }
    public function setEnabled(bool $enabled): static { $this->enabled = $enabled; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    // Сеттер для createdAt обычно не нужен, так как он задается в конструкторе

    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }
    
}