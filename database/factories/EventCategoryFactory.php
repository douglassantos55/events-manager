<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\SupplierCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventCategory>
 */
class EventCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'budget' => $this->faker->randomFloat(),
            'category_id' => SupplierCategory::factory()->create()->id,
            'event_id' => Event::factory()->forUser()->create()->id,
        ];
    }
}
