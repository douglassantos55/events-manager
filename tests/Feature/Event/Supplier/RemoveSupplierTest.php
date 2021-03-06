<?php

namespace Test\Feature\Event\Supplier;

use App\Models\ContractFile;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventSupplier;
use App\Models\Installment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RemoveSupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_needs_authentication()
    {
        $event = Event::factory()->forUser()->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        $response = $this->delete(route('suppliers.detach', ['supplier' => $supplier->id]));
        $response->assertRedirect(route('login'));
    }

    public function test_needs_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [],
        ]);

        $event = Event::factory()->for($user)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        Auth::login($user);

        $response = $this->delete(route('suppliers.detach', ['supplier' => $supplier->id]));
        $response->assertForbidden();
    }

    public function test_passes_authorization()
    {
        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        $event = Event::factory()->for($user)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        Auth::login($user);

        $response = $this->delete(route('suppliers.detach', ['supplier' => $supplier->id]));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_cannot_remove_from_other_users_events()
    {
        $event = Event::factory()->forUser()->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        $user = User::factory()->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->delete(route('suppliers.detach', ['supplier' => $supplier->id]));
        $response->assertForbidden();
    }

    public function test_member_can_remove_from_parents_events()
    {
        $parent = User::factory()->create();

        $event = Event::factory()->for($parent)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        Auth::login($user);

        $response = $this->delete(route('suppliers.detach', ['supplier' => $supplier->id]));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_removes_successfully()
    {
        $parent = User::factory()->create();

        $event = Event::factory()->for($parent)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        Auth::login($user);
        $response = $this->delete(route('suppliers.detach', ['supplier' => $supplier->id]));

        $this->assertFalse($category->refresh()->suppliers->contains('supplier_id', $supplier->id));
        $response->assertRedirect(route('events.view', ['event' => $event]));
    }

    public function test_removes_uploaded_files()
    {
        $storage = Storage::fake();

        $parent = User::factory()->create();

        $event = Event::factory()->for($parent)->create();
        $categories = EventCategory::factory(3)->for($event)->hasSuppliers(3)->create();

        $category = $categories->first();
        $supplier = $category->suppliers->first();

        $image = UploadedFile::fake()->create('image.png')->store('contracts');
        $document = UploadedFile::fake()->create('document.pdf')->store('contracts');

        $supplier->files()->create(['path' => $image]);
        $supplier->files()->create(['path' => $document]);

        $user = User::factory()->for($parent, 'captain')->create();
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        Auth::login($user);
        $this->delete(route('suppliers.detach', ['supplier' => $supplier->id]));

        $this->assertCount(0, $storage->files('contracts'));
        $this->assertEquals(0, ContractFile::all()->count());
    }

    public function test_removes_installments()
    {
        $supplier = EventSupplier::factory()->hasInstallments(5)->create();

        $user = $supplier->category->event->user;
        $user->role = Role::factory()->for($user)->create([
            'permissions' => [Permission::REMOVE_SUPPLIER],
        ]);

        Auth::login($user);
        $this->delete(route('suppliers.detach', ['supplier' => $supplier->id]));

        $this->assertModelMissing($supplier);
        $this->assertEquals(0, Installment::all()->count());
    }
}
