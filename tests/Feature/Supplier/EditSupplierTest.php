<?php

namespace Test\Feature\Supplier;

use App\Models\Event;
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
        $event = Event::factory()->forUser()->hasAttached(Supplier::factory(), ['value' => 315])->create();
        $supplier = $event->suppliers->first();

        $response = $this->put(route('suppliers.update', [
            'event' => $event,
            'supplier' => $supplier,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();

        $event = Event::factory()->for($user)->hasAttached(Supplier::factory(), ['value' => 315])->create();
        $supplier = $event->suppliers->first();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event,
            'supplier' => $supplier,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();

        $event = Event::factory()->for($user)->hasAttached(Supplier::factory(), ['value' => 315])->create();
        $supplier = $event->suppliers->first();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event,
            'supplier' => $supplier,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_edit_other_users_events_suppliers()
    {
        $event = Event::factory()->forUser()->hasAttached(Supplier::factory(), ['value' => 315])->create();
        $supplier = $event->suppliers->first();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event,
            'supplier' => $supplier,
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
        $supplier = Supplier::factory()->forCategory()->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event,
            'supplier' => $supplier,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $response->assertNotFound();
    }

    public function test_member_can_edit_parents_suppliers()
    {
        $parent = User::factory()->create();

        $event = Event::factory()->for($parent)->hasAttached(Supplier::factory(), ['value' => 456])->create();
        $supplier = $event->suppliers->first();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event,
            'supplier' => $supplier,
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

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event,
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
        $event = Event::factory()->for($user)->hasAttached(Supplier::factory(), ['value' => 456])->create();
        $supplier = $event->suppliers->first();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event->id,
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
        $event = Event::factory()->for($user)->hasAttached(Supplier::factory(5), ['value' => 456])->create();
        $supplier = $event->suppliers->last();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->put(route('suppliers.update', [
            'event' => $event->id,
            'supplier' => $supplier->id,
        ]), [
            'value' => 69,
            'status' => 'hired',
        ]);

        $event->refresh();

        $this->assertEquals(69, $event->suppliers->last()->pivot->value);
        $this->assertEquals('hired', $event->suppliers->last()->pivot->status);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_file_upload()
    {
        Storage::fake();

        $user = User::factory()->create();
        $event = Event::factory()->for($user)->hasAttached(Supplier::factory(5), ['value' => 456])->create();
        $supplier = $event->suppliers->last();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $files = [
            UploadedFile::fake()->create('contract.pdf'),
            UploadedFile::fake()->create('agreement.pdf'),
        ];

        $this->put(route('suppliers.update', [
            'event' => $event->id,
            'supplier' => $supplier->id,
        ]), [
            'value' => 69,
            'status' => 'hired',
            'contract' => $files,
        ]);

        Storage::assertExists('contracts/' . $files[0]->hashName());
        Storage::assertExists('contracts/' . $files[1]->hashName());
    }
}
