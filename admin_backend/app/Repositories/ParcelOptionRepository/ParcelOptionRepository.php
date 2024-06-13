<?php

namespace App\Repositories\ParcelOptionRepository;

use App\Models\Language;
use App\Models\ParcelOption;
use App\Repositories\CoreRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Schema;

class ParcelOptionRepository extends CoreRepository
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return ParcelOption::class;
    }

    /**
     * @param array $filter
     * @return Paginator
     * @throws Exception
     */
    public function paginate(array $filter = []): Paginator
    {
        /** @var ParcelOption $model */
        $model  = $this->model();
        $column = data_get($filter, 'column', 'id');

        if (!Schema::hasColumn('parcel_options', $column)) {
            $column = 'id';
        }

        $locale = data_get(Language::languagesList()->where('default', 1)->first(), 'locale');

        return $model
            ->filter($filter)
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->language)->orWhere('locale', $locale)
            ])
            ->orderBy($column, data_get($filter, 'sort', 'desc'))
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    public function show(int $id): mixed
    {

        $locale = data_get(Language::languagesList()->where('default', 1)->first(), 'locale');

        return $this->model
            ->with([
                'translations',
                'translation' => fn($q) => $q->where('locale', $this->language)->orWhere('locale', $locale)
            ])->find($id);
    }

    /**
     * @param int $id
     * @return ParcelOption|null
     * @throws Exception
     */
    public function showById(int $id): ?ParcelOption
    {
        $parcelOption = ParcelOption::find($id);

        $locale = data_get(Language::languagesList()->where('default', 1)->first(), 'locale');

        return $parcelOption?->loadMissing([
            'translations',
            'translation' => fn($q) => $q->where('locale', $this->language)->orWhere('locale', $locale)
        ]);
    }

}
