<?php

namespace Test\Feature\Supplier;

use App\Models\Event;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SupplierCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AddCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authorization()
    {
        $category = SupplierCategory::factory()->create();
        $event = Event::factory()->forUser()->create();

        $route = route('categories.attach', [
            'event' => $event,
            'category' => $category
        ]);

        $response = $this->post($route, ['budget' => 13490]);
        $response->assertRedirect(route('login'));
    }

    public function test_needs_permission()
    {
        $category = SupplierCategory::factory()->create();
        $event = Event::factory()->forUser()->create();

        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [Permission::EDIT_EVENT],
        ]);

        Auth::login($event->user);

        $route = route('categories.attach', [
            'event' => $event,
            'category' => $category
        ]);

        $response = $this->post($route, ['budget' => 24569]);
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $category = SupplierCategory::factory()->create();
        $event = Event::factory()->forUser()->create();

        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [Permission::ADD_CATEGORY],
        ]);

        Auth::login($event->user);

        $route = route('categories.attach', [
            'event' => $event,
            'category' => $category
        ]);

        $response = $this->post($route, ['budget' => 4569]);
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_add_to_other_users_events()
    {
        $event = Event::factory()->forUser()->create();
        $category = SupplierCategory::factory()->create();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create();

        Auth::login($user);

        $route = route('categories.attach', [
            'event' => $event,
            'category' => $category
        ]);

        $response = $this->post($route, ['budget' => 4561]);
        $response->assertForbidden();
    }

    public function test_member_can_add_to_parents_events()
    {
        $event = Event::factory()->forUser()->create();
        $category = SupplierCategory::factory()->create();

        $user = User::factory()->for($event->user, 'captain')->create();
        $user->role = Role::factory()->for($user)->create();

        Auth::login($user);

        $route = route('categories.attach', [
            'event' => $event,
            'category' => $category
        ]);
        $response = $this->post($route, ['budget' => 3450]);

        $response->assertRedirect(route('events.view', ['event' => $event]));
        $this->assertTrue($event->refresh()->categories->contains($category));
    }

    public function test_ignores_duplicated_category()
    {
        $category = SupplierCategory::factory()->create();

        $event = Event::factory()->forUser()->create();
        $event->categories()->attach($category, ['budget' => 430]);

        $event->user->role = Role::factory()->for($event->user)->create();

        Auth::login($event->user);

        $route = route('categories.attach', [
            'event' => $event,
            'category' => $category
        ]);

        $response = $this->post($route, ['budget' => 1305]);

        $this->assertCount(1, $event->refresh()->categories->all());
        $this->assertTrue($event->refresh()->categories->contains($category));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_validation()
    {
        $category = SupplierCategory::factory()->create();
        $event = Event::factory()->forUser()->create();

        $event->user->role = Role::factory()->for($event->user)->create([
            'permissions' => [Permission::ADD_CATEGORY],
        ]);

        Auth::login($event->user);

        $route = route('categories.attach', [
            'event' => $event,
            'category' => $category
        ]);
        $response = $this->post($route, ['budget' => '12345,33']);

        $response->assertInvalid([
            'budget' => 'The budget must be a number.',
        ]);
    }
}
