<?php

namespace App\Enums;

enum OrderStatusEnum
{
    case NEW;
    case IN_PROCESSING;
    case SENT;
    case DELIVERED;

}
