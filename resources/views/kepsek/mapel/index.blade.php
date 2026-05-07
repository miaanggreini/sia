@extends('layouts.kepsek')

@section('content')
@php
    $items = $items ?? $mapels ?? $mataPelajaran ?? collect();
@endphp

<div class="space-y-6">

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Mata Pelajaran</h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola data mata pelajaran yang digunakan dalam kegiatan akademik.
            </p>
        </div>
    </div>

    @if(session('ok'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('ok') }}
        </div>
    @endif

    @if(session('err'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('err') }}
        </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
<form id="searchMapelForm" method="GET" action="{{ route('kepala_sekolah.data.mapel') }}">            <label class="mb-1 block text-sm font-medium text-gray-700">Cari Mata Pelajaran</label>

            <div class="relative">
                <input type="text"
                       id="searchMapelInput"
                       name="q"
                       value="{{ $q ?? request('q') }}"
                       placeholder="Cari nama mapel atau kelompok..."
                       autocomplete="off"
                       class="w-full rounded-xl border-gray-300 pl-4 pr-10 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                @if(request('q'))
                    <a href="{{ route('kepala_sekolah.data.mapel') }}"
                       class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500">
                        ✕
                    </a>
                @endif
            </div>

            <p class="mt-2 text-xs text-gray-400">
                Pencarian berjalan otomatis setelah mengetik.
            </p>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        @if($items->isEmpty())
            <div class="px-5 py-12 text-center">
                <h3 class="text-sm font-semibold text-gray-700">Data mata pelajaran tidak ditemukan</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Data mata pelajaran belum tersedia atau tidak sesuai pencarian.
                </p>

                <a href="{{ route('kepala_sekolah.data.mapel.create') }}"
                   class="mt-4 inline-flex rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Tambah Mapel
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">No</th>
                            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nama Mata Pelajaran</th>
                            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Kelompok</th>
                            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                            <th class="sticky right-0 whitespace-nowrap bg-gray-50 px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($items as $i => $row)
                            <tr class="transition hover:bg-gray-50">
                                <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                    {{ method_exists($items, 'firstItem') ? $items->firstItem() + $i : $loop->iteration }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-semibold text-gray-800">
                                        {{ $row->nama_mapel ?? $row->nama ?? '-' }}
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                    {{ $row->kelompok ?? '-' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @if(($row->aktif ?? $row->status ?? 'aktif') == 'aktif' || ($row->aktif ?? null) == 1)
                                        <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>

                                <td class="sticky right-0 bg-white px-5 py-4 min-w-[220px]">
                                    <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                        <a href="{{ route('kepala_sekolah.data.mapel.edit', $row) }}"
                                           class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                            Edit
                                        </a>

                                        <button type="button"
                                                onclick="openDeleteModal('{{ route('kepala_sekolah.data.mapel.destroy', $row) }}', '{{ addslashes($row->nama_mapel ?? $row->nama ?? '-') }}')"
                                                class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($items, 'links'))
                <div class="border-t bg-gray-50 px-5 py-4">
                    {{ $items->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Modal Hapus --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteModal()"></div>

    <div class="relative flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <h2 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus</h2>

            <p class="mt-2 text-sm leading-6 text-gray-600">
                Yakin ingin menghapus mata pelajaran
                <span id="deleteMapelName" class="font-semibold text-gray-900"></span>?
            </p>

            <p class="mt-2 text-sm text-red-600">
                Jika mata pelajaran sudah digunakan pada jadwal atau nilai, sistem akan menolak penghapusan.
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        onclick="closeDeleteModal()"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Batal
                </button>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const searchMapelInput = document.getElementById('searchMapelInput');
    const searchMapelForm = document.getElementById('searchMapelForm');

    if (searchMapelInput && searchMapelForm) {
        let timer;

        searchMapelInput.addEventListener('input', function () {
            clearTimeout(timer);

            timer = setTimeout(function () {
                searchMapelForm.submit();
            }, 500);
        });
    }

    function openDeleteModal(actionUrl, mapelName) {
        document.getElementById('deleteForm').action = actionUrl;
        document.getElementById('deleteMapelName').textContent = mapelName;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('deleteForm').action = '';
        document.getElementById('deleteMapelName').textContent = '';
        document.body.classList.remove('overflow-hidden');
    }
</script>
@endsection