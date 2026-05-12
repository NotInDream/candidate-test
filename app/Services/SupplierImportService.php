<?php

namespace App\Services;

use App\Models\CLT_Layers;
use App\Models\CLT_Layups;
use App\Models\Suppliers;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SupplierImportService
{
    public const STRATEGY_OVERWRITE = 'overwrite';
    public const STRATEGY_SKIP      = 'skip';
    public const STRATEGY_DUPLICATE = 'duplicate';
    public const STRATEGY_REJECT    = 'reject';

    public const STRATEGIES = [
        self::STRATEGY_OVERWRITE,
        self::STRATEGY_SKIP,
        self::STRATEGY_DUPLICATE,
        self::STRATEGY_REJECT,
    ];

    /**
     * Parse a JSON payload and apply it to the given supplier using the chosen
     * conflict resolution strategy. Conflicts are always detected and returned
     * for the UI to surface, regardless of strategy.
     *
     * @return array{created_layups:int,updated_layups:int,skipped_layups:int,duplicated_layups:int,created_layers:int,updated_layers:int,conflicts:array<int,string>,dry_run:bool,applied:bool}
     */
    public function import(Suppliers $supplier, string $json, string $strategy, bool $dryRun = false): array
    {
        if (!in_array($strategy, self::STRATEGIES, true)) {
            throw new RuntimeException("Unknown strategy: {$strategy}");
        }

        $data = $this->decode($json);

        $summary = [
            'created_layups'    => 0,
            'updated_layups'    => 0,
            'skipped_layups'    => 0,
            'duplicated_layups' => 0,
            'created_layers'    => 0,
            'updated_layers'    => 0,
            'conflicts'         => $this->detectConflicts($supplier, $data['layups']),
            'dry_run'           => $dryRun,
            'applied'           => false,
        ];

        if ($strategy === self::STRATEGY_REJECT && !empty($summary['conflicts'])) {
            return $summary;
        }

        DB::beginTransaction();
        try {
            foreach ($data['layups'] as $incomingLayup) {
                $existing = $supplier->layups()->where('name', $incomingLayup['name'])->first();

                if ($existing === null) {
                    $this->createLayup($supplier, $incomingLayup, $summary);
                    continue;
                }

                match ($strategy) {
                    self::STRATEGY_OVERWRITE => $this->overwriteLayup($existing, $incomingLayup, $summary),
                    self::STRATEGY_SKIP      => $summary['skipped_layups']++,
                    self::STRATEGY_DUPLICATE => $this->duplicateLayup($supplier, $incomingLayup, $summary),
                    self::STRATEGY_REJECT    => null,
                };
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
     * @return array{layups:array<int,array{name:string,layers:array<int,array<string,mixed>>}>}
     */
    private function decode(string $json): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid JSON file.');
        }
        if (!isset($decoded['layups']) || !is_array($decoded['layups'])) {
            throw new RuntimeException('Missing "layups" array in import file.');
        }

        foreach ($decoded['layups'] as $i => $layup) {
            if (!isset($layup['name']) || !is_string($layup['name'])) {
                throw new RuntimeException("Layup at index {$i} is missing a name.");
            }
            if (!isset($layup['layers']) || !is_array($layup['layers'])) {
                throw new RuntimeException("Layup '{$layup['name']}' is missing a layers array.");
            }
        }

        return $decoded;
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
                    || ((int) $existingLayer->angle !== (int) $incomingLayer['angle']);

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

    /**
     * @param  array<string,mixed>  $incoming
     * @param  array<string,mixed>  $summary
     */
    private function createLayup(Suppliers $supplier, array $incoming, array &$summary): void
    {
        $layup = $supplier->layups()->create(['name' => $incoming['name']]);
        $summary['created_layups']++;
        $summary['created_layers'] += $this->writeLayers($layup, $incoming['layers']);
    }

    /**
     * @param  array<string,mixed>  $incoming
     * @param  array<string,mixed>  $summary
     */
    private function overwriteLayup(CLT_Layups $existing, array $incoming, array &$summary): void
    {
        $summary['updated_layups']++;

        foreach ($incoming['layers'] as $incomingLayer) {
            $existingLayer = $existing->layers()
                ->where('layer_order', $incomingLayer['layer_order'])
                ->first();

            $attributes = [
                'thickness' => $incomingLayer['thickness'],
                'width'     => $incomingLayer['width'],
                'angle'     => $incomingLayer['angle'],
            ];

            if ($existingLayer === null) {
                $existing->layers()->create($attributes + ['layer_order' => $incomingLayer['layer_order']]);
                $summary['created_layers']++;
            } else {
                $existingLayer->update($attributes);
                $summary['updated_layers']++;
            }
        }
    }

    /**
     * @param  array<string,mixed>  $incoming
     * @param  array<string,mixed>  $summary
     */
    private function duplicateLayup(Suppliers $supplier, array $incoming, array &$summary): void
    {
        $baseName = $incoming['name'] . ' (imported)';
        $name     = $baseName;
        $i        = 2;
        while ($supplier->layups()->where('name', $name)->exists()) {
            $name = "{$baseName} {$i}";
            $i++;
        }

        $layup = $supplier->layups()->create(['name' => $name]);
        $summary['duplicated_layups']++;
        $summary['created_layers'] += $this->writeLayers($layup, $incoming['layers']);
    }

    /**
     * @param  array<int,array<string,mixed>>  $layers
     */
    private function writeLayers(CLT_Layups $layup, array $layers): int
    {
        $count = 0;
        foreach ($layers as $layer) {
            $layup->layers()->create([
                'layer_order' => $layer['layer_order'],
                'thickness'   => $layer['thickness'],
                'width'       => $layer['width'],
                'angle'       => $layer['angle'],
            ]);
            $count++;
        }
        return $count;
    }
}
