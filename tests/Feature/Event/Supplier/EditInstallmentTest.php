<?php

namespace Test\Feature\Event\Supplier;

use App\Models\Installment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EditInstallmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $installment = Installment::factory()->forSupplier()->create();
        $route = route('installments.update', ['installment' => $installment]);

        $response = $this->put($route, ['value' => 100, 'status' => 'paid']);
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $installment = Installment::factory()->forSupplier()->create();
        $route = route('installments.update', ['installment' => $installment]);

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->put($route, ['value' => 100, 'status' => 'paid']);
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $installment = Installment::factory()->forSupplier()->create();
        $event = $installment->supplier->category->event;

        $user = $event->user;
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('installments.update', ['installment' => $installment]);
        $response = $this->put($route, ['value' => 100, 'status' => 'paid']);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_member_can_edit_from_parents_events()
    {
        $installment = Installment::factory()->forSupplier()->create();
        $event = $installment->supplier->category->event;
        $parent = $event->user;

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('installments.update', ['installment' => $installment]);
        $response = $this->put($route, ['value' => 100, 'status' => 'paid']);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_edit_from_other_users_events()
    {
        $installment = Installment::factory()->forSupplier()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('installments.update', ['installment' => $installment]);
        $response = $this->put($route, ['value' => 100, 'status' => 'paid']);

        $response->assertForbidden();
    }

    public function test_validation()
    {
        $installment = Installment::factory()->forSupplier()->create();
        $event = $installment->supplier->category->event;

        $user = $event->user;
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('installments.update', ['installment' => $installment]);

        $response = $this->put($route, [
            'value' => '100,00',
            'status' => 'breathing',
            'due_date' => '2022-13-17T03:00:00.000Z'
        ]);

        $response->assertInvalid([
            'value' => 'The value must be a number.',
            'status' => 'The selected status is invalid.',
            'due_date' => 'The due date is not a valid date.',
        ]);
    }

    public function test_updates_successfully()
    {
        $installment = Installment::factory()->forSupplier()->create();
        $event = $installment->supplier->category->event;

        $user = $event->user;
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('installments.update', ['installment' => $installment]);

        $this->put($route, [
            'value' => '100.00',
            'status' => 'paid',
            'due_date' => '2022-03-17T03:00:00.000Z'
        ]);

        $installment->refresh();
        $this->assertEquals(100, $installment->value);
        $this->assertEquals('paid', $installment->status);
        $this->assertEquals('2022-03-17 03:00:00', $installment->due_date);
    }
}
