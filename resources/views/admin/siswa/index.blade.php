@extends(auth()->user()?->role === 'kepala_sekolah' ? 'layouts.kepsek' : 'layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Data Siswa</h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola data siswa, identitas, kontak, dan informasi akun siswa.
            </p>
        </div>

        <a href="{{ route('admin.siswa.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 5c.414 0 .75.336.75.75V11h5.25a.75.75 0 010 1.5H12.75v5.25a.75.75 0 01-1.5 0V12.5H6a.75.75 0 010-1.5h5.25V5.75c0-.414.336-.75.75-.75z"/>
            </svg>
            Tambah Siswa
        </a>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Search Auto Load --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <form id="searchSiswaForm" method="GET" action="{{ route('admin.siswa.index') }}">
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
                       value="{{ $q }}"
                       placeholder="Cari nama, NIS, no HP, email, atau alamat..."
                       autocomplete="off"
                       class="w-full rounded-xl border-gray-300 pl-10 pr-10 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">

                @if(request('q'))
                    <a href="{{ route('admin.siswa.index') }}"
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

    {{-- Table --}}
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
    @php
        $foto = $row->foto ?? null;

        if ($foto) {
            $foto = ltrim($foto, '/');

            if (str_starts_with($foto, 'storage/app/public/')) {
                $foto = substr($foto, strlen('storage/app/public/'));
            }

            if (str_starts_with($foto, 'public/')) {
                $foto = substr($foto, strlen('public/'));
            }

            if (str_starts_with($foto, 'storage/')) {
                $fotoUrl = asset($foto);
            } else {
                $fotoUrl = asset('storage/' . $foto);
            }
        } else {
            $fotoUrl = null;
        }
    @endphp

    @if($fotoUrl)
        <img src="{{ $fotoUrl }}"
             alt="Foto {{ $row->nama }}"
             class="mx-auto h-14 w-14 rounded-xl border border-gray-200 object-cover shadow-sm"
             onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.classList.remove('hidden');">

        <div class="hidden mx-auto h-14 w-14 items-center justify-center rounded-xl border border-gray-200 bg-gray-100 text-[10px] font-semibold text-gray-400">
            No Foto
        </div>
    @else
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl border border-gray-200 bg-gray-100 text-[10px] font-semibold text-gray-400">
            No Foto
        </div>
    @endif
</td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $row->nis ?? '—' }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-800">{{ $row->nama }}</div>
                                <div class="mt-1 text-xs text-gray-500 md:hidden">
                                    {{ $row->email ?? 'Email belum tersedia' }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                @if(($row->jk ?? '') === 'L')
                                    <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                        Laki-laki
                                    </span>
                                @elseif(($row->jk ?? '') === 'P')
                                    <span class="inline-flex rounded-full border border-pink-200 bg-pink-50 px-2.5 py-1 text-xs font-semibold text-pink-700">
                                        Perempuan
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                        {{ $row->jk_label ?? '—' }}
                                    </span>
                                @endif
                            </td>

                            <td class="max-w-xs px-5 py-4 text-gray-600">
                                <div class="line-clamp-2">
                                    {{ $row->alamat ?? '—' }}
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $row->no_hp ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $row->email ?? '—' }}
                            </td>

                        <td class="sticky right-0 bg-white px-5 py-4">
                            <div class="flex flex-nowrap items-center justify-center gap-2 whitespace-nowrap">
                                <a href="{{ route('admin.siswa.edit', $row) }}"
                                class="inline-flex items-center justify-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                    Ubah
                                </a>

                                <a href="{{ route('admin.siswa.show', $row) }}"
                                class="inline-flex items-center justify-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">
                                    Detail
                                </a>
                            </div>
                        </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m10-4a4 4 0 1 0-8 0 4 4 0 0 0 8 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-semibold text-gray-700">Belum ada data siswa</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Data siswa belum tersedia atau tidak sesuai pencarian.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $items->withQueryString()->links() }}
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
</script>
@endsection