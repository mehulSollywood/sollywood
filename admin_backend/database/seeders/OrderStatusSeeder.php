<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            [
                'id' => 1,
                'name' => 'new',
                'active' => true,
                'sort' => 1,
            ],
            [
                'id' => 2,
                'name' => 'accepted',
                'active' => true,
                'sort' => 2,
            ],
            [
                'id' => 3,
                'name' => 'ready',
                'active' => true,
                'sort' => 3,
            ],
            [
                'id' => 4,
                'name' => 'on_a_way',
                'active' => true,
                'sort' => 4,
            ],
            [
                'id' => 5,
                'name' => 'delivered',
                'active' => true,
                'sort' => 5,
            ],
            [
                'id' => 6,
                'name' => 'canceled',
                'active' => true,
                'sort' => 6,
            ],
        ];

        foreach ($statuses as $status){
            OrderStatus::updateOrInsert(['id' => $status['id']],$status);
        }
    }
}
