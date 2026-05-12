<?php

namespace Tests\Feature;

use App\Models\CLT_Layups;
use App\Models\Suppliers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SupplierExportImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_export_returns_json_attachment_with_full_tree(): void
    {
        $supplier = Suppliers::factory()->create(['name' => 'Acme']);
        $layup    = CLT_Layups::factory()->for($supplier, 'supplier')->create(['name' => 'Standard']);
        $layup->layers()->create(['layer_order' => 1, 'thickness' => 35, 'width' => 100, 'angle' => 0]);

        $response = $this->get(route('suppliers.export', $supplier));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');

        $payload = json_decode($response->streamedContent(), true);
        $this->assertArrayHasKey('layups', $payload);
        $this->assertSame('Standard', $payload['layups'][0]['name']);
        $this->assertSame(1, $payload['layups'][0]['layers'][0]['layer_order']);
    }

    public function test_import_creates_new_layups_with_overwrite_strategy(): void
    {
        $supplier = Suppliers::factory()->create();
        $json = json_encode([
            'layups' => [
                [
                    'name'   => 'Imported Layup',
                    'layers' => [
                        ['layer_order' => 1, 'thickness' => 35, 'width' => 100, 'angle' => 0],
                    ],
                ],
            ],
        ]);

        $response = $this->post(route('suppliers.import', $supplier), [
            'file'     => UploadedFile::fake()->createWithContent('import.json', $json),
            'strategy' => 'overwrite',
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));
        $this->assertDatabaseHas('clt_layups', ['supplier_id' => $supplier->id, 'name' => 'Imported Layup']);
        $this->assertDatabaseHas('clt_layers', ['thickness' => 35, 'layer_order' => 1]);
    }

    public function test_import_with_reject_strategy_flashes_conflicts(): void
    {
        $supplier = Suppliers::factory()->create();
        $layup    = CLT_Layups::factory()->for($supplier, 'supplier')->create(['name' => 'Standard']);
        $layup->layers()->create(['layer_order' => 1, 'thickness' => 30, 'width' => 100, 'angle' => 0]);

        $json = json_encode([
            'layups' => [
                ['name' => 'Standard', 'layers' => [
                    ['layer_order' => 1, 'thickness' => 99, 'width' => 100, 'angle' => 0],
                ]],
            ],
        ]);

        $this->post(route('suppliers.import', $supplier), [
            'file'     => UploadedFile::fake()->createWithContent('import.json', $json),
            'strategy' => 'reject',
        ])->assertSessionHas('import_conflicts');

        // Original value preserved
        $this->assertEqualsWithDelta(30, $layup->fresh()->layers()->first()->thickness, 0.001);
    }

    public function test_import_dry_run_does_not_persist(): void
    {
        $supplier = Suppliers::factory()->create();
        $json = json_encode([
            'layups' => [
                ['name' => 'Test', 'layers' => [
                    ['layer_order' => 1, 'thickness' => 35, 'width' => 100, 'angle' => 0],
                ]],
            ],
        ]);

        $this->post(route('suppliers.import', $supplier), [
            'file'     => UploadedFile::fake()->createWithContent('import.json', $json),
            'strategy' => 'overwrite',
            'dry_run'  => '1',
        ])->assertRedirect();

        $this->assertSame(0, $supplier->layups()->count());
    }

    public function test_import_validates_strategy_enum(): void
    {
        $supplier = Suppliers::factory()->create();
        $json = json_encode(['layups' => []]);

        $this->from(route('suppliers.show', $supplier))
            ->post(route('suppliers.import', $supplier), [
                'file'     => UploadedFile::fake()->createWithContent('import.json', $json),
                'strategy' => 'invalid-strategy',
            ])
            ->assertSessionHasErrors('strategy');
    }
}
