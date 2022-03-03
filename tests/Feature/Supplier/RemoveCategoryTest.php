<?php

namespace Test\Feature\Supplier;

use App\Models\Event;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\SupplierCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RemoveCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->create();
        $categories = SupplierCategory::factory(5)->create();

        $event->categories()->syncWithPivotValues($categories, ['budget' => 13569]);

        $response = $this->delete(route('categories.detach', [
            'event' => $event->id,
            'category' => $categories->first()->id,
        ]));

        $response->assertRedirect(route('login'));
    }

    public function tests_needs_authorization()
    {
        $event = Event::factory()->forUser()->create();
        $categories = SupplierCategory::factory(5)->create();

        $event->categories()->syncWithPivotValues($categories, ['budget' => 13569]);

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $response = $this->delete(route('categories.detach', [
            'event' => $event->id,
            'category' => $categories->first()->id,
        ]));

        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();
        $categories = SupplierCategory::factory(5)->create();

        $event->categories()->syncWithPivotValues($categories, ['budget' => 13569]);

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_CATEGORY],
        ]);

        Auth::login($user);

        $response = $this->delete(route('categories.detach', [
            'event' => $event->id,
            'category' => $categories->first()->id,
        ]));

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_remove_category_from_other_users_events()
    {
        $event = Event::factory()->forUser()->create();
        $categories = SupplierCategory::factory(5)->create();

        $event->categories()->syncWithPivotValues($categories, ['budget' => 13569]);

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_CATEGORY],
        ]);

        Auth::login($user);

        $response = $this->delete(route('categories.detach', [
            'event' => $event->id,
            'category' => $categories->first()->id,
        ]));

        $response->assertForbidden();
    }

    public function test_cannot_remove_category_not_associated_to_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $category = SupplierCategory::factory()->create();

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_CATEGORY],
        ]);

        Auth::login($user);

        $response = $this->delete(route('categories.detach', [
            'event' => $event->id,
            'category' => $category->id,
        ]));

        $response->assertNotFound();
    }

    public function test_member_can_remove_from_parents_events()
    {
        $parent = User::factory()->hasMembers()->create();
        $event = Event::factory()->for($parent)->create();

        $categories = SupplierCategory::factory(5)->create();
        $event->categories()->syncWithPivotValues($categories, ['budget' => 13569]);

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_CATEGORY],
        ]);

        Auth::login($user);

        $response = $this->delete(route('categories.detach', [
            'event' => $event->id,
            'category' => $categories->first()->id,
        ]));

        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_removes_successfully()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $categories = SupplierCategory::factory(5)->create();
        $event->categories()->syncWithPivotValues($categories, ['budget' => 13569]);

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_CATEGORY],
        ]);

        Auth::login($user);
        $category = $categories->first();

        $response = $this->delete(route('categories.detach', [
            'event' => $event->id,
            'category' => $category->id,
        ]));

        $this->assertFalse($event->refresh()->categories->contains($category));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_removes_attached_suppliers_for_category()
    {
        $user = User::factory()->create();
        $event = Event::factory()->for($user)->create();

        $category = SupplierCategory::factory()->hasSuppliers(4)->create();
        $event->categories()->attach($category, ['budget' => 13569]);
        $event->suppliers()->syncWithPivotValues($category->suppliers, ['value' => 100]);

        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_CATEGORY],
        ]);

        Auth::login($user);

        $response = $this->delete(route('categories.detach', [
            'event' => $event->id,
            'category' => $category->id,
        ]));

        $this->assertCount(0, $event->refresh()->suppliers->all());
        $this->assertFalse($event->refresh()->categories->contains($category));
        $response->assertRedirect(route('events.view', ['event' => $event]));

    }
}
