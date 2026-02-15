<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class OrderCreateDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2)]
        public readonly string $number,

        #[Assert\NotBlank]
        #[Assert\Type('numeric', message: 'Значение должно быть числом')]
        public readonly string $total,

        #[Assert\NotBlank]
        #[Assert\Length(min: 3)]
        public readonly string $customerName,
    ) {}
}
