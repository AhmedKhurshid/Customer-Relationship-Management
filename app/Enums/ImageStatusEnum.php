<?php

namespace App\Enums;

enum ImageStatusEnum: string
{

    case REJECT = 'Reject';
    case APPROVED = 'Approve';
    case PENDING = 'Pending';
}
