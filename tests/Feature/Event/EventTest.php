<?php

namespace Test\Feature\Event;

use App\Models\Event;
use App\Models\Supplier;
use App\Models\SupplierCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_category()
    {
        $event = Event::factory()->forUser()->create();
        $category = SupplierCategory::factory()->create();

        $eventCategory = $event->categories()->create([
            'budget' => 355,
            'category_id' => $category->id,
        ]);

        $this->assertTrue($event->categories->contains($eventCategory));
    }

    public function test_add_supplier()
    {
        $event = Event::factory()->forUser()->create();
        $supplier = Supplier::factory()->forCategory()->create();

        $eventCategory = $event->categories()->create([
            'budget' => 355,
            'category_id' => $supplier->category->id,
        ]);

        $eventSupplier = $eventCategory->suppliers()->create([
            'value' => 1355,
            'supplier_id' => $supplier->id,
        ]);

        $this->assertTrue($eventCategory->suppliers->contains($eventSupplier));
    }
}
