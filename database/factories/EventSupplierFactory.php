<?php

namespace Database\Factories;

use App\Models\EventCategory;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventSupplier>
 */
class EventSupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'value' => $this->faker->randomFloat(),
            'status' => $this->faker->randomElement(['pending', 'hired']),
            'supplier_id' => Supplier::factory()->forCategory()->create()->id,
            'event_category_id' => EventCategory::factory()->create()->id,
        ];
    }
}
