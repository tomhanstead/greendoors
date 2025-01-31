<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word,
        ];
    }

    /**
     * Custom relationship method for assigning predefined products.
     */
    public function hasProducts(int $count, array $products)
    {
        return $this->has(
            Product::factory()
                ->count($count)
                ->sequence(
                    fn ($sequence) => ['name' => $products[$sequence->index]]
                )
        );
    }
}
