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

            <div class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px]">
                <img src="{{ asset('assets/edit_icon.svg') }}" alt="">
                <h2 class="text-[14px] font-bold"> Edit Supplier</h2>
            </div>
        </div>

        <div class="flex justify-between items-center w-full p-[24px] my-[16px] rounded-[8px] dark:text-gray-100 bg-gray-800">
            <h1>Associated Layups</h1>

            <div class="flex gap-[8px]">
                <div class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px]">
                    <img src="{{ asset('assets/edit_icon.svg') }}" alt="">
                    <h2 class="text-[14px] font-bold"> Import</h2>
                </div>
                <div class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px]">
                    <img src="{{ asset('assets/edit_icon.svg') }}" alt="">
                    <h2 class="text-[14px] font-bold"> Export</h2>
                </div>
                <div class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px]">
                    <img src="{{ asset('assets/plus_icon.svg') }}" alt="">
                    <h2 class="text-[14px] font-bold"> Add Layup</h2>
                </div>
            </div>
        </div>

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
                            <td class="px-[16px] text-gray-700">{{ $layup->name }}</td>
                            <td class="px-[16px] text-gray-700"></td>
                            <td class="px-[16px] "></td>
                            <td class="px-[16px] "></td>
                            <td class="px-[16px] "></td>
                            <td class="px-[16px] "></td>
                            <td class="px-[16px] text-right"></td>
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
