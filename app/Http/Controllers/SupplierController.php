<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportSupplierRequest;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Suppliers;
use App\Services\SupplierImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Suppliers::withCount('layups')->latest()->paginate(5);

        return view('suppliers', ['suppliers' => $suppliers]);
    }

    public function show(Suppliers $supplier): View
    {
        $layups = $supplier->layups()
            ->withCount('layers')
            ->withSum('layers as layers_thickness_sum', 'thickness')
            ->latest()
            ->paginate(5);

        return view('layup-manager', [
            'supplier' => $supplier,
            'layups'   => $layups,
        ]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Suppliers::create($request->validated());

        return redirect()
            ->route('suppliers.index')
            ->with('status', 'Supplier created.');
    }

    public function update(UpdateSupplierRequest $request, Suppliers $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()
            ->back()
            ->with('status', 'Supplier updated.');
    }

    public function destroy(Suppliers $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('status', 'Supplier deleted.');
    }

    public function import(ImportSupplierRequest $request, Suppliers $supplier, SupplierImportService $importer): RedirectResponse
    {
        $json     = (string) file_get_contents($request->file('file')->getRealPath());
        $strategy = $request->string('strategy')->toString();
        $dryRun   = $request->boolean('dry_run');

        try {
            $summary = $importer->import($supplier, $json, $strategy, $dryRun);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('suppliers.show', $supplier)
                ->withErrors(['file' => $e->getMessage()])
                ->with('reopen_import', true);
        }

        $redirect = redirect()->route('suppliers.show', $supplier);

        if (!empty($summary['conflicts'])) {
            $redirect->with('import_conflicts', $summary['conflicts'])
                     ->with('reopen_import', true);
        }

        if ($strategy === SupplierImportService::STRATEGY_REJECT && !$summary['applied']) {
            return $redirect->with('status', 'Import rejected: conflicts detected.');
        }

        $message = sprintf(
            '%sLayups: %d created, %d updated, %d skipped, %d duplicated. Layers: %d created, %d updated.',
            $dryRun ? 'Dry run — nothing was saved. ' : 'Import complete. ',
            $summary['created_layups'],
            $summary['updated_layups'],
            $summary['skipped_layups'],
            $summary['duplicated_layups'],
            $summary['created_layers'],
            $summary['updated_layers'],
        );

        return $redirect->with('status', $message);
    }

    public function export(Suppliers $supplier): StreamedResponse
    {
        $supplier->load(['layups.layers' => function ($query) {
            $query->orderBy('layer_order');
        }]);

        $payload = [
            'layups' => $supplier->layups->map(fn ($layup) => [
                'name'   => $layup->name,
                'layers' => $layup->layers->map(fn ($layer) => [
                    'layer_order' => $layer->layer_order,
                    'thickness'   => $layer->thickness,
                    'width'       => $layer->width,
                    'angle'       => $layer->angle,
                ])->values(),
            ])->values(),
        ];

        $filename = sprintf('supplier-%d-%s-%s.json',
            $supplier->id,
            Str::slug($supplier->name),
            now()->format('Ymd-His')
        );

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}
