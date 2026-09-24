@props(['supplier'])

<div x-data="{ openCardId: null, openDropdownId: null }" class="space-y-4 md:hidden">
    @forelse ($supplier as $index => $i)
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 transition-all">

            <div class="flex justify-between items-start gap-3">
                {{-- Area Klik untuk Expand --}}
                <div class="flex gap-3 cursor-pointer flex-1 min-w-0"
                    @click="openCardId = (openCardId === {{ $i->id }} ? null : {{ $i->id }})">

                    {{-- Inisial Perusahaan sebagai pengganti Foto --}}
                    <div class="flex-shrink-0">
                        <div
                            class="h-12 w-12 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600 font-bold text-lg border border-blue-100">
                            {{ $supplier->firstItem() + $index }}
                        </div>
                    </div>

                    <div class="flex flex-col justify-center overflow-hidden">
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-gray-900 truncate">{{ $i->nama_supplier ?? 'No Name' }}</h2>
                            <div class="text-xs text-font-mono text-gray-500 mt-0.5">
                                {{ $i->kode ?? 'No Kode' }}
                            </div>
                            <svg class="w-3 h-3 text-gray-400 transition-transform duration-200"
                                :class="openCardId === {{ $i->id }} ? 'rotate-180' : ''" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        @php
                            $colorClass = match ($i->jenis_supplier) {
                                'Barang' => 'text-blue-800',
                                'Bahan Baku' => 'text-amber-800',
                                default => 'text-gray-800',
                            };
                        @endphp
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">
                            <span class="{{ $colorClass }} font-semibold">{{ $i->jenis_supplier }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <button
                            @click.stop="openDropdownId = (openDropdownId === {{ $i->id }} ? null : {{ $i->id }})"
                            @click.away="if(openDropdownId === {{ $i->id }}) openDropdownId = null"
                            class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition text-gray-500 focus:outline-none">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                            </svg>
                        </button>

                        <div x-show="openDropdownId === {{ $i->id }}" x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            class="absolute right-0 mt-2 w-40 bg-white border border-gray-200 rounded-xl shadow-xl z-50 overflow-hidden outline-none">

                            <ul class="flex flex-col text-xs font-medium">
                                @can('supplier.edit')
                                    <li>
                                        <a href="{{ route('supplier.edit', $i->id) }}"
                                            class="flex items-center gap-2 px-4 py-3 text-gray-700 hover:bg-amber-50 hover:text-amber-700 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            Edit Data
                                        </a>
                                    </li>
                                @endcan
                                @if ($i->deleted_at == null)
                                    @can('supplier.delete')
                                        <li>
                                            <form id="delete-form-{{ $i->id }}"
                                                action="{{ route('supplier.destroy', $i->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="confirmDelete({{ $i->id }})"
                                                    class="w-full flex items-center gap-2 px-4 py-3 text-red-600 hover:bg-red-50 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Hapus Data
                                                </button>
                                            </form>
                                        </li>
                                    @endcan
                                @else
                                    @can('supplier.activate')
                                        <li>
                                            <form id="aktif-form-{{ $i->id }}"
                                                action="{{ route('supplier.activate', $i->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="button" onclick="confirmActivate('{{ $i->id }}')"
                                                    class="w-full flex items-center gap-2 px-4 py-3 text-green-600 hover:bg-green-50 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                        viewBox="0 0 12 12">
                                                        <path fill="currentColor"
                                                            d="M9.765 3.205a.75.75 0 0 1 .03 1.06l-4.25 4.5a.75.75 0 0 1-1.075.015L2.22 6.53a.75.75 0 0 1 1.06-1.06l1.705 1.704l3.72-3.939a.75.75 0 0 1 1.06-.03" />
                                                    </svg>
                                                    Aktifkan
                                                </button>
                                            </form>
                                        </li>
                                    @endcan
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="openCardId === {{ $i->id }}" x-collapse x-cloak
                class="mt-3 pt-3 border-t border-gray-50 space-y-3">

                <div class="flex flex-col gap-1">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Supplier dari:</span>
                    <div class="flex items-start gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-400 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span
                            class="text-xs text-gray-700 leading-relaxed">{{ $i->perusahaan->nama_perusahaan ?? '-' }}
                            ({{ $i->perusahaan->kota ?? '-' }})
                        </span>
                    </div>
                </div>

            </div>

        </div>
    @empty
        <div class="text-center py-10 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
            <p class="text-sm text-gray-500 font-medium">Data supplier tidak ditemukan</p>
        </div>
    @endforelse
</div>

<div class="md:hidden mt-6">
    <div class="flex justify-end">
        {{ $supplier->links('vendor.pagination.custom') }}
    </div>
</div>
