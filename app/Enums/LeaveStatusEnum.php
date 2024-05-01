<?php

namespace App\Enums;

enum LeaveStatusEnum: string
{

    case REJECT = 'Reject';
    case APPROVED = 'Approve';
    case PENDING = 'Pending';
}
