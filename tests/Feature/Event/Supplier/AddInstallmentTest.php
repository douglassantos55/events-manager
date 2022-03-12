<?php

namespace Test\Feature\Event\Supplier;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventSupplier;
use App\Models\Installment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AddInstallmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $category = EventCategory::factory()->create();
        $supplier = EventSupplier::factory()->for($category, 'category')->create([
            'value' => 5000,
        ]);

        $response = $this->post(route('installments.create', [
            'supplier' => $supplier,
        ]), [
            'value' => '355.34',
            'status' => 'pending',
            'due_date' => '2022-10-20',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $category = EventCategory::factory()->create();
        $supplier = EventSupplier::factory()->for($category, 'category')->create([
            'value' => 5000,
        ]);

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->post(route('installments.create', [
            'supplier' => $supplier,
        ]), [
            'value' => '355.34',
            'status' => 'pending',
            'due_date' => '2022-10-20',
        ]);

        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();

        $event = Event::factory()->for($user)->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->for($category, 'category')->create([
            'value' => 5000,
        ]);

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_INSTALLMENT],
        ]);

        Auth::login($user);

        $response = $this->post(route('installments.create', [
            'supplier' => $supplier,
        ]), [
            'value' => '355.34',
            'status' => 'pending',
            'due_date' => '2022-10-20',
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_add_to_other_users_events()
    {
        $event = Event::factory()->forUser()->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->for($category, 'category')->create([
            'value' => 5000,
        ]);

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_INSTALLMENT],
        ]);

        Auth::login($user);

        $response = $this->post(route('installments.create', [
            'supplier' => $supplier,
        ]), [
            'value' => '355.34',
            'status' => 'pending',
            'due_date' => '2022-10-20',
        ]);

        $response->assertForbidden();
    }

    public function test_member_can_add_to_parents_events()
    {
        $parent = User::factory()->create();

        $event = Event::factory()->for($parent)->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->for($category, 'category')->create([
            'value' => 6000,
        ]);

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_INSTALLMENT],
        ]);

        Auth::login($user);

        $response = $this->post(route('installments.create', [
            'supplier' => $supplier,
        ]), [
            'value' => '355.34',
            'status' => 'pending',
            'due_date' => '2022-10-20',
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_member_cannot_add_to_others_events()
    {

        $event = Event::factory()->forUser()->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->for($category, 'category')->create([
            'value' => 5000,
        ]);

        $parent = User::factory()->create();
        $user = User::factory()->for($parent, 'captain')->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_INSTALLMENT],
        ]);

        Auth::login($user);

        $response = $this->post(route('installments.create', [
            'supplier' => $supplier,
        ]), [
            'value' => '355.34',
            'status' => 'pending',
            'due_date' => '2022-10-20',
        ]);

        $response->assertForbidden();
    }

    public function test_installments_sum_cannot_exceed_hired_value()
    {
        $category = EventCategory::factory()->create();
        $supplier = EventSupplier::factory()->for($category, 'category')->create([
            'value' => 1000,
        ]);

        $supplier->installments()->createMany([
            ['value' => 500, 'due_date' => '2022-03-20'],
            ['value' => 500, 'due_date' => '2022-04-20'],
        ]);

        $installment = Installment::make(['value' => 100, 'due_date' => '2020-05-20']);
        $this->assertFalse($supplier->canCreateInstallment($installment));
    }

    public function test_validation()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_INSTALLMENT],
        ]);

        $event = Event::factory()->for($user)->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->for($category, 'category')->create([
            'value' => 5000,
        ]);

        Auth::login($user);

        $response = $this->post(route('installments.create', [
            'supplier' => $supplier,
        ]), [
            'value' => '355,34',
            'status' => 'pending',
            'due_date' => '2022-13-20',
        ]);

        $response->assertInvalid([
            'value' => 'The value must be a number.',
            'due_date' => 'The due date is not a valid date.',
        ]);
    }

    public function test_creates_successfully()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_INSTALLMENT],
        ]);

        $event = Event::factory()->for($user)->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->for($category, 'category')->create([
            'value' => 5000,
        ]);

        Auth::login($user);

        $response = $this->post(route('installments.create', [
            'supplier' => $supplier,
        ]), [
            'value' => '355.34',
            'status' => 'pending',
            'due_date' => '2022-03-20',
        ]);

        $this->assertEquals(1, $supplier->refresh()->installments->count());
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_add_exceeding_sum()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_INSTALLMENT],
        ]);

        $event = Event::factory()->for($user)->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->for($category, 'category')->create([
            'value' => 1200,
        ]);

        $supplier->installments()->createMany([
            ['value' => 500, 'due_date' => '2022-03-20'],
            ['value' => 500, 'due_date' => '2022-04-20'],
        ]);

        Auth::login($user);

        $response = $this->post(route('installments.create', [
            'supplier' => $supplier,
        ]), [
            'value' => '500',
            'status' => 'pending',
            'due_date' => '2022-05-20',
        ]);

        $response->assertInvalid([
            'value' => 'The sum of installments exceeds the value hired.',
        ]);
    }
}
