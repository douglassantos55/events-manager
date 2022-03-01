<?php

namespace Test\Feature\Supplier;

use App\Models\Event;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AddSupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->create();
        $response = $this->post(route('suppliers.attach', ['event' => $event]), [
            'value' => 4699,
        ]);
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);
        $supplier = Supplier::factory()->forCategory()->create();

        $response = $this->post(route('suppliers.attach', ['event' => $event]), [
            'value' => 4699,
            'supplier' => $supplier->id,
        ]);

        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->forCategory()->create();

        $event = Event::factory()->for($user)->create();
        $event->categories()->attach($supplier->category, ['budget' => 134]);

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->post(route('suppliers.attach', ['event' => $event]), [
            'value' => 4699,
            'supplier' => $supplier->id,
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_add_to_other_users_events()
    {
        $event = Event::factory()->forUser()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);
        $supplier = Supplier::factory()->forCategory()->create();

        $response = $this->post(route('suppliers.attach', ['event' => $event]), [
            'value' => 4699,
            'supplier' => $supplier->id,
        ]);

        $response->assertForbidden();
    }

    public function test_member_can_add_to_parents_events()
    {
        $parent = User::factory()->create();
        $supplier = Supplier::factory()->forCategory()->create();

        $event = Event::factory()->for($parent)->create();
        $event->categories()->attach($supplier->category, ['budget' => 123]);

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->post(route('suppliers.attach', ['event' => $event]), [
            'value' => 4699,
            'supplier' => $supplier->id,
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_adds_successfully()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->forCategory()->create();

        $event = Event::factory()->for($user)->create();
        $event->categories()->attach($supplier->category, ['budget' => 465]);

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);


        $response = $this->post(route('suppliers.attach', ['event' => $event]), [
            'value' => 69,
            'supplier' => $supplier->id,
        ]);

        $this->assertTrue($event->refresh()->suppliers->contains($supplier));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_ignores_duplicated()
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->forCategory()->create();

        $event = Event::factory()->for($user)->create();
        $event->categories()->attach($supplier->category->id, ['budget' => 420]);
        $event->suppliers()->attach($supplier, ['value' => 520]);

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->post(route('suppliers.attach', ['event' => $event]), [
            'value' => 69,
            'supplier' => $supplier->id,
        ]);

        $this->assertCount(1, $event->refresh()->suppliers->all());
        $this->assertTrue($event->refresh()->suppliers->contains($supplier));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_add_non_existing_supplier()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->post(route('suppliers.attach', ['event' => $event]), [
            'value' => 69,
            'supplier' => 69,
        ]);

        $response->assertInvalid([
            'supplier' => 'The selected supplier is invalid.',
        ]);
    }

    public function test_cannot_add_supplier_from_category_not_assigned_to_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);
        $supplier = Supplier::factory()->forCategory()->create();

        $response = $this->post(route('suppliers.attach', ['event' => $event]), [
            'value' => 69420,
            'supplier' => $supplier->id,
        ]);

        $response->assertInvalid([
            'supplier' => 'The selected supplier is invalid.',
        ]);
    }
}
