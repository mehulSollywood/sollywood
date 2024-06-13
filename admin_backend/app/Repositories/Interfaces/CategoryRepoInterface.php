<?php

namespace App\Repositories\Interfaces;

interface CategoryRepoInterface
{

    public function parentCategories($perPage, $active = null, array $array = []);

    public function selectPaginate($perPage, $active = null, array $array = []);

    public function categoriesList(array $array = []);

    public function categoriesPaginate(int $perPage, $active = null, array $array = []);

    public function categoryDetails(int $id);

    public function categoryByUuid(string $uuid);

    public function categoriesSearch(string $search, $active = null, $shop_id = null);
}
