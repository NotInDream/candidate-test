<?php

namespace Tests\Unit;

use App\Models\CLT_Layups;
use App\Models\Suppliers;
use App\Services\SupplierImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SupplierImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private SupplierImportService $service;

    private Suppliers $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SupplierImportService;
        $this->supplier = Suppliers::factory()->create();
    }

    private function payload(array $layups): string
    {
        return json_encode(['layups' => $layups]);
    }

    public function test_creates_brand_new_layups_and_layers(): void
    {
        $json = $this->payload([
            [
                'name' => 'Standard 3-Ply',
                'layers' => [
                    ['layer_order' => 1, 'thickness' => 35, 'width' => 100, 'angle' => 0],
                    ['layer_order' => 2, 'thickness' => 35, 'width' => 100, 'angle' => 90],
                ],
            ],
        ]);

        $summary = $this->service->import($this->supplier, $json, SupplierImportService::STRATEGY_OVERWRITE);

        $this->assertSame(1, $summary['created_layups']);
        $this->assertSame(2, $summary['created_layers']);
        $this->assertTrue($summary['applied']);
        $this->assertSame(1, $this->supplier->layups()->count());
        $this->assertSame(2, $this->supplier->layups()->first()->layers()->count());
    }

    public function test_overwrite_updates_existing_layer_values(): void
    {
        $layup = CLT_Layups::factory()->for($this->supplier, 'supplier')->create(['name' => 'Standard']);
        $layup->layers()->create(['layer_order' => 1, 'thickness' => 30, 'width' => 100, 'angle' => 0]);

        $json = $this->payload([[
            'name' => 'Standard',
            'layers' => [
                ['layer_order' => 1, 'thickness' => 99, 'width' => 100, 'angle' => 0],
            ],
        ]]);

        $summary = $this->service->import($this->supplier, $json, SupplierImportService::STRATEGY_OVERWRITE);

        $this->assertSame(1, $summary['updated_layups']);
        $this->assertSame(1, $summary['updated_layers']);
        $this->assertEqualsWithDelta(99, $layup->layers()->first()->thickness, 0.001);
    }

    public function test_skip_leaves_existing_data_unchanged(): void
    {
        $layup = CLT_Layups::factory()->for($this->supplier, 'supplier')->create(['name' => 'Standard']);
        $layup->layers()->create(['layer_order' => 1, 'thickness' => 30, 'width' => 100, 'angle' => 0]);

        $json = $this->payload([[
            'name' => 'Standard',
            'layers' => [
                ['layer_order' => 1, 'thickness' => 99, 'width' => 100, 'angle' => 0],
            ],
        ]]);

        $summary = $this->service->import($this->supplier, $json, SupplierImportService::STRATEGY_SKIP);

        $this->assertSame(1, $summary['skipped_layups']);
        $this->assertEqualsWithDelta(30, $layup->layers()->first()->thickness, 0.001);
    }

    public function test_duplicate_creates_a_new_layup_with_imported_suffix(): void
    {
        CLT_Layups::factory()->for($this->supplier, 'supplier')->create(['name' => 'Standard']);

        $json = $this->payload([[
            'name' => 'Standard',
            'layers' => [
                ['layer_order' => 1, 'thickness' => 35, 'width' => 100, 'angle' => 0],
            ],
        ]]);

        $summary = $this->service->import($this->supplier, $json, SupplierImportService::STRATEGY_DUPLICATE);

        $this->assertSame(1, $summary['duplicated_layups']);
        $this->assertTrue($this->supplier->layups()->where('name', 'Standard (imported)')->exists());
    }

    public function test_reject_aborts_when_conflict_detected(): void
    {
        $layup = CLT_Layups::factory()->for($this->supplier, 'supplier')->create(['name' => 'Standard']);
        $layup->layers()->create(['layer_order' => 1, 'thickness' => 30, 'width' => 100, 'angle' => 0]);

        $json = $this->payload([[
            'name' => 'Standard',
            'layers' => [
                ['layer_order' => 1, 'thickness' => 99, 'width' => 100, 'angle' => 0],
            ],
        ]]);

        $summary = $this->service->import($this->supplier, $json, SupplierImportService::STRATEGY_REJECT);

        $this->assertFalse($summary['applied']);
        $this->assertNotEmpty($summary['conflicts']);
        $this->assertEqualsWithDelta(30, $layup->layers()->first()->thickness, 0.001);
    }

    public function test_dry_run_rolls_back_changes(): void
    {
        $json = $this->payload([[
            'name' => 'Dry-Run Only',
            'layers' => [
                ['layer_order' => 1, 'thickness' => 35, 'width' => 100, 'angle' => 0],
            ],
        ]]);

        $summary = $this->service->import(
            $this->supplier,
            $json,
            SupplierImportService::STRATEGY_OVERWRITE,
            dryRun: true
        );

        $this->assertSame(1, $summary['created_layups']);
        $this->assertTrue($summary['dry_run']);
        $this->assertFalse($summary['applied']);
        $this->assertSame(0, $this->supplier->layups()->count());
    }

    public function test_conflicts_detected_when_layer_values_differ(): void
    {
        $layup = CLT_Layups::factory()->for($this->supplier, 'supplier')->create(['name' => 'Standard']);
        $layup->layers()->create(['layer_order' => 1, 'thickness' => 30, 'width' => 100, 'angle' => 0]);

        $json = $this->payload([[
            'name' => 'Standard',
            'layers' => [
                ['layer_order' => 1, 'thickness' => 30, 'width' => 100, 'angle' => 45], // angle differs
            ],
        ]]);

        $summary = $this->service->import($this->supplier, $json, SupplierImportService::STRATEGY_SKIP);

        $this->assertCount(1, $summary['conflicts']);
    }

    public function test_no_conflict_when_layer_values_match(): void
    {
        $layup = CLT_Layups::factory()->for($this->supplier, 'supplier')->create(['name' => 'Standard']);
        $layup->layers()->create(['layer_order' => 1, 'thickness' => 30, 'width' => 100, 'angle' => 0]);

        $json = $this->payload([[
            'name' => 'Standard',
            'layers' => [
                ['layer_order' => 1, 'thickness' => 30, 'width' => 100, 'angle' => 0],
            ],
        ]]);

        $summary = $this->service->import($this->supplier, $json, SupplierImportService::STRATEGY_OVERWRITE);

        $this->assertEmpty($summary['conflicts']);
    }

    public function test_invalid_json_throws(): void
    {
        $this->expectException(RuntimeException::class);
        $this->service->import($this->supplier, '{not valid', SupplierImportService::STRATEGY_OVERWRITE);
    }

    public function test_missing_layups_array_throws(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing "layups" array');
        $this->service->import($this->supplier, json_encode(['other' => []]), SupplierImportService::STRATEGY_OVERWRITE);
    }

    public function test_unknown_strategy_throws(): void
    {
        $this->expectException(RuntimeException::class);
        $this->service->import($this->supplier, $this->payload([]), 'nonsense');
    }
}
