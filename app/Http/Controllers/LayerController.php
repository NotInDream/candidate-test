<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLayerRequest;
use App\Http\Requests\UpdateLayerRequest;
use App\Models\CLT_Layers;
use App\Models\CLT_Layups;
use App\Models\Suppliers;
use Illuminate\Http\RedirectResponse;

class LayerController extends Controller
{
    public function store(StoreLayerRequest $request, Suppliers $supplier, CLT_Layups $layup): RedirectResponse
    {
        $layup->layers()->create($request->validated());

        return redirect()
            ->route('layups.show', [$supplier, $layup])
            ->with('status', 'Layer created.');
    }

    public function update(UpdateLayerRequest $request, CLT_Layers $layer): RedirectResponse
    {
        $layer->update($request->validated());

        return redirect()
            ->back()
            ->with('status', 'Layer updated.');
    }

    public function destroy(CLT_Layers $layer): RedirectResponse
    {
        $layer->delete();

        return redirect()
            ->back()
            ->with('status', 'Layer deleted.');
    }
}
