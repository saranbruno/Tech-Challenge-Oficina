<?php

namespace App\Domain\Inventory\Enums;

enum InventoryItemType: string
{
    case Part = 'part';
    case Supply = 'supply';
}
