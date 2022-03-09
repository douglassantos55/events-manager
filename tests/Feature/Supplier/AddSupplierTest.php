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
        $event = Event::factory()->forUser()->hasCategories()->create();

        $category = $event->categories->first();
        $supplier = Supplier::factory()->for($category->category, 'category')->create();

        $route = route('suppliers.attach', [
            'event' => $event,
            'category' => $category,
        ]);

        $response = $this->post($route, [
            'value' => 4699,
            'status' => 'pending',
            'supplier_id' => $supplier->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->hasCategories()->create();

        $category = $event->categories->first();
        $supplier = Supplier::factory()->for($category->category, 'category')->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $route = route('suppliers.attach', [
            'event' => $event,
            'category' => $category,
        ]);

        $response = $this->post($route, [
            'value' => 4699,
            'status' => 'pending',
            'supplier_id' => $supplier->id,
        ]);

        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->hasCategories()->create();

        $category = $event->categories->first();
        $supplier = Supplier::factory()->for($category->category, 'category')->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('suppliers.attach', [
            'event' => $event,
            'category' => $category,
        ]);

        $response = $this->post($route, [
            'value' => 4699,
            'status' => 'pending',
            'supplier_id' => $supplier->id,
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_add_to_other_users_events()
    {
        $event = Event::factory()->forUser()->hasCategories()->create();

        $category = $event->categories->first();
        $supplier = Supplier::factory()->for($category->category, 'category')->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('suppliers.attach', [
            'event' => $event,
            'category' => $category,
        ]);

        $response = $this->post($route, [
            'value' => 4699,
            'status' => 'pending',
            'supplier_id' => $supplier->id,
        ]);

        $response->assertForbidden();
    }

    public function test_member_can_add_to_parents_events()
    {
        $parent = User::factory()->create();
        $event = Event::factory()->for($parent)->hasCategories()->create();

        $category = $event->categories->first();
        $supplier = Supplier::factory()->for($category->category, 'category')->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('suppliers.attach', [
            'event' => $event,
            'category' => $category,
        ]);

        $response = $this->post($route, [
            'value' => 4699,
            'status' => 'pending',
            'supplier_id' => $supplier->id,
        ]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_validation()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->hasCategories()->create();

        $category = $event->categories->first();
        $supplier = Supplier::factory()->for($category->category, 'category')->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('suppliers.attach', [
            'event' => $event,
            'category' => $category,
        ]);

        $response = $this->post($route, [
            'value' => '69,99',
            'status' => 'breathing',
            'supplier_id' => $supplier->id,
        ]);

        $response->assertInvalid([
            'value' => 'The value must be a number.',
            'status' => 'The selected status is invalid.',
        ]);
    }

    public function test_adds_successfully()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->hasCategories()->create();

        $category = $event->categories->first();
        $supplier = Supplier::factory()->for($category->category, 'category')->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('suppliers.attach', [
            'event' => $event,
            'category' => $category,
        ]);

        $response = $this->post($route, [
            'value' => 69,
            'status' => 'hired',
            'supplier_id' => $supplier->id,
        ]);

        $category->refresh();

        $this->assertEquals('hired', $category->suppliers->first()->status);
        $this->assertTrue($category->suppliers->contains('supplier_id', $supplier->id));

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_ignores_duplicated()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->hasCategories()->create();

        $category = $event->categories->first();
        $supplier = Supplier::factory()->for($category->category, 'category')->create();

        $category->suppliers()->create([
            'supplier_id' => $supplier->id,
            'value' => 520,
            'status' => 'pending',
        ]);

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('suppliers.attach', [
            'event' => $event,
            'category' => $category,
        ]);

        $response = $this->post($route, [
            'value' => 69,
            'status' => 'hired',
            'supplier_id' => $supplier->id,
        ]);

        $category->refresh();

        $this->assertCount(1, $category->suppliers->all());
        $this->assertTrue($category->suppliers->contains('supplier_id', $supplier->id));

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_add_non_existing_supplier()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->hasCategories()->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);

        $route = route('suppliers.attach', [
            'event' => $event,
            'category' => $event->categories->first()->id,
        ]);

        $response = $this->post($route, [
            'value' => 69,
            'status' => 'hired',
            'supplier_id' => 69,
        ]);

        $response->assertInvalid([
            'supplier_id' => 'The selected supplier id is invalid.',
        ]);
    }

    public function test_cannot_add_supplier_from_category_not_assigned_to_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->hasCategories()->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::ADD_SUPPLIER],
        ]);

        Auth::login($user);
        $supplier = Supplier::factory()->forCategory()->create();

        $route = route('suppliers.attach', [
            'event' => $event,
            'category' => $event->categories->first()->id,
        ]);

        $response = $this->post($route, [
            'value' => 69420,
            'status' => 'hired',
            'supplier_id' => $supplier->id,
        ]);

        $response->assertInvalid([
            'supplier_id' => 'The selected supplier id is invalid.',
        ]);
    }
}
