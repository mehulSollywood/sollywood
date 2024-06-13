<?php

namespace App\Services\ShopServices;

use App\Helpers\ResponseError;
use App\Models\Shop;
use App\Services\CoreService;

class ShopInvitationService extends CoreService
{
    protected function getModelClass(): string
    {
        return Shop::class;
    }
}
