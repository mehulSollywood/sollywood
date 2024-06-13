<?php

namespace App\Repositories\ShopRepository;

use App\Models\Shop;
use App\Repositories\CoreRepository;
use App\Repositories\Interfaces\ShopRepoInterface;

class ShopRepository extends CoreRepository implements ShopRepoInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Shop::class;
    }

    /**
     * Get all Shops from table
     */
    public function shopsList(array $array = [])
    {
        return $this->model()
            ->filter($array)
            ->with([
                'translation',
                'seller.roles',
                'seller:id,firstname,lastname'
            ])->orderByDesc('id')->orderByDesc('updated_at')->get();
    }

    /**
     * Get one Shop by UUID
     * @param int $perPage
     * @param array $array
     * @return mixed
     */
    public function shopsPaginate(int $perPage, array $array = []): mixed
    {
        return $this->model()
            ->withAvg('reviews', 'rating')
            ->whereHas('translation')
            ->filter($array)
            ->with([
                'workingDays',
                'closedDates',
                'translation:id,locale,title,description,shop_id,address',
                'seller.roles',
                'seller:id,firstname,lastname',
            ])
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * @param string $uuid
     * @return mixed
     */
    public function shopDetails(string $uuid): mixed
    {
        return $this->model()->query()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->whereHas('translation')
            ->with([
                'translation',

                'subscription',

                'seller.roles',

                'shopPayments.payment',

                'deliveries.translation',

                'deliveries.translations',

                'seller',

                'branches.translation',

                'tags:id,img',

                'tags.translation',

                'workingDays',

                'closedDates',

                'group.translation'
            ])
            ->firstWhere('uuid', $uuid);
    }

    /**
     * @param string $slug
     * @return mixed
     */
    public function showBySlug(string $slug): mixed
    {
        return $this->model()->query()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->whereHas('translation')
            ->with([
                'translation',

                'subscription',

                'seller.roles',

                'shopPayments.payment',

                'deliveries.translation',

                'deliveries.translations',

                'seller',

                'branches.translation',

                'tags:id,img',

                'tags.translation',

                'workingDays',

                'closedDates',

                'group.translation'
            ])
            ->firstWhere('slug', $slug);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function shopById($id): mixed
    {
        return $this->model()->query()
            ->withAvg('reviews', 'rating')
            ->whereHas('translation')
            ->with([
                'workingDays',
                'closedDates',
                'translation',
                'subscription',
                'seller.roles',
                'seller:id,firstname,lastname',
                'branches.translation'
            ])->find($id);
    }

    /**
     * @param string $search
     * @param array $array
     * @return mixed
     */
    public function shopsSearch(string $search, $array = []): mixed
    {
        return $this->model()->with([
            'translation',
            'workingDays',
            'closedDates',
        ])
            ->withAvg('reviews', 'rating')
            ->where('phone', 'LIKE', '%' . $search . '%')
            ->orWhereHas('translations', function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->select('id', 'shop_id', 'locale', 'title');
            })
            ->filter($array)
            ->latest()->take(10)->get();
    }

    /**
     * @param array $ids
     * @param null $status
     * @return mixed
     */
    public function shopsByIDs(array $ids = [], $status = null): mixed
    {
        return $this->model()->with([
            'translation',
            'deliveries.translation',
            'workingDays',
            'closedDates',
        ])
            ->when(isset($status), function ($q) use ($status) {
                $q->where('status', $status);
            })->find($ids);
    }

}
