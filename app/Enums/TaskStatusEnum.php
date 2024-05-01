<?php

namespace App\Enums;

enum TaskStatusEnum: string
{
    case TODO = 'TO DO';
    case PROGRESS = 'IN PROGRESS';
    case QA = 'QA';
    case DONE = 'DONE';
}
