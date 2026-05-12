<x-app-layout>

    <div class="flex justify-between items-center w-full p-[32px] dark:text-gray-100">
        {{-- Texts --}}
        <div>
            <h1 class="font-bold text-[24px]">Suppliers</h1>
            <h2 class="text-size[14px]">Manage timber suppliers and material sourcing.</h2>
        </div>

        <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'add-supplier')"
            class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px] text-white hover:bg-[#356a4f] transition-colors">
            <img src="{{ asset('assets/plus_icon.svg') }}" alt="">
            <span class="text-[14px] font-bold"> Add Supplier</span>
        </button>
    </div>

    @if (session('status'))
        <div class="mx-[32px] mb-[16px] px-[16px] py-[10px] rounded-[8px] bg-green-100 text-green-800 text-[14px]">
            {{ session('status') }}
        </div>
    @endif

    <x-form-modal
        name="add-supplier"
        title="Add Supplier"
        :action="route('suppliers.store')"
        submit-label="Create"
        :show="$errors->any()"
    >
        <div>
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" name="name" type="text"
                          class="mt-1 block w-full"
                          :value="old('name')"
                          required autofocus placeholder="Supplier name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
    </x-form-modal>

    <div class="p-[32px]">
        {{-- Tools --}}
        <div class="flex w-full justify-between">
            <div class="flex w-[320px] h-[40px] gap-[13px] bg-white rounded-[8px]">
                <div class="flex items-center justify-end w-[27px]">
                    <img src="{{ asset('assets/search_icon.svg') }}" alt="" srcset="">
                </div>
                <input type="text" class="border-0 w-full rounded-r-[8px] p-0 focus:outline-none focus:ring-0 focus:border-0 " placeholder="Search suppliers by name...">
            </div>
            <div class="flex gap-[8px]">
                <div class="flex h-[38px] bg-white rounded-[8px] border-[1px] py-[8px] px-[12px] items-center text-[14px] gap-[8px]">
                    <img src="{{ asset("assets/filter_icon.svg") }}" alt="" class="w-[13.5px] h-[9px]">
                    <h3>Filter</h3>
                </div>
                <div class="flex h-[38px] bg-white rounded-[8px] border-[1px] py-[8px] px-[12px] items-center text-[14px] gap-[4px]">
                    <img src="{{ asset("assets/download_icon.svg") }}" alt="" class="w-[12px] h-[12px]">
                    <h3>Export</h3>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="w-full rounded-[8px] bg-white border border-gray-200 overflow-hidden mt-[16px]">
            <table class="w-full text-left text-[14px]">
                <thead class="bg-gray-50 text-gray-600 text-[12px] uppercase">
                    <tr>
                        <th class="px-[16px] py-[12px] font-semibold">Name</th>
                        <th class="px-[16px] py-[12px] font-semibold">Total Layups</th>
                        <th class="px-[16px] py-[12px] font-semibold">Created At</th>
                        <th class="px-[16px] py-[12px] font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        @php
                            preg_match_all('/[A-Z]/', $supplier->name, $matches);
                            $initials = implode('', array_slice($matches[0], 0, 2)) ?: strtoupper(substr($supplier->name, 0, 2));

                            $palette = ['#3F7A5C', '#B45309', '#1D4ED8', '#9333EA', '#DB2777', '#0F766E', '#CA8A04', '#DC2626'];
                            $bgColor = $palette[$supplier->id % count($palette)];
                        @endphp
                        <tr class="h-[73px] border-t border-gray-200">
                            <td class="px-[16px] font-medium text-gray-900">
                                <div class="flex items-center gap-[16px]">
                                    {{-- Initials --}}
                                    <div class="w-[40px] h-[40px] rounded-full font-bold text-white justify-center flex items-center"
                                         style="background-color: {{ $bgColor }}">
                                        <h1>{{ $initials }}</h1>
                                    </div>
                                    <div class="flex flex-col">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="hover:text-green-800 hover:cursor-pointer transition-colors duration-300">{{ $supplier->name }}</a>
                                        <h3 class="text-gray-400 font-mono">ID: {{ $supplier->id }}</h3>

                                    </div>
                                </div>
                            </td>
                            <td class="px-[16px] text-gray-700">{{ $supplier->layups_count }}</td>
                            <td class="px-[16px] text-gray-700">{{ $supplier->created_at->format('Y-m-d') }}</td>
                            <td class="px-[16px] text-right">
                                <div class="flex justify-end gap-[8px]">
                                    <button type="button" x-data=""
                                            x-on:click="$dispatch('open-modal', 'edit-supplier-{{ $supplier->id }}')"
                                            class="p-[6px] rounded-[6px] hover:bg-gray-100" title="Edit">
                                        <img src="{{ asset('assets/edit_icon.svg') }}" alt="Edit" class="w-[16px] h-[16px]">
                                    </button>
                                    <button type="button" x-data=""
                                            x-on:click="$dispatch('open-modal', 'delete-supplier-{{ $supplier->id }}')"
                                            class="p-[6px] rounded-[6px] hover:bg-red-50" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[16px] h-[16px] text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- Edit modal --}}
                                <x-form-modal
                                    name="edit-supplier-{{ $supplier->id }}"
                                    title="Edit Supplier"
                                    :action="route('suppliers.update', $supplier)"
                                    method="PATCH"
                                    submit-label="Save"
                                    :show="$errors->any() && (int) old('_supplier_id') === $supplier->id"
                                >
                                    <input type="hidden" name="_supplier_id" value="{{ $supplier->id }}">
                                    <div class="text-left">
                                        <x-input-label for="name-{{ $supplier->id }}" value="Name" />
                                        <x-text-input id="name-{{ $supplier->id }}" name="name" type="text"
                                                      class="mt-1 block w-full"
                                                      :value="old('name', $supplier->name)"
                                                      required />
                                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                    </div>
                                </x-form-modal>

                                {{-- Delete confirm modal --}}
                                <x-modal name="delete-supplier-{{ $supplier->id }}" :show="false" focusable maxWidth="md">
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="p-[24px] text-left">
                                        @csrf
                                        @method('DELETE')
                                        <h2 class="text-[18px] font-bold text-gray-900">Delete supplier?</h2>
                                        <p class="mt-[8px] text-[14px] text-gray-600">
                                            This will permanently delete <span class="font-semibold">{{ $supplier->name }}</span>
                                            and all associated layups and layers. This cannot be undone.
                                        </p>
                                        <div class="flex justify-end gap-[8px] mt-[20px]">
                                            <button type="button" x-on:click="$dispatch('close-modal', 'delete-supplier-{{ $supplier->id }}')"
                                                    class="px-[16px] py-[8px] rounded-[8px] border border-gray-300 text-[14px] text-gray-700 hover:bg-gray-50">
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
                            <td colspan="4" class="px-[16px] text-center text-gray-500">
                                No suppliers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200 bg-white">
                        <td colspan="4" class="px-[16px] py-[12px]">
                            <div class="flex items-center justify-between">
                                <p class="text-[14px] text-gray-600">
                                    Showing <span class="font-semibold text-gray-900">{{ $suppliers->firstItem() ?? 0 }}</span>
                                    to <span class="font-semibold text-gray-900">{{ $suppliers->lastItem() ?? 0 }}</span>
                                    of <span class="font-semibold text-gray-900">{{ $suppliers->total() }}</span> results
                                </p>
                                <div class="flex items-center gap-[8px]">
                                    <a href="{{ $suppliers->previousPageUrl() ?? '#' }}"
                                       class="h-[32px] w-[32px] flex items-center justify-center rounded-full border border-gray-300 text-gray-600 hover:bg-gray-50 {{ $suppliers->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </a>
                                    <a href="{{ $suppliers->nextPageUrl() ?? '#' }}"
                                       class="h-[32px] w-[32px] flex items-center justify-center rounded-full border border-gray-300 text-gray-600 hover:bg-gray-50 {{ !$suppliers->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
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
