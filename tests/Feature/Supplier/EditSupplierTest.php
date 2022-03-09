<?php

namespace Test\Feature\Supplier;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditSupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        $response = $this->put(route('suppliers.update', [
            'event' => $event->id,
            'category' => $category->id,
            'supplier' => $supplier->id,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();

        $event = Event::factory()->for($user)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event->id,
            'category' => $category->id,
            'supplier' => $supplier->id,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();

        $event = Event::factory()->for($user)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event->id,
            'category' => $category->id,
            'supplier' => $supplier->id,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_edit_other_users_events_suppliers()
    {
        $event = Event::factory()->forUser()->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event->id,
            'category' => $category->id,
            'supplier' => $supplier->id,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_edit_supplier_not_attached_to_event()
    {
        $user = User::factory()->create();

        $event = Event::factory()->for($user)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = Supplier::factory()->forCategory()->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event->id,
            'category' => $category->id,
            'supplier' => $supplier->id,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertNotFound();
    }

    public function test_member_can_edit_parents_suppliers()
    {
        $parent = User::factory()->create();

        $event = Event::factory()->for($parent)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event->id,
            'category' => $category->id,
            'supplier' => $supplier->id,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_edit_from_non_existing_event()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => 535,
            'category' => 539,
            'supplier' => 690,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertNotFound();
    }

    public function test_cannot_edit_non_existing_supplier()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $event = Event::factory()->for($user)->create();
        $categories = EventCategory::factory(3)->for($event)->create();

        $category = $categories->first();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event->id,
            'category' => $category->id,
            'supplier' => 1350,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertNotFound();
    }

    public function test_validation()
    {
        $user = User::factory()->create();

        $event = Event::factory()->for($user)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event->id,
            'category' => $category->id,
            'supplier' => $supplier->id,
        ]), [
            'value' => '69,99',
            'status' => 'breathing',
        ]);

        $response->assertInvalid([
            'value' => 'The value must be a number.',
            'status' => 'The selected status is invalid.',
        ]);
    }

    public function test_updates_successfully()
    {
        $user = User::factory()->create();

        $event = Event::factory()->for($user)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->last();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event->id,
            'category' => $category->id,
            'supplier' => $supplier->id,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $category->refresh();

        $this->assertEquals(69, $category->suppliers->last()->value);
        $this->assertEquals('hired', $category->suppliers->last()->status);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_file_upload()
    {
        Storage::fake();
        $user = User::factory()->create();

        $event = Event::factory()->for($user)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->last();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $files = [
            UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->create('agreement.pdf', 150, 'application/pdf'),
        ];

        $this->put(route('suppliers.update', [
            'event' => $event->id,
            'category' => $category->id,
            'supplier' => $supplier->id,
        ]), [
            'value' => 42069,
            'status' => 'hired',
            'contract' => $files,
        ]);

        $supplier->refresh();

        Storage::assertExists('contracts/' . $files[0]->hashName());
        Storage::assertExists('contracts/' . $files[1]->hashName());

        $this->assertEquals(2, $supplier->files->count());
    }
}
