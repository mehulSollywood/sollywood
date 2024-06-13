<?php

namespace App\Repositories\TicketRepository;

use App\Models\Ticket;
use App\Repositories\CoreRepository;

class TicketRepository extends CoreRepository
{

    protected function getModelClass(): string
    {
        return Ticket::class;
    }

    public function paginate($perPage, $array = [])
    {
        return $this->model()->with('children')
            ->when(isset($array['created_by']), function ($q) use($array) {
                $q->where('created_by', $array['created_by']);
            })
            ->where('parent_id', 0)
            ->filter($array)
            ->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc')
            ->paginate($perPage);
    }

    public function ticketDetails($id)
    {
        return $this->model()->with('children')->find($id);
    }
}
