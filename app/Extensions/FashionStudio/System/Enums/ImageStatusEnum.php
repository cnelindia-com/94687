<?php

namespace App\Extensions\FashionStudio\System\Enums;

enum ImageStatusEnum: string
{
    case pending = 'PENDING';
    case processing = 'PROCESSING';
    case completed = 'COMPLETED';
    case failed = 'FAILED';
}
