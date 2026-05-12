<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Suppliers;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
}
