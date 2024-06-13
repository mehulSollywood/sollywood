<?php

namespace App\Repositories\GroupRepository;

use App\Models\Group;
use App\Repositories\CoreRepository;

class GroupRepository extends CoreRepository
{


    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Group::class;
    }

    public function paginate($perPage, $active = null)
    {
        return $this->model()
            ->with('translation')
            ->whereHas('translation')
            ->when(isset($active), function ($q) {
                $q->where('status', true);
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function show(int $id)
    {
        return $this->model()->with([
            'translation',
            'translations'
        ])
            ->find($id);
    }


}
