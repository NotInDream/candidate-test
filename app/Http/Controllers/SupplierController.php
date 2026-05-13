<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportSupplierRequest;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Suppliers;
use App\Services\SupplierImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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
            'layups' => $layups,
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
        $strategy = $request->string('strategy')->toString();
        $dryRun = $request->boolean('dry_run');

        $staged = $this->readStagedImport($supplier, $request->string('staged_token')->toString() ?: null);

        if ($request->hasFile('file')) {
            $json = (string) file_get_contents($request->file('file')->getRealPath());
            $filename = $request->file('file')->getClientOriginalName();
            $reusedStage = false;
        } elseif ($staged !== null) {
            $json = $staged['contents'];
            $filename = $staged['name'];
            $reusedStage = true;
        } else {
            return redirect()
                ->route('suppliers.show', $supplier)
                ->withErrors(['file' => 'Please choose a file to import.'])
                ->with('reopen_import', true);
        }

        try {
            $summary = $importer->import($supplier, $json, $strategy, $dryRun);
        } catch (RuntimeException $e) {
            $this->clearStagedImport($supplier);

            return redirect()
                ->route('suppliers.show', $supplier)
                ->withErrors(['file' => $e->getMessage()])
                ->with('reopen_import', true);
        }

        $redirect = redirect()->route('suppliers.show', $supplier);

        if (! empty($summary['conflicts'])) {
            $redirect->with('import_conflicts', $summary['conflicts'])
                ->with('reopen_import', true);
        }

        // Persist the file across requests when nothing was actually written,
        // so the user doesn't need to re-upload after a dry run or rejection.
        if (! $summary['applied']) {
            $token = $reusedStage
                ? $staged['token']
                : $this->writeStagedImport($supplier, $json, $filename);
            $redirect->with('staged_file_token', $token)
                ->with('staged_file_name', $filename)
                ->with('reopen_import', true);
        } else {
            $this->clearStagedImport($supplier);
        }

        if ($strategy === SupplierImportService::STRATEGY_REJECT && ! $summary['applied']) {
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
                'name' => $layup->name,
                'layers' => $layup->layers->map(fn ($layer) => [
                    'layer_order' => $layer->layer_order,
                    'thickness' => $layer->thickness,
                    'width' => $layer->width,
                    'angle' => $layer->angle,
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

    private function stagedSessionKey(Suppliers $supplier): string
    {
        return "import_staging.{$supplier->id}";
    }

    /**
     * @return array{token:string,name:string,contents:string}|null
     */
    private function readStagedImport(Suppliers $supplier, ?string $token): ?array
    {
        if ($token === null || $token === '') {
            return null;
        }

        $entry = session($this->stagedSessionKey($supplier));
        if (! is_array($entry) || ($entry['token'] ?? null) !== $token) {
            return null;
        }
        if (! Storage::disk('local')->exists($entry['path'])) {
            return null;
        }

        return [
            'token' => $entry['token'],
            'name' => $entry['name'],
            'contents' => (string) Storage::disk('local')->get($entry['path']),
        ];
    }

    private function writeStagedImport(Suppliers $supplier, string $json, string $name): string
    {
        $this->clearStagedImport($supplier);

        $token = Str::random(40);
        $path = "import-staging/{$supplier->id}/{$token}.json";
        Storage::disk('local')->put($path, $json);

        session([$this->stagedSessionKey($supplier) => [
            'token' => $token,
            'name' => $name,
            'path' => $path,
        ]]);

        return $token;
    }

    private function clearStagedImport(Suppliers $supplier): void
    {
        $entry = session($this->stagedSessionKey($supplier));
        if (is_array($entry) && Storage::disk('local')->exists($entry['path'] ?? '')) {
            Storage::disk('local')->delete($entry['path']);
        }
        session()->forget($this->stagedSessionKey($supplier));
    }
}
