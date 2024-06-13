<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'keywords' => $this->faker->colorName(),
            'parent_id' => 0,
            'type' => 1,
            'img' => $this->faker->image(),
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
