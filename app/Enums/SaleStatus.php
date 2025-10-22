<?php

namespace App\Enums;

enum SaleStatus: int
{
    case Pending = 0;
    case Processing = 1;
    case Completed = 2;
    case Failed = 3;
}
