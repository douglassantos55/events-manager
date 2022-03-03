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

class RemoveSupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->hasAttached(Supplier::factory(5), ['value' => 123])->create();
        $supplier = $event->suppliers->first();

        $response = $this->delete(route('suppliers.detach', [
            'event' => $event->id,
            'supplier' => $supplier->id,
        ]));

        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $event = Event::factory()->forUser()->hasAttached(Supplier::factory(5), ['value' => 123])->create();
        $supplier = $event->suppliers->first();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->delete(route('suppliers.detach', [
            'event' => $event->id,
            'supplier' => $supplier->id,
        ]));

        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        $event = Event::factory()->for($user)->hasAttached(Supplier::factory(5), ['value' => 123])->create();
        $supplier = $event->suppliers->first();

        Auth::login($user);

        $response = $this->delete(route('suppliers.detach', [
            'event' => $event->id,
            'supplier' => $supplier->id,
        ]));

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_remove_from_others_events()
    {
        $event = Event::factory()->forUser()->hasAttached(Supplier::factory(5), ['value' => 123])->create();
        $supplier = $event->suppliers->first();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->delete(route('suppliers.detach', [
            'event' => $event->id,
            'supplier' => $supplier->id,
        ]));

        $response->assertForbidden();
    }

    public function test_member_can_remove_from_parents_events()
    {
        $parent = User::factory()->create();

        $event = Event::factory()->for($parent)->hasAttached(Supplier::factory(5), ['value' => 123])->create();
        $supplier = $event->suppliers->first();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->delete(route('suppliers.detach', [
            'event' => $event->id,
            'supplier' => $supplier->id,
        ]));

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_remove_supplier_from_other_events()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        $event = Event::factory()->for($user)->hasAttached(Supplier::factory(5), ['value' => 123])->create();
        $other = Event::factory()->for($user)->hasAttached(Supplier::factory(5), ['value' => 123])->create();

        $supplier = $other->suppliers->first();

        Auth::login($user);

        $response = $this->delete(route('suppliers.detach', [
            'event' => $event->id,
            'supplier' => $supplier->id,
        ]));

        $response->assertNotFound();
    }

    public function test_cannot_remove_non_attached_supplier()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        $event = Event::factory()->for($user)->hasAttached(Supplier::factory(5), ['value' => 123])->create();
        $supplier = Supplier::factory()->create();

        Auth::login($user);

        $response = $this->delete(route('suppliers.detach', [
            'event' => $event->id,
            'supplier' => $supplier->id,
        ]));

        $response->assertNotFound();
    }

    public function test_removes_successfully()
    {
        $parent = User::factory()->create();

        $event = Event::factory()->for($parent)->hasAttached(Supplier::factory(5), ['value' => 123])->create();
        $supplier = $event->suppliers->first();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->delete(route('suppliers.detach', [
            'event' => $event->id,
            'supplier' => $supplier->id,
        ]));

        $this->assertFalse($event->refresh()->suppliers->contains($supplier));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }
}
