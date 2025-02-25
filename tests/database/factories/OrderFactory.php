<?php

namespace Elegant\Media\Tests\Database\Factories;

use Elegant\Media\Tests\Fixtures\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product' => $this->faker->words(2, true),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
