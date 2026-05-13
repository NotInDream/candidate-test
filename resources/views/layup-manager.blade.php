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
                <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'import-supplier')"
                    class="bg-[#3F7A5C] py-[10px] px-[16px] flex items-center gap-2 rounded-[8px] text-gray-100 hover:bg-[#356a4f] transition-colors">
                    <x-lucide-upload class="w-4 h-4" />
                    <span class="text-[14px] font-bold"> Import</span>
                </button>
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

        {{-- Import Layup Data modal --}}
        <x-modal name="import-supplier" :show="session('reopen_import') || $errors->has('file') || $errors->has('strategy')" focusable maxWidth="xl">
            <form method="POST" action="{{ route('suppliers.import', $supplier) }}" enctype="multipart/form-data"
                  x-data="{
                      submitting: false,
                      dragging: false,
                      fileName: @js(session('staged_file_name', '')),
                      stagedToken: @js(session('staged_file_token', '')),
                      handleFiles(files) {
                          if (files.length > 0) {
                              this.fileName = files[0].name;
                              this.stagedToken = '';
                              this.$refs.fileInput.files = files;
                          }
                      },
                      clearStaged() {
                          this.fileName = '';
                          this.stagedToken = '';
                          this.$refs.fileInput.value = '';
                      }
                  }"
                  x-on:submit="submitting = true">
                @csrf
                <input type="hidden" name="staged_token" :value="stagedToken">

                <div class="px-[24px] pt-[24px] pb-[16px] flex justify-between items-center border-b border-gray-200">
                    <h2 class="text-[18px] font-bold text-gray-900 dark:text-gray-100">Import Layup Data</h2>
                    <button type="button" x-on:click="$dispatch('close-modal', 'import-supplier')"
                            class="text-gray-400 hover:text-gray-600 text-[20px] leading-none">&times;</button>
                </div>

                <div class="px-[24px] py-[20px] space-y-[16px]">
                    {{-- Drop zone --}}
                    <label for="import-file"
                           class="block border-2 border-dashed rounded-[12px] py-[40px] px-[24px] text-center cursor-pointer transition-colors"
                           :class="dragging ? 'border-[#3F7A5C] bg-green-50' : 'border-gray-300 hover:border-gray-400'"
                           x-on:dragover.prevent="dragging = true"
                           x-on:dragleave.prevent="dragging = false"
                           x-on:drop.prevent="dragging = false; handleFiles($event.dataTransfer.files)">
                        <input id="import-file" name="file" type="file"
                               :required="!stagedToken"
                               accept=".json,.csv,application/json,text/csv"
                               x-ref="fileInput"
                               x-on:change="if ($event.target.files[0]) { fileName = $event.target.files[0].name; stagedToken = ''; }"
                               class="sr-only">
                        <div class="w-[48px] h-[48px] mx-auto rounded-full border border-[#3F7A5C]/40 flex items-center justify-center">
                            <x-lucide-cloud-upload class="w-6 h-6 text-[#3F7A5C]" />
                        </div>
                        <p class="mt-[12px] text-[14px] text-gray-700 dark:text-gray-200">
                            <span class="text-[#3F7A5C] font-bold">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-[12px] text-gray-500 mt-[4px]">CSV or JSON up to 10MB</p>
                        <p x-show="fileName" x-cloak
                           class="mt-[10px] text-[12px] font-mono text-gray-700 dark:text-gray-300">
                            <span x-text="fileName"></span>
                            <span x-show="stagedToken" class="ml-[6px] text-[#3F7A5C]">(from previous dry run)</span>
                        </p>
                    </label>
                    <div x-show="stagedToken" x-cloak class="flex justify-end">
                        <button type="button" x-on:click.stop.prevent="clearStaged()"
                                class="text-[12px] text-gray-500 hover:text-red-600 underline">
                            Clear staged file
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('file')" />

                    {{-- Strategy --}}
                    <div>
                        <label for="import-strategy" class="block text-[14px] font-semibold text-gray-900 dark:text-gray-100 mb-[6px]">
                            Conflict Resolution Strategy
                        </label>
                        <select id="import-strategy" name="strategy" required
                                class="block w-full rounded-[8px] border-gray-300 text-[14px] text-gray-800 focus:border-[#3F7A5C] focus:ring-[#3F7A5C]">
                            <option value="skip" class="text-gray-800" {{ old('strategy', 'skip') === 'skip' ? 'selected' : '' }}>Skip conflicts (Default)</option>
                            <option value="overwrite" class="text-gray-800" {{ old('strategy') === 'overwrite' ? 'selected' : '' }}>Overwrite existing</option>
                            <option value="duplicate" class="text-gray-800" {{ old('strategy') === 'duplicate' ? 'selected' : '' }}>Duplicate conflicting layups</option>
                            <option value="reject" class="text-gray-800" {{ old('strategy') === 'reject' ? 'selected' : '' }}>Reject entire import on conflict</option>
                        </select>
                        <x-input-error :messages="$errors->get('strategy')" class="mt-2" />
                    </div>

                    {{-- Dry run --}}
                    <label class="flex items-center justify-between p-[12px] rounded-[8px] border border-gray-200 bg-gray-50 cursor-pointer">
                        <div class="flex items-center gap-[12px]">
                            <input type="checkbox" name="dry_run" value="1" {{ old('dry_run') ? 'checked' : '' }}
                                   class="w-[16px] h-[16px] rounded border-gray-300 text-[#3F7A5C] focus:ring-[#3F7A5C]">
                            <div>
                                <p class="text-[14px] font-semibold text-gray-900">Run as Dry Run</p>
                                <p class="text-[12px] text-gray-500">Simulate the import process without saving changes to the database.</p>
                            </div>
                        </div>
                        <x-lucide-flask-conical class="w-5 h-5 text-gray-400" />
                    </label>

                    {{-- Conflicts alert --}}
                    @if (session('import_conflicts'))
                        <div x-data="{ open: false }" class="p-[14px] rounded-[8px] border border-red-200 bg-red-50 text-red-700">
                            <div class="flex items-start gap-[12px]">
                                <x-lucide-triangle-alert class="w-5 h-5 flex-shrink-0 mt-[2px]" />
                                <div class="flex-1">
                                    <p class="font-bold text-[14px]">Potential Conflicts Detected</p>
                                    <p class="text-[13px] mt-[2px]">
                                        {{ count(session('import_conflicts')) }} layer{{ count(session('import_conflicts')) === 1 ? '' : 's' }}
                                        differ from current data.
                                        <button type="button" x-on:click="open = !open" class="underline font-medium">
                                            <span x-text="open ? 'Hide details' : 'View details'">View details</span>
                                        </button>
                                    </p>
                                    <ul x-show="open" x-cloak class="mt-[8px] list-disc list-inside text-[12px] space-y-[2px]">
                                        @foreach (session('import_conflicts') as $conflict)
                                            <li>{{ $conflict }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="px-[24px] py-[16px] border-t border-gray-200 flex justify-end gap-[8px]">
                    <button type="button" x-on:click="$dispatch('close-modal', 'import-supplier')"
                            :disabled="submitting"
                            class="px-[20px] py-[10px] rounded-[8px] border border-gray-300 text-[14px] font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            class="px-[20px] py-[10px] rounded-[8px] bg-[#3F7A5C] text-white text-[14px] font-bold hover:bg-[#356a4f] flex items-center gap-[8px] disabled:opacity-60 disabled:cursor-not-allowed">
                        <x-lucide-file-up class="w-4 h-4" />
                        <span x-text="submitting ? 'Importing…' : 'Confirm Import'">Confirm Import</span>
                    </button>
                </div>
            </form>
        </x-modal>

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
                                    <form method="POST" action="{{ route('layups.destroy', $layup) }}" class="p-[24px] text-left"
                                          x-data="{ submitting: false }"
                                          x-on:submit="submitting = true">
                                        @csrf
                                        @method('DELETE')
                                        <h2 class="text-[18px] font-bold dark:text-gray-100">Delete layup?</h2>
                                        <p class="mt-[8px] text-[14px] dark:text-gray-200">
                                            This will permanently delete <span class="font-semibold">{{ $layup->name }}</span>
                                            and all associated layers. This cannot be undone.
                                        </p>
                                        <div class="flex justify-end gap-[8px] mt-[20px]">
                                            <button type="button" x-on:click="$dispatch('close-modal', 'delete-layup-{{ $layup->id }}')"
                                                    :disabled="submitting"
                                                    class="px-[16px] py-[8px] rounded-[8px] border border-gray-300 text-[14px] dark:text-gray-100 hover:bg-gray-50 hover:text-gray-700 transition-all duration-250 disabled:opacity-50 disabled:cursor-not-allowed">
                                                Cancel
                                            </button>
                                            <button type="submit"
                                                    :disabled="submitting"
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
