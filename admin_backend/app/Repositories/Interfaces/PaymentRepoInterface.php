<?php

namespace App\Repositories\Interfaces;

interface PaymentRepoInterface
{
    public function paginate($perPage, $array);
}
