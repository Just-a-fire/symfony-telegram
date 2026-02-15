<?php

namespace App\Enum;

enum SendStatus: string {
    case SENT = 'SENT';
    case FAILED = 'FAILED';
}