<?php

namespace App\Repositories\UnitRepository;

use App\Models\Unit;
use App\Repositories\CoreRepository;

class UnitRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Unit::class;
    }

    /**
     * Get Units with pagination
     */
    public function unitsPaginate($perPage, $active = null, $array = [])
    {
       return $this->model()->with([
            'translation'
       ])
           ->when(isset($active), function ($q) use ($active) {
               $q->where('active', $active);
           })
           ->when(isset($array['search']), function ($q) use($array) {
               $q->whereHas('translations', function ($q) use($array) {
                   $q->where('title', 'LIKE', '%'. $array['search'] . '%');
               });
           })
           ->orderBy('id','desc')
           ->paginate($perPage);
    }

    /**
     * Get Unit by Identification
     */
    public function unitDetails(int $id)
    {
        return $this->model()->with([
            'translation'
        ])->find($id);
    }

}
