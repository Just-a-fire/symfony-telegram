<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

// use Symfony\Component\Validator\Constraints\NotBlank;

class TelegramConnectDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 10)]
        public readonly string $botToken,

        #[Assert\NotBlank]
        #[Assert\Type('digit', message: 'Chat ID должен состоять из цифр')]
        public readonly string $chatId,

        public readonly bool $enabled = true,
    ) {}
}
