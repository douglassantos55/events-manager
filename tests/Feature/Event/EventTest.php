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
        $category = SupplierCategory::factory()->create();
        $event = Event::factory()->forUser()->create();

        $event->categories()->attach($category, ['budget' => 1300]);
        $this->assertTrue($event->categories->contains($category));
    }

    public function test_add_supplier()
    {
        $supplier = Supplier::factory()->forCategory()->create();
        $event = Event::factory()->forUser()->create();

        $event->suppliers()->attach($supplier, ['value' => 530]);
        $this->assertTrue($event->suppliers->contains($supplier));
    }

    public function test_group_suppliers_by_category()
    {
        $event = Event::factory()->forUser()->create();
        $event->suppliers()->attach(Supplier::factory()->forCategory()->create(), ['value' => 100]);
        $event->suppliers()->attach(Supplier::factory()->forCategory()->create(), ['value' => 100]);
        $event->suppliers()->attach(Supplier::factory()->forCategory()->create(), ['value' => 100]);

        $this->assertCount(1, $event->getSuppliersFor(1));
    }
}
