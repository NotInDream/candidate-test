<x-app-layout>

    <div class="flex justify-between items-center w-full p-[32px] dark:text-gray-100">
        {{-- Texts --}}
        <div>
            <h1 class="font-bold text-[24px]">Suppliers</h1>
            <h2 class="text-size[14px]">Manage timber suppliers and material sourcing.</h2>
        </div>

        <div class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px]">
            <img src="{{ asset('assets/plus_icon.svg') }}" alt="">
            <h2 class="text-[14px] font-bold"> Add Supplier</h2>
        </div>
    </div>

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
                        <tr class="h-[73px] border-t border-gray-200">
                            <td class="px-[16px] font-medium text-gray-900">{{ $supplier->name }}</td>
                            <td class="px-[16px] text-gray-700">{{ $supplier->layups_count }}</td>
                            <td class="px-[16px] text-gray-700">{{ $supplier->created_at->format('Y-m-d') }}</td>
                            <td class="px-[16px] text-right">

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
