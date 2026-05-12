@props([
    'name',
    'title',
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save',
    'cancelLabel' => 'Cancel',
    'show' => false,
    'maxWidth' => 'md',
])

<x-modal :name="$name" :show="$show" :max-width="$maxWidth" focusable>
    <form method="POST" action="{{ $action }}" class="p-[24px]">
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method($method)
        @endif

        <div class="flex justify-between items-center mb-[16px]">
            <h2 class="text-[18px] font-bold text-gray-900 dark:text-gray-100">
                {{ $title }}
            </h2>
            <button type="button" x-on:click="$dispatch('close-modal', '{{ $name }}')"
                    class="text-gray-500 hover:text-gray-700 text-[20px] leading-none">
                &times;
            </button>
        </div>

        <div class="space-y-[12px]">
            {{ $slot }}
        </div>

        <div class="flex justify-end gap-[8px] mt-[20px]">
            <button type="button" x-on:click="$dispatch('close-modal', '{{ $name }}')"
                    class="px-[16px] py-[8px] rounded-[8px] border border-gray-300 text-[14px] dark:text-gray-100 transition-all duration-250 hover:text-gray-700 hover:bg-gray-50">
                {{ $cancelLabel }}
            </button>
            <button type="submit"
                    class="px-[16px] py-[8px] rounded-[8px] bg-[#3F7A5C] text-white text-[14px] font-bold hover:bg-[#356a4f]">
                {{ $submitLabel }}
            </button>
        </div>
    </form>
</x-modal>
