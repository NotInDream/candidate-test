<?php

namespace App\Services;

use App\Models\CLT_Layups;
use App\Models\Suppliers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SupplierImportService
{
    public const STRATEGY_OVERWRITE = 'overwrite';

    public const STRATEGY_SKIP = 'skip';

    public const STRATEGY_DUPLICATE = 'duplicate';

    public const STRATEGY_REJECT = 'reject';

    public const STRATEGIES = [
        self::STRATEGY_OVERWRITE,
        self::STRATEGY_SKIP,
        self::STRATEGY_DUPLICATE,
        self::STRATEGY_REJECT,
    ];

    public const EXPORT_TYPE_SUPPLIERS = 'clt.suppliers.export';

    public const EXPORT_TYPE_LAYUPS = 'clt.layups.export';

    /**
     * @return array{created_layups:int,updated_layups:int,created_layers:int,updated_layers:int,skipped_layers:int,duplicated_layers:int,conflicts:array<int,string>,dry_run:bool,applied:bool}
     */
    public function import(Suppliers $supplier, string $json, string $strategy, bool $dryRun = false): array
    {
        if (! in_array($strategy, self::STRATEGIES, true)) {
            throw new RuntimeException("Unknown strategy: {$strategy}");
        }

        $data = $this->decode($json);

        $summary = $this->emptySummary($dryRun);
        $summary['conflicts'] = $this->detectConflicts($supplier, $data['layups']);

        if ($strategy === self::STRATEGY_REJECT && ! empty($summary['conflicts'])) {
            return $summary;
        }

        DB::beginTransaction();
        try {
            foreach ($data['layups'] as $incomingLayup) {
                $existing = $supplier->layups()->where('name', $incomingLayup['name'])->first();
                $this->applyLayup($supplier, $existing, $incomingLayup, $strategy, $summary);
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
                $summary['applied'] = true;
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $summary;
    }

    /**
     * @return array{created_suppliers:int,created_layups:int,updated_layups:int,created_layers:int,updated_layers:int,skipped_layers:int,duplicated_layers:int,conflicts:array<int,string>,dry_run:bool,applied:bool}
     */
    public function importAll(string $json, string $strategy, bool $dryRun = false): array
    {
        if (! in_array($strategy, self::STRATEGIES, true)) {
            throw new RuntimeException("Unknown strategy: {$strategy}");
        }

        $data = $this->decodeAll($json);

        $summary = $this->emptySummary($dryRun);
        $summary['created_suppliers'] = 0;

        foreach ($data['suppliers'] as $incomingSupplier) {
            $existingSupplier = Suppliers::where('name', $incomingSupplier['name'])->first();
            if ($existingSupplier === null) {
                continue;
            }
            foreach ($this->detectConflicts($existingSupplier, $incomingSupplier['layups']) as $conflict) {
                $summary['conflicts'][] = "[{$existingSupplier->name}] {$conflict}";
            }
        }

        if ($strategy === self::STRATEGY_REJECT && ! empty($summary['conflicts'])) {
            return $summary;
        }

        DB::beginTransaction();
        try {
            foreach ($data['suppliers'] as $incomingSupplier) {
                $supplier = Suppliers::where('name', $incomingSupplier['name'])->first();
                if ($supplier === null) {
                    $supplier = Suppliers::create(['name' => $incomingSupplier['name']]);
                    $summary['created_suppliers']++;
                }

                foreach ($incomingSupplier['layups'] as $incomingLayup) {
                    $existingLayup = $supplier->layups()->where('name', $incomingLayup['name'])->first();
                    $this->applyLayup($supplier, $existingLayup, $incomingLayup, $strategy, $summary);
                }
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
                $summary['applied'] = true;
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $summary;
    }

    /**
     * @return array{created_layups:int,updated_layups:int,created_layers:int,updated_layers:int,skipped_layers:int,duplicated_layers:int,conflicts:array<int,string>,dry_run:bool,applied:bool}
     */
    private function emptySummary(bool $dryRun): array
    {
        return [
            'created_layups' => 0,
            'updated_layups' => 0,
            'created_layers' => 0,
            'updated_layers' => 0,
            'skipped_layers' => 0,
            'duplicated_layers' => 0,
            'conflicts' => [],
            'dry_run' => $dryRun,
            'applied' => false,
        ];
    }

    /**
     * Apply an incoming layup. New layups are created in full; existing layups
     * resolve conflicts at the layer level: identical layers are no-ops, new
     * layer_orders are appended, and conflicting layers follow the strategy.
     *
     * @param  array{name:string,layers:array<int,array<string,mixed>>}  $incoming
     * @param  array<string,mixed>  $summary
     */
    private function applyLayup(Suppliers $supplier, ?CLT_Layups $existing, array $incoming, string $strategy, array &$summary): void
    {
        if ($existing === null) {
            $layup = $supplier->layups()->create(['name' => $incoming['name']]);
            $summary['created_layups']++;
            foreach ($incoming['layers'] as $layer) {
                $layup->layers()->create([
                    'layer_order' => $layer['layer_order'],
                    'thickness' => $layer['thickness'],
                    'width' => $layer['width'],
                    'angle' => $layer['angle'],
                ]);
                $summary['created_layers']++;
            }

            return;
        }

        $touched = false;
        $maxOrder = (int) $existing->layers()->max('layer_order');

        foreach ($incoming['layers'] as $incomingLayer) {
            $existingLayer = $existing->layers()
                ->where('layer_order', $incomingLayer['layer_order'])
                ->first();

            if ($existingLayer === null) {
                $existing->layers()->create([
                    'layer_order' => $incomingLayer['layer_order'],
                    'thickness' => $incomingLayer['thickness'],
                    'width' => $incomingLayer['width'],
                    'angle' => $incomingLayer['angle'],
                ]);
                $summary['created_layers']++;
                $touched = true;
                if ((int) $incomingLayer['layer_order'] > $maxOrder) {
                    $maxOrder = (int) $incomingLayer['layer_order'];
                }
                continue;
            }

            $differs = ((float) $existingLayer->thickness !== (float) $incomingLayer['thickness'])
                || ((float) $existingLayer->width !== (float) $incomingLayer['width'])
                || ((float) $existingLayer->angle !== (float) $incomingLayer['angle']);

            if (! $differs) {
                continue;
            }

            switch ($strategy) {
                case self::STRATEGY_OVERWRITE:
                    $existingLayer->update([
                        'thickness' => $incomingLayer['thickness'],
                        'width' => $incomingLayer['width'],
                        'angle' => $incomingLayer['angle'],
                    ]);
                    $summary['updated_layers']++;
                    $touched = true;
                    break;
                case self::STRATEGY_SKIP:
                    $summary['skipped_layers']++;
                    break;
                case self::STRATEGY_DUPLICATE:
                    $maxOrder++;
                    $existing->layers()->create([
                        'layer_order' => $maxOrder,
                        'thickness' => $incomingLayer['thickness'],
                        'width' => $incomingLayer['width'],
                        'angle' => $incomingLayer['angle'],
                    ]);
                    $summary['duplicated_layers']++;
                    $touched = true;
                    break;
            }
        }

        if ($touched) {
            $summary['updated_layups']++;
        }
    }

    /**
     * @return array{suppliers:array<int,array{name:string,layups:array<int,array{name:string,layers:array<int,array<string,mixed>>}>}>}
     */
    private function decodeAll(string $json): array
    {
        $decoded = $this->decodeJson($json, self::EXPORT_TYPE_SUPPLIERS);

        if (! isset($decoded['suppliers']) || ! is_array($decoded['suppliers'])) {
            throw new RuntimeException($this->mismatchMessage(self::EXPORT_TYPE_SUPPLIERS));
        }

        foreach ($decoded['suppliers'] as $supplier) {
            if (! isset($supplier['name']) || ! is_string($supplier['name'])
                || ! isset($supplier['layups']) || ! is_array($supplier['layups'])) {
                throw new RuntimeException($this->mismatchMessage(self::EXPORT_TYPE_SUPPLIERS));
            }
            foreach ($supplier['layups'] as $layup) {
                if (! isset($layup['name']) || ! is_string($layup['name'])
                    || ! isset($layup['layers']) || ! is_array($layup['layers'])) {
                    throw new RuntimeException($this->mismatchMessage(self::EXPORT_TYPE_SUPPLIERS));
                }
            }
        }

        return $decoded;
    }

    /**
     * @return array{layups:array<int,array{name:string,layers:array<int,array<string,mixed>>}>}
     */
    private function decode(string $json): array
    {
        $decoded = $this->decodeJson($json, self::EXPORT_TYPE_LAYUPS);

        if (! isset($decoded['layups']) || ! is_array($decoded['layups'])) {
            throw new RuntimeException($this->mismatchMessage(self::EXPORT_TYPE_LAYUPS));
        }

        foreach ($decoded['layups'] as $layup) {
            if (! isset($layup['name']) || ! is_string($layup['name'])
                || ! isset($layup['layers']) || ! is_array($layup['layers'])) {
                throw new RuntimeException($this->mismatchMessage(self::EXPORT_TYPE_LAYUPS));
            }
        }

        return $decoded;
    }

    /**
     * @return array<string,mixed>
     */
    private function decodeJson(string $json, string $expectedType): array
    {
        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            throw new RuntimeException($this->mismatchMessage($expectedType));
        }
        if (($decoded['type'] ?? null) !== $expectedType) {
            throw new RuntimeException($this->mismatchMessage($expectedType));
        }

        return $decoded;
    }

    private function mismatchMessage(string $expectedType): string
    {
        $label = $expectedType === self::EXPORT_TYPE_SUPPLIERS ? 'suppliers' : 'layups';

        return "Export file does not match. Please upload a {$label} export file.";
    }

    /**
     * Returns a flat list of human-readable conflict descriptions.
     *
     * @param  array<int,array{name:string,layers:array<int,array<string,mixed>>}>  $layups
     * @return array<int,string>
     */
    private function detectConflicts(Suppliers $supplier, array $layups): array
    {
        $conflicts = [];

        foreach ($layups as $incomingLayup) {
            $existing = $supplier->layups()->where('name', $incomingLayup['name'])->first();
            if ($existing === null) {
                continue;
            }

            foreach ($incomingLayup['layers'] as $incomingLayer) {
                $existingLayer = $existing->layers()
                    ->where('layer_order', $incomingLayer['layer_order'])
                    ->first();

                if ($existingLayer === null) {
                    continue;
                }

                $differs = ((float) $existingLayer->thickness !== (float) $incomingLayer['thickness'])
                    || ((float) $existingLayer->width !== (float) $incomingLayer['width'])
                    || ((float) $existingLayer->angle !== (float) $incomingLayer['angle']);

                if ($differs) {
                    $conflicts[] = sprintf(
                        'Layup "%s" layer #%d differs (existing: %smm/%smm/%s°, incoming: %smm/%smm/%s°)',
                        $existing->name,
                        $incomingLayer['layer_order'],
                        $existingLayer->thickness, $existingLayer->width, $existingLayer->angle,
                        $incomingLayer['thickness'], $incomingLayer['width'], $incomingLayer['angle'],
                    );
                }
            }
        }

        return $conflicts;
    }
}
