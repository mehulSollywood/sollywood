<?php

namespace App\Services\Interfaces;

interface LanguageServiceInterface
{
    public function create($collection);

    public function update(int $id, $collection);

    public function setLanguageDefault(int $id = null, int $default = null);
}
