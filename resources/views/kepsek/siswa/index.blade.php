@extends('layouts.kepsek')

@section('title', 'Data Siswa')

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-800">Data Siswa</h1>
        <p class="mt-1 text-sm text-gray-500">
            Monitoring dan pengelolaan data siswa oleh kepala sekolah.
        </p>
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

    {{-- Search otomatis --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <form id="searchSiswaForm" method="GET" action="{{ route('kepala_sekolah.data.siswa') }}">
            <label class="mb-1 block text-sm font-medium text-gray-700">Cari Data Siswa</label>

            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14z"/>
                    </svg>
                </span>

                <input type="text"
                       id="searchSiswaInput"
                       name="q"
                       value="{{ $q ?? request('q') }}"
                       placeholder="Cari nama, NIS, NISN, email, no HP, atau orang tua..."
                       autocomplete="off"
                       class="w-full rounded-xl border-gray-300 pl-10 pr-10 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                @if(request('q'))
                    <a href="{{ route('kepala_sekolah.data.siswa') }}"
                       class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm font-semibold text-gray-400 hover:text-red-500">
                        ✕
                    </a>
                @endif
            </div>

            <p class="mt-2 text-xs text-gray-400">
                Pencarian berjalan otomatis setelah mengetik.
            </p>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
                        <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Foto</th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">NIS</th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Siswa</th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jenis Kelamin</th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Alamat</th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No HP</th>
                        <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Email</th>
                        <th class="sticky right-0 whitespace-nowrap bg-gray-50 px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($items as $row)
                        <tr class="transition hover:bg-gray-50">
                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $items->firstItem() + $loop->index }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <img src="{{ $row->foto_url }}"
                                     alt="Foto {{ $row->nama }}"
                                     class="mx-auto h-14 w-14 rounded-xl border border-gray-200 object-cover shadow-sm"
                                     onerror="this.onerror=null;this.src='{{ asset('images/no-photo.png') }}';">
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $row->nis ?: '—' }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-800">{{ $row->nama }}</div>
                                <div class="mt-1 text-xs text-gray-500">
                                    NISN: {{ $row->nisn ?? '—' }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                @if(($row->jk ?? '') === 'L')
                                    <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                        Laki-laki
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full border border-pink-200 bg-pink-50 px-2.5 py-1 text-xs font-semibold text-pink-700">
                                        Perempuan
                                    </span>
                                @endif
                            </td>

                            <td class="max-w-xs px-5 py-4 text-gray-600">
                                <div class="line-clamp-2">
                                    {{ $row->alamat ?: '—' }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $row->no_hp ?: '—' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $row->email ?: '—' }}
                            </td>

                            <td class="sticky right-0 bg-white px-5 py-4 min-w-[230px]">
                                <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                                    <a href="{{ route('kepala_sekolah.data.siswa.show', $row) }}"
                                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">
                                        Detail
                                    </a>

                                    <a href="{{ route('kepala_sekolah.data.siswa.edit', $row) }}"
                                       class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                        Ubah
                                    </a>

                                    <button type="button"
                                            onclick="openDeleteModal('{{ route('kepala_sekolah.data.siswa.destroy', $row) }}', '{{ addslashes($row->nama) }}')"
                                            class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
                                <h3 class="text-sm font-semibold text-gray-700">Data siswa tidak ditemukan</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Data siswa belum tersedia atau tidak sesuai pencarian.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t bg-gray-50 px-5 py-4">
            {{ $items->withQueryString()->links() }}
        </div>
    </div>
</div>

{{-- Modal Hapus --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteModal()"></div>

    <div class="relative flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <h2 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus Data</h2>

            <p class="mt-2 text-sm leading-6 text-gray-600">
                Yakin ingin menghapus data siswa
                <span id="deleteSiswaName" class="font-semibold text-gray-900"></span>?
            </p>

            <p class="mt-2 text-sm text-red-600">
                Jika data siswa sudah digunakan pada data akademik, sistem akan menolak penghapusan.
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
    const searchSiswaInput = document.getElementById('searchSiswaInput');
    const searchSiswaForm = document.getElementById('searchSiswaForm');

    if (searchSiswaInput && searchSiswaForm) {
        let typingTimer;

        searchSiswaInput.addEventListener('input', function () {
            clearTimeout(typingTimer);

            typingTimer = setTimeout(function () {
                searchSiswaForm.submit();
            }, 500);
        });
    }

    function openDeleteModal(actionUrl, siswaName) {
        document.getElementById('deleteForm').action = actionUrl;
        document.getElementById('deleteSiswaName').textContent = siswaName;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('deleteForm').action = '';
        document.getElementById('deleteSiswaName').textContent = '';
        document.body.classList.remove('overflow-hidden');
    }
</script>
@endsection