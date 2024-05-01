<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case ADMIN = 'Admin';
    case USER = 'User';
    case SALES = 'Sales and Marketing';
    case HR = 'HR';
    case MANAGER = 'Project Manager';
    case FINANCE = 'Finance';
    case INVENTORY = 'Inventory Manager';
    // case User = 'user';
}
