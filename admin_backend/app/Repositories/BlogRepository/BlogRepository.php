<?php

namespace App\Repositories\BlogRepository;

use App\Models\Blog;
use App\Repositories\CoreRepository;

class BlogRepository extends CoreRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Blog::class;
    }

    /**
     * Get brands with pagination
     */
    public function blogsPaginate($perPage, $active = null, $array = [])
    {
        return $this->model()
            ->whereHas('translation')
            ->with([
                'translation:id,locale,blog_id,title,short_desc'
            ])
            ->when(isset($array['type']), function ($q) use ($array) {
                $q->where('type', Blog::TYPES[$array['type']]);
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->when(isset($array['published_at']), function ($q) {
                $q->whereNotNull('published_at');
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Get brands with pagination
     */
    public function blogByUUID(string $uuid)
    {
        return $this->model()
            ->whereHas('translation')
            ->with('translation')
            ->firstWhere('uuid', $uuid);
    }
}
