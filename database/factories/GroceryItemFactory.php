<?php

namespace Database\Factories;

use App\Models\GroceryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroceryItem>
 */
class GroceryItemFactory extends Factory
{
    protected $model = GroceryItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word().' '.fake()->randomElement(['fruit', 'veg', 'grain', 'dairy']),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 20, 300),
            'stock' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
