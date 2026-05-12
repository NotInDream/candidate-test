<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLayupRequest;
use App\Http\Requests\UpdateLayupRequest;
use App\Models\CLT_Layups;
use App\Models\Suppliers;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LayupController extends Controller
{
    public function store(StoreLayupRequest $request, Suppliers $supplier): RedirectResponse
    {
        $supplier->layups()->create($request->validated());

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('status', 'Layup created.');
    }

    public function show(Suppliers $supplier, CLT_Layups $layup): View
    {
        $layers = $layup->layers()->orderBy('layer_order')->get();

        return view('layer-manager', [
            'supplier' => $supplier,
            'layup'    => $layup,
            'layers'   => $layers,
        ]);
    }

    public function update(UpdateLayupRequest $request, CLT_Layups $layup): RedirectResponse
    {
        $layup->update($request->validated());

        return redirect()
            ->route('suppliers.show', $layup->supplier_id)
            ->with('status', 'Layup updated.');
    }

    public function destroy(CLT_Layups $layup): RedirectResponse
    {
        $supplierId = $layup->supplier_id;
        $layup->delete();

        return redirect()
            ->route('suppliers.show', $supplierId)
            ->with('status', 'Layup deleted.');
    }
}
