<?php

namespace Test\Feature\Supplier;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventSupplier;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RemoveContractFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->hasFiles()->for($category, 'category')->create();

        $file = $supplier->files->first();

        $response = $this->delete(route('files.delete', ['file' => $file]));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        Auth::login($user);

        $event = Event::factory()->for($user)->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->hasFiles()->for($category, 'category')->create();

        $file = $supplier->files->first();

        $response = $this->delete(route('files.delete', ['file' => $file]));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $event = Event::factory()->for($user)->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->hasFiles()->for($category, 'category')->create();

        $file = $supplier->files->first();

        $response = $this->delete(route('files.delete', ['file' => $file]));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_member_can_remove_from_parents_events()
    {
        $parent = User::factory()->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $event = Event::factory()->for($parent)->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->hasFiles()->for($category, 'category')->create();

        $file = $supplier->files->first();

        $response = $this->delete(route('files.delete', ['file' => $file]));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_remove_from_other_users_events()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $event = Event::factory()->forUser()->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->hasFiles()->for($category, 'category')->create();

        $file = $supplier->files->first();

        $response = $this->delete(route('files.delete', ['file' => $file]));
        $response->assertForbidden();
    }

    public function test_file_is_removed()
    {
        $storage = Storage::fake();
        $parent = User::factory()->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $event = Event::factory()->for($parent)->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->for($category, 'category')->create();

        $path = UploadedFile::fake()->create('document.pdf')->store('contracts');
        $file = $supplier->files()->create(['path' => $path]);

        $this->delete(route('files.delete', ['file' => $file]));
        $this->assertCount(0, $storage->files('contracts'));
    }

    public function test_removes_successfully()
    {
        $parent = User::factory()->create();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::EDIT_SUPPLIER],
        ]);

        Auth::login($user);

        $event = Event::factory()->for($parent)->create();
        $category = EventCategory::factory()->for($event)->create();
        $supplier = EventSupplier::factory()->hasFiles()->for($category, 'category')->create();

        $file = $supplier->files->first();

        $this->delete(route('files.delete', ['file' => $file]));
        $this->assertEquals(0, $supplier->refresh()->files->count());
    }
}

