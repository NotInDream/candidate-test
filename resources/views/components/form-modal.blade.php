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
    <form method="POST" action="{{ $action }}" class="p-[24px]"
          x-data="{ submitting: false }"
          x-on:submit="submitting = true">
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
                    :disabled="submitting"
                    class="px-[16px] py-[8px] rounded-[8px] border border-gray-300 text-[14px] dark:text-gray-100 transition-all duration-250 hover:text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ $cancelLabel }}
            </button>
            <button type="submit"
                    :disabled="submitting"
                    class="px-[16px] py-[8px] rounded-[8px] bg-[#3F7A5C] text-white text-[14px] font-bold hover:bg-[#356a4f] disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-[6px]">
                <svg x-show="submitting" x-cloak class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span x-text="submitting ? 'Working…' : '{{ $submitLabel }}'">{{ $submitLabel }}</span>
            </button>
        </div>
    </form>
</x-modal>
