<?php

namespace Test\Feature\Event\Supplier;

use App\Models\Installment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RemoveInstallmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $installment = Installment::factory()->forSupplier()->create();

        $route = route('installments.delete', ['installment' => $installment]);
        $response = $this->delete($route);

        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $installment = Installment::factory()->forSupplier()->create();
        $user = $installment->supplier->category->event->user;

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $route = route('installments.delete', ['installment' => $installment]);
        $response = $this->delete($route);

        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $installment = Installment::factory()->forSupplier()->create();
        $event = $installment->supplier->category->event;

        $user = $event->user;
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_INSTALLMENT],
        ]);

        Auth::login($user);

        $route = route('installments.delete', ['installment' => $installment]);
        $response = $this->delete($route);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_member_can_remove_from_parents()
    {
        $installment = Installment::factory()->forSupplier()->create();
        $event = $installment->supplier->category->event;
        $parent = $event->user;

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_INSTALLMENT],
        ]);

        Auth::login($user);

        $route = route('installments.delete', ['installment' => $installment]);
        $response = $this->delete($route);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_remove_from_other_users_events()
    {
        $installment = Installment::factory()->forSupplier()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_INSTALLMENT],
        ]);

        Auth::login($user);

        $route = route('installments.delete', ['installment' => $installment]);
        $response = $this->delete($route);

        $response->assertForbidden();
    }

    public function test_deletes_successfully()
    {
        $installment = Installment::factory()->forSupplier()->create();
        $event = $installment->supplier->category->event;

        $user = $event->user;
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_INSTALLMENT],
        ]);

        Auth::login($user);

        $route = route('installments.delete', ['installment' => $installment]);
        $this->delete($route);

        $this->assertModelMissing($installment);
    }
}
