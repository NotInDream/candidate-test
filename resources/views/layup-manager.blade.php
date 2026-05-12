<x-app-layout>
    <div class="flex flex-col  w-full p-[32px] dark:text-gray-100">
        {{-- Breadcrumbs --}}
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[14px]">
            <a href="{{ route('suppliers.index') }}"
               class="font-medium text-gray-500 hover:text-[#3F7A5C] hover:cursor-pointer transition-colors duration-300">
                Suppliers
            </a>
            <span class="text-gray-400" aria-hidden="true">/</span>
            <span class="font-medium text-gray-900 dark:text-gray-100" aria-current="page">{{ $supplier->name }}</span>
        </nav>

        <div class="flex justify-between items-center w-full p-[24px] my-[16px] rounded-[8px] dark:text-gray-100 bg-gray-800">
            {{-- Supplier --}}
            <div class="flex flex-col">
                <div class="flex gap-[12px]">
                    <h1 class="font-bold text-[24px]">{{$supplier->name}}</h1>
                    <div class="bg-[#3F7A5C] flex items-center px-[12px] rounded-full">
                        <h3 class="text-[12px] font-bold">Active Partner</h3>
                    </div>
                </div>
                <h2 class="text-size[14px] font-mono">ID: {{ $supplier->id }} </h2>
            </div>

            <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'edit-supplier')"
                class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px] text-white hover:bg-[#356a4f] transition-colors">
                <x-lucide-pencil class="w-4 h-4" />
                <span class="text-[14px] font-bold"> Edit Supplier</span>
            </button>
        </div>

        <x-form-modal
            name="edit-supplier"
            title="Edit Supplier"
            :action="route('suppliers.update', $supplier)"
            method="PATCH"
            submit-label="Save"
            :show="$errors->any() && old('_supplier_id') !== null"
        >
            <input type="hidden" name="_supplier_id" value="{{ $supplier->id }}">
            <div class="text-left">
                <x-input-label for="supplier-name" value="Name" />
                <x-text-input id="supplier-name" name="name" type="text"
                              class="mt-1 block w-full"
                              :value="old('name', $supplier->name)"
                              required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
        </x-form-modal>

        <div class="flex justify-between items-center w-full p-[24px] my-[16px] rounded-[8px] dark:text-gray-100 bg-gray-800">
            <h1>Associated Layups</h1>

            <div class="flex gap-[8px]">
                <div class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px] text-gray-100">
                    <x-lucide-upload class="w-4 h-4" />
                    <h2 class="text-[14px] font-bold"> Import</h2>
                </div>
                <a href="{{ route('suppliers.export', $supplier) }}"
                   class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px] text-gray-100 hover:bg-[#356a4f] transition-colors">
                    <x-lucide-download class="w-4 h-4" />
                    <span class="text-[14px] font-bold"> Export</span>
                </a>
                <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'add-layup')"
                    class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px] text-white hover:bg-[#356a4f] transition-colors">
                    <x-lucide-plus class="w-4 h-4" />
                    <span class="text-[14px] font-bold"> Add Layup</span>
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-[16px] px-[16px] py-[10px] rounded-[8px] bg-green-100 text-green-800 text-[14px]">
                {{ session('status') }}
            </div>
        @endif

        <x-form-modal
            name="add-layup"
            title="Add Layup"
            :action="route('layups.store', $supplier)"
            submit-label="Create"
            :show="$errors->any() && !old('_layup_id')"
        >
            <div class="text-left">
                <x-input-label for="layup-name" value="Name" />
                <x-text-input id="layup-name" name="name" type="text"
                              class="mt-1 block w-full"
                              :value="old('name')"
                              required autofocus placeholder="Layup name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
        </x-form-modal>

        <div class="w-full rounded-[8px] bg-white border border-gray-200 overflow-hidden mt-[16px]">
            <table class="w-full text-left text-[14px]">
                <thead class="bg-gray-50 text-gray-600 text-[12px] uppercase">
                    <tr>
                        <th class="px-[16px] py-[12px] font-semibold">Layup Id</th>
                        <th class="px-[16px] py-[12px] font-semibold">Name</th>
                        <th class="px-[16px] py-[12px] font-semibold">Thickness</th>
                        <th class="px-[16px] py-[12px] font-semibold">Ply Count</th>
                        <th class="px-[16px] py-[12px] font-semibold">Species/Grade</th>
                        <th class="px-[16px] py-[12px] font-semibold">Revisions</th>
                        <th class="px-[16px] py-[12px] font-semibold">Status</th>
                        <th class="px-[16px] py-[12px] font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($layups as $layup)
                        <tr class="h-[73px] border-t border-gray-200">
                            <td class="px-[16px] font-medium text-gray-900">
                                <div class="flex items-center gap-[16px]">
                                    <div class="flex flex-col">
                                        <h3 class="text-gray-400 font-mono">ID: {{ $layup->id }}</h3>
                                    </div>
                                </div>
                            </td>
                            <td class="px-[16px] text-gray-700">
                                <a href="{{ route('layups.show', [$supplier, $layup]) }}"
                                   class="hover:text-green-800 hover:cursor-pointer transition-colors duration-300">
                                    {{ $layup->name }}
                                </a>
                            </td>
                            <td class="px-[16px] text-gray-700">{{ $layup->layers_thickness_sum ?? 0 }}mm</td>
                            <td class="px-[16px] text-gray-700">{{ $layup->layers_count }}</td>
                            <td class="px-[16px] text-gray-700"></td>
                            <td class="px-[16px] text-gray-700 "></td>
                            <td class="px-[16px] text-gray-700 "></td>
                            <td class="px-[16px] text-right">
                                <div class="flex justify-end gap-[8px]">
                                    <button type="button" x-data=""
                                            x-on:click="$dispatch('open-modal', 'edit-layup-{{ $layup->id }}')"
                                            class="p-[6px] rounded-[6px] hover:bg-gray-100" title="Edit">
                                        <x-lucide-pencil class="w-4 h-4 text-gray-700" />
                                    </button>
                                    <button type="button" x-data=""
                                            x-on:click="$dispatch('open-modal', 'delete-layup-{{ $layup->id }}')"
                                            class="p-[6px] rounded-[6px] hover:bg-red-50" title="Delete">
                                        <x-lucide-trash-2 class="w-4 h-4 text-red-600" />
                                    </button>
                                </div>

                                <x-form-modal
                                    name="edit-layup-{{ $layup->id }}"
                                    title="Edit Layup"
                                    :action="route('layups.update', $layup)"
                                    method="PATCH"
                                    submit-label="Save"
                                    :show="$errors->any() && (int) old('_layup_id') === $layup->id"
                                >
                                    <input type="hidden" name="_layup_id" value="{{ $layup->id }}">
                                    <div class="text-left">
                                        <x-input-label for="layup-name-{{ $layup->id }}" value="Name" />
                                        <x-text-input id="layup-name-{{ $layup->id }}" name="name" type="text"
                                                      class="mt-1 block w-full"
                                                      :value="old('name', $layup->name)"
                                                      required />
                                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                    </div>
                                </x-form-modal>

                                <x-modal name="delete-layup-{{ $layup->id }}" :show="false" focusable maxWidth="md">
                                    <form method="POST" action="{{ route('layups.destroy', $layup) }}" class="p-[24px] text-left">
                                        @csrf
                                        @method('DELETE')
                                        <h2 class="text-[18px] font-bold dark:text-gray-100">Delete layup?</h2>
                                        <p class="mt-[8px] text-[14px] dark:text-gray-200">
                                            This will permanently delete <span class="font-semibold">{{ $layup->name }}</span>
                                            and all associated layers. This cannot be undone.
                                        </p>
                                        <div class="flex justify-end gap-[8px] mt-[20px]">
                                            <button type="button" x-on:click="$dispatch('close-modal', 'delete-layup-{{ $layup->id }}')"
                                                    class="px-[16px] py-[8px] rounded-[8px] border border-gray-300 text-[14px] dark:text-gray-100 hover:bg-gray-50 hover:text-gray-700 transition-all duration-250">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                    class="px-[16px] py-[8px] rounded-[8px] bg-red-600 text-white text-[14px] font-bold hover:bg-red-700">
                                                Delete
                                            </button>
                                        </div>
                                    </form>
                                </x-modal>
                            </td>
                        </tr>
                    @empty
                        <tr class="h-[73px] border-t border-gray-200">
                            <td colspan="8" class="px-[16px] text-center text-gray-500">
                                No layups found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200 bg-white">
                        <td colspan="8" class="px-[16px] py-[12px]">
                            <div class="flex items-center justify-between">
                                <p class="text-[14px] text-gray-600">
                                    Showing <span class="font-semibold text-gray-900">{{ $layups->firstItem() ?? 0 }}</span>
                                    to <span class="font-semibold text-gray-900">{{ $layups->lastItem() ?? 0 }}</span>
                                    of <span class="font-semibold text-gray-900">{{ $layups->total() }}</span> results
                                </p>
                                <div class="flex items-center gap-[8px]">
                                    <a href="{{ $layups->previousPageUrl() ?? '#' }}"
                                       class="h-[32px] w-[32px] flex items-center justify-center rounded-full border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $layups->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </a>
                                    <a href="{{ $layups->nextPageUrl() ?? '#' }}"
                                       class="h-[32px] w-[32px] flex items-center justify-center rounded-full border border-gray-300 text-gray-600 hover:bg-gray-50 {{ !$layups->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>


    </div>
</x-app-layout>
