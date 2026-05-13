<x-app-layout>
    <div class="flex flex-col  w-full p-[32px] dark:text-gray-100">
        {{-- Breadcrumbs --}}
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[14px]">
            <a href="{{ route('suppliers.index') }}"
                class="font-medium text-gray-500 hover:text-[#3F7A5C] hover:cursor-pointer transition-colors duration-300">
                Suppliers
            </a>
            <span class="text-gray-400" aria-hidden="true">/</span>
            <a href="{{ route('suppliers.show', $supplier) }}"
                class="font-medium text-gray-500 hover:text-[#3F7A5C] hover:cursor-pointer transition-colors duration-300">{{ $supplier->name }}</a>
            <span class="text-gray-400" aria-hidden="true">/</span>
            <span class="font-medium text-gray-900 dark:text-gray-100" aria-current="page">{{ $layup->name }}</span>
        </nav>

        <div
            class="flex justify-between items-center w-full p-[24px] my-[16px] rounded-[8px] dark:text-gray-100 bg-gray-800">
            {{-- Layup --}}
            <div class="flex flex-col">
                <div class="flex gap-[12px]">
                    <h1 class="font-bold text-[24px]">Layup Specification: {{ $layup->id }}</h1>
                    <div class="bg-[#3F7A5C] flex items-center px-[12px] rounded-full">
                        <h3 class="text-[12px] font-bold">Active Partner</h3>
                    </div>
                </div>
                <h2 class="text-size[14px] font-mono">{{ $layup->name }} </h2>
            </div>

            <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'edit-layup')"
                class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px] text-white hover:bg-[#356a4f] transition-colors">
                <x-lucide-pencil class="w-4 h-4" />
                <span class="text-[14px] font-bold"> Edit Layup</span>
            </button>
        </div>

        <x-form-modal name="edit-layup" title="Edit Layup" :action="route('layups.update', $layup)" method="PATCH" submit-label="Save"
            :show="$errors->any() && old('_layup_id') !== null">
            <input type="hidden" name="_layup_id" value="{{ $layup->id }}">
            <div class="text-left">
                <x-input-label for="layup-name" value="Name" />
                <x-text-input id="layup-name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $layup->name)"
                    required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
        </x-form-modal>

        @if (session('status'))
            <div class="mb-[16px] px-[16px] py-[10px] rounded-[8px] bg-green-100 text-green-800 text-[14px]">
                {{ session('status') }}
            </div>
        @endif

        <x-form-modal name="add-layer" title="Add Layer" :action="route('layers.store', [$supplier, $layup])" submit-label="Create" :show="$errors->any() && !old('_layer_id')">
            <div class="text-left space-y-[12px]">
                <div>
                    <x-input-label for="layer-order" value="Order" />
                    <x-text-input id="layer-order" name="layer_order" type="number" class="mt-1 block w-full"
                        :value="old('layer_order')" required autofocus />
                    <x-input-error :messages="$errors->get('layer_order')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="layer-thickness" value="Thickness (mm)" />
                    <x-text-input id="layer-thickness" name="thickness" type="number" step="0.01"
                        class="mt-1 block w-full" :value="old('thickness')" required />
                    <x-input-error :messages="$errors->get('thickness')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="layer-width" value="Width (mm)" />
                    <x-text-input id="layer-width" name="width" type="number" step="0.01"
                        class="mt-1 block w-full" :value="old('width')" required />
                    <x-input-error :messages="$errors->get('width')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="layer-angle" value="Angle (°)" />
                    <x-text-input id="layer-angle" name="angle" type="number" step="0.01"
                        class="mt-1 block w-full" :value="old('angle')" required />
                    <x-input-error :messages="$errors->get('angle')" class="mt-2" />
                </div>
            </div>
        </x-form-modal>

        <div class="flex gap-[16px] items-start mt-[16px]">
            {{-- Structure Visualizer --}}
            <div class="flex-1 min-w-0 rounded-[8px] bg-white border border-gray-200 p-[20px] order-2">
                <div class="flex justify-between items-center mb-[12px]">
                    <h2 class="font-bold text-[16px] text-gray-900">Structure Visualizer</h2>
                    <div class="flex items-center gap-[16px] text-[12px] text-gray-600">
                        <div class="flex items-center gap-[6px]">
                            <span class="inline-block w-[12px] h-[12px] rounded-full"
                                style="background-color:#E8C9A0"></span>
                            Longitudinal (0°)
                        </div>
                        <div class="flex items-center gap-[6px]">
                            <span class="inline-block w-[12px] h-[12px] rounded-full"
                                style="background-color:#B8824A"></span>
                            Transverse (90°)
                        </div>
                    </div>
                </div>

                @if ($layers->isEmpty())
                    <p class="text-center text-gray-500 text-[13px] py-[24px]">No layers to visualize yet.</p>
                @else
                    @php
                        $maxThickness = max($layers->max('thickness'), 1);
                        $maxWidth = max($layers->max('width'), 1);
                    @endphp
                    <div class="flex gap-[16px]">
                        {{-- Side labels --}}
                        <div
                            class="flex flex-col justify-between text-[10px] font-bold text-gray-500 tracking-wider py-[8px] w-[64px] shrink-0">
                            <div>
                                <div>TOP</div>
                                <div class="text-gray-400">(OUTSIDE)</div>
                            </div>
                            <div>
                                <div>BOTTOM</div>
                                <div class="text-gray-400">(INSIDE)</div>
                            </div>
                        </div>

                        {{-- Stack --}}
                        <div
                            class="flex-1 bg-gray-50 rounded-[12px] py-[20px] px-[40px] flex flex-col gap-[8px] items-center">
                            @foreach ($layers as $layer)
                                @php
                                    $isLong = (float) $layer->angle == 0.0;
                                    $bg = $isLong ? '#E8C9A0' : '#B8824A';
                                    $textCol = $isLong ? 'text-gray-800' : 'text-white';
                                    $heightPx = 28 + (int) round(((float) $layer->thickness / $maxThickness) * 36);
                                    $widthPct = 50 + (int) round(((float) $layer->width / $maxWidth) * 50);
                                @endphp
                                <div class="rounded-[6px] flex items-center justify-between px-[16px] shadow-sm"
                                    style="background-color: {{ $bg }}; height: {{ $heightPx }}px; width: {{ $widthPct }}%;">
                                    <span class="font-bold text-[13px] {{ $textCol }}">
                                        L{{ $layer->layer_order }}
                                        ({{ rtrim(rtrim(number_format((float) $layer->thickness, 2), '0'), '.') }}mm)
                                    </span>
                                    @if ($isLong)
                                        <x-lucide-arrow-up class="w-4 h-4 {{ $textCol }}" />
                                    @else
                                        <x-lucide-rotate-cw class="w-4 h-4 {{ $textCol }}" />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex-1 min-w-0 rounded-[8px] bg-white border border-gray-200 overflow-hidden order-1">
                <div class="flex justify-between items-center px-[20px] py-[16px] border-b border-gray-200">
                    <h2 class="font-bold text-[16px] text-gray-900">Layer Composition</h2>
                    <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'add-layer')"
                        class="flex items-center gap-[4px] text-[14px] font-semibold text-[#3F7A5C] hover:text-[#356a4f]">
                        <x-lucide-plus class="w-4 h-4" />
                        Add Layer
                    </button>
                </div>
                <table class="w-full text-left text-[14px]">
                    <thead class="bg-gray-50 text-gray-600 text-[12px] uppercase">
                        <tr>
                            <th class="px-[16px] py-[12px] font-semibold">Order</th>
                            <th class="px-[16px] py-[12px] font-semibold">Thickness</th>
                            <th class="px-[16px] py-[12px] font-semibold">Width</th>
                            <th class="px-[16px] py-[12px] font-semibold">Angle</th>
                            <th class="px-[16px] py-[12px] font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($layers as $layer)
                            <tr class="h-[73px] border-t border-gray-200">
                                <td class="px-[16px] font-medium text-gray-900">
                                    <div class="flex items-center gap-[16px]">
                                        <div class="flex flex-col">
                                            <h3 class="text-gray-400 font-mono">{{ $layer->layer_order }}</h3>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-[16px] text-gray-700">{{ $layer->thickness }}</td>
                                <td class="px-[16px] text-gray-700">{{ $layer->width }}</td>
                                <td class="px-[16px] text-gray-700">{{ $layer->angle }}</td>
                                <td class="px-[16px] text-right">
                                    <div class="flex justify-end gap-[8px]">
                                        <button type="button" x-data=""
                                            x-on:click="$dispatch('open-modal', 'edit-layer-{{ $layer->id }}')"
                                            class="p-[6px] rounded-[6px] hover:bg-gray-100" title="Edit">
                                            <x-lucide-pencil class="w-4 h-4 text-gray-700" />
                                        </button>
                                        <button type="button" x-data=""
                                            x-on:click="$dispatch('open-modal', 'delete-layer-{{ $layer->id }}')"
                                            class="p-[6px] rounded-[6px] hover:bg-red-50" title="Delete">
                                            <x-lucide-trash-2 class="w-4 h-4 text-red-600" />
                                        </button>
                                    </div>

                                    <x-form-modal name="edit-layer-{{ $layer->id }}" title="Edit Layer"
                                        :action="route('layers.update', $layer)" method="PATCH" submit-label="Save" :show="$errors->any() && (int) old('_layer_id') === $layer->id">
                                        <input type="hidden" name="_layer_id" value="{{ $layer->id }}">
                                        <div class="text-left space-y-[12px]">
                                            <div>
                                                <x-input-label :for="'layer-order-' . $layer->id" value="Order" />
                                                <x-text-input :id="'layer-order-' . $layer->id" name="layer_order" type="number"
                                                    class="mt-1 block w-full" :value="old('layer_order', $layer->layer_order)" required />
                                                <x-input-error :messages="$errors->get('layer_order')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label :for="'layer-thickness-' . $layer->id" value="Thickness" />
                                                <x-text-input :id="'layer-thickness-' . $layer->id" name="thickness" type="number"
                                                    step="0.01" class="mt-1 block w-full" :value="old('thickness', $layer->thickness)"
                                                    required />
                                                <x-input-error :messages="$errors->get('thickness')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label :for="'layer-width-' . $layer->id" value="Width" />
                                                <x-text-input :id="'layer-width-' . $layer->id" name="width" type="number"
                                                    step="0.01" class="mt-1 block w-full" :value="old('width', $layer->width)"
                                                    required />
                                                <x-input-error :messages="$errors->get('width')" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-input-label :for="'layer-angle-' . $layer->id" value="Angle" />
                                                <x-text-input :id="'layer-angle-' . $layer->id" name="angle" type="number"
                                                    step="0.01" class="mt-1 block w-full" :value="old('angle', $layer->angle)"
                                                    required />
                                                <x-input-error :messages="$errors->get('angle')" class="mt-2" />
                                            </div>
                                        </div>
                                    </x-form-modal>

                                    <x-modal name="delete-layer-{{ $layer->id }}" :show="false" focusable
                                        maxWidth="md">
                                        <form method="POST" action="{{ route('layers.destroy', $layer) }}"
                                            class="p-[24px] text-left" x-data="{ submitting: false }"
                                            x-on:submit="submitting = true">
                                            @csrf
                                            @method('DELETE')
                                            <h2 class="text-[18px] font-bold text-gray-900 dark:text-gray-100">Delete
                                                layer?</h2>
                                            <p class="mt-[8px] text-[14px] text-gray-600 dark:text-gray-200">
                                                This will permanently delete layer
                                                <span class="font-semibold">#{{ $layer->layer_order }}</span>.
                                                This cannot be undone.
                                            </p>
                                            <div class="flex justify-end gap-[8px] mt-[20px]">
                                                <button type="button"
                                                    x-on:click="$dispatch('close-modal', 'delete-layer-{{ $layer->id }}')"
                                                    :disabled="submitting"
                                                    class="px-[16px] py-[8px] rounded-[8px] border border-gray-300 text-[14px] text-gray-700 dark:text-gray-100 dark:hover:text-gray-700 transition-all duration-250 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                                    Cancel
                                                </button>
                                                <button type="submit" :disabled="submitting"
                                                    class="px-[16px] py-[8px] rounded-[8px] bg-red-600 text-white text-[14px] font-bold hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed">
                                                    <span x-text="submitting ? 'Deleting…' : 'Delete'">Delete</span>
                                                </button>
                                            </div>
                                        </form>
                                    </x-modal>
                                </td>
                            </tr>
                        @empty
                            <tr class="h-[73px] border-t border-gray-200">
                                <td colspan="6" class="px-[16px] text-center text-gray-500">
                                    No layers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
