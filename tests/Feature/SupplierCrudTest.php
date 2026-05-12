<?php

namespace Tests\Feature;

use App\Models\Suppliers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    public function test_index_lists_suppliers(): void
    {
        $this->actingAsUser();
        Suppliers::factory()->create(['name' => 'Acme Timber']);

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Acme Timber');
    }

    public function test_guest_cannot_access_suppliers(): void
    {
        $this->get(route('suppliers.index'))->assertRedirect(route('login'));
    }

    public function test_store_creates_a_supplier(): void
    {
        $this->actingAsUser();

        $this->post(route('suppliers.store'), ['name' => 'New Co'])
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', ['name' => 'New Co']);
    }

    public function test_store_requires_unique_name(): void
    {
        $this->actingAsUser();
        Suppliers::factory()->create(['name' => 'Existing']);

        $this->from(route('suppliers.index'))
            ->post(route('suppliers.store'), ['name' => 'Existing'])
            ->assertSessionHasErrors('name');
    }

    public function test_update_changes_supplier_name(): void
    {
        $this->actingAsUser();
        $supplier = Suppliers::factory()->create(['name' => 'Old']);

        $this->patch(route('suppliers.update', $supplier), ['name' => 'New'])
            ->assertRedirect();

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'New']);
    }

    public function test_destroy_removes_supplier(): void
    {
        $this->actingAsUser();
        $supplier = Suppliers::factory()->create();

        $this->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
