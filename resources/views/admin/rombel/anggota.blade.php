@extends('layouts.admin')

@section('content')
@php
    $tingkat = (string) ($rombel->tingkat ?? '');

    $tahunAjaranText =
        $rombel->tahunAjaran->nama_tahun
        ?? $rombel->tahunAjaran->nama
        ?? $rombel->tahun_ajaran
        ?? $tahunAktif->nama_tahun
        ?? $tahunAktif->nama
        ?? '—';

    $daftarSiswaView = $kandidat ?? collect();
    $anggotaView = $anggota ?? collect();

    $waliKelasNama =
        $rombel->waliKelas->nama
        ?? $rombel->guru->nama
        ?? '-';

    $mapelList = collect();

    if (isset($rombel->mataPelajaran) && $rombel->mataPelajaran) {
        $mapelList = $rombel->mataPelajaran->pluck('nama_mapel');
    } elseif (isset($rombel->mapel) && $rombel->mapel) {
        $mapelList = $rombel->mapel->pluck('nama_mapel');
    } elseif (!empty($rombel->nama_mapel_list)) {
        $mapelList = collect(explode(', ', $rombel->nama_mapel_list));
    }

    $isKelasXII = in_array($tingkat, ['XII', '12']);
    $kapasitas = (int) ($rombel->kapasitas ?? 0);
    $jumlahAnggota = $anggotaView->count();
    $sisaKapasitas = $kapasitas > 0 ? max(0, $kapasitas - $jumlahAnggota) : null;

    $isRombelTahunAktif = $isRombelTahunAktif
        ?? (($tahunAktif ?? null) && (int) $rombel->tahun_ajaran_id === (int) $tahunAktif->id);

    $backParams = array_filter([
        'tahun_ajaran_id' => request('tahun_ajaran_id', $rombel->tahun_ajaran_id),
        'tingkat' => request('tingkat'),
        'q' => request('q'),
        'page' => request('page'),
    ], fn ($value) => $value !== null && $value !== '');

    $anggotaFilterParams = array_filter([
        'tahun_ajaran_id' => request('tahun_ajaran_id', $rombel->tahun_ajaran_id),
        'tingkat' => request('tingkat'),
        'page' => request('page'),
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Anggota Rombel — {{ $rombel->nama_rombel }}
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Tingkat: <span class="font-semibold text-gray-700">{{ $rombel->tingkat ?? '—' }}</span>
                <span class="mx-1">|</span>
                Wali: <span class="font-semibold text-gray-700">{{ $waliKelasNama }}</span>
                <span class="mx-1">|</span>
                TA: <span class="font-semibold text-gray-700">{{ $tahunAjaranText }}</span>
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    Kapasitas: {{ $kapasitas > 0 ? $kapasitas.' siswa' : '-' }}
                </span>

                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                    {{ $kapasitas > 0 && $sisaKapasitas <= 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">
                    {{ $isRombelTahunAktif ? 'Sisa' : 'Sisa' }}:
                    {{ $kapasitas > 0 ? $sisaKapasitas.' siswa' : '-' }}
                </span>

                @if(!$isRombelTahunAktif)
                    <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                        Riwayat Tahun Ajaran
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-row items-center gap-3">
            @if($isRombelTahunAktif)
                @if($isKelasXII)
                    <a href="{{ route('admin.rombel.kelulusan.form', $rombel) }}"
                       class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                        Kelulusan Kelas XII
                    </a>
                @else
                    <a href="{{ route('admin.rombel.kenaikan.form', $rombel) }}"
                       class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Kenaikan Kelas
                    </a>
                @endif
            @endif

            <a href="{{ route('admin.rombel.index', $backParams) }}"
               class="inline-flex items-center rounded-xl border bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Kembali
            </a>
        </div>
    </div>

    {{-- ALERT --}}
    @if (session('ok'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
            {{ session('ok') }}
        </div>
    @endif

    @if (session('err'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            {{ session('err') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!$isRombelTahunAktif)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            Rombel ini berasal dari tahun ajaran lama. Data siswa di bawah ditampilkan sebagai
            <span class="font-semibold">riwayat anggota rombel</span>, sehingga tidak bisa ditambah atau dikeluarkan dari halaman ini.
        </div>
    @endif

    {{-- CARD ANGGOTA --}}
    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $isRombelTahunAktif ? 'Anggota Rombel Aktif' : 'Riwayat Anggota Rombel' }}
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    @if($isRombelTahunAktif)
                        Daftar siswa yang saat ini terdaftar aktif pada rombel ini.
                    @else
                        Daftar siswa yang pernah terdaftar pada rombel ini di tahun ajaran tersebut.
                    @endif
                </p>
            </div>

            <span class="inline-flex w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                {{ $isRombelTahunAktif ? 'Total aktif' : 'Total histori' }}: {{ $jumlahAnggota }} siswa
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">No</th>
                        <th class="px-5 py-3 text-left">Nama Siswa</th>
                        <th class="px-5 py-3 text-left">NIS</th>
                        <th class="px-5 py-3 text-left">NISN</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($anggotaView as $i => $row)
                        @php
                            $s = $row->siswa ?? $row;
                            $statusKeanggotaan = (int) ($row->status_keanggotaan ?? 0);
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 text-gray-500">
                                {{ $i + 1 }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-900">
                                    {{ $s->nama ?? '-' }}
                                </div>
                            </td>

                            <td class="px-5 py-4 text-gray-600">
                                {{ $s->nis ?? '-' }}
                            </td>

                            <td class="px-5 py-4 text-gray-600">
                                {{ $s->nisn ?? '-' }}
                            </td>

@if($isRombelTahunAktif)
    <td class="px-5 py-4 text-right">
        <form method="POST"
              action="{{ route('admin.rombel.anggota.destroy', [$rombel, $s->id]) }}"
              class="form-keluarkan-rombel"
              data-nama="{{ $s->nama ?? 'siswa ini' }}">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">
                Keluarkan dari Rombel
            </button>
        </form>
    </td>
@endif

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">
                                @if($isRombelTahunAktif)
                                    Belum ada anggota aktif pada rombel ini.
                                @else
                                    Belum ada riwayat anggota pada rombel ini.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- CARD TAMBAH ANGGOTA --}}
    @if($isRombelTahunAktif)
        <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
            <div class="border-b px-5 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Tambahkan Anggota Baru</h2>

            </div>

            <div class="p-5">
                <form method="GET"
                      action="{{ route('admin.rombel.anggota', array_merge(['rombel' => $rombel->id], $anggotaFilterParams)) }}"
                      class="mb-4 flex flex-col gap-3 md:flex-row">
                    <input type="hidden" name="tahun_ajaran_id" value="{{ request('tahun_ajaran_id', $rombel->tahun_ajaran_id) }}">

                    @if(request('tingkat'))
                        <input type="hidden" name="tingkat" value="{{ request('tingkat') }}">
                    @endif

                    @if(request('page'))
                        <input type="hidden" name="page" value="{{ request('page') }}">
                    @endif

                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Cari NIS, NISN, atau nama siswa..."
                           class="h-11 flex-1 rounded-xl border border-gray-300 px-4 text-sm focus:border-indigo-500 focus:ring-indigo-500">

                    <button type="submit"
                            class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                        Cari
                    </button>

                    @if(request('q'))
                        <a href="{{ route('admin.rombel.anggota', array_merge(['rombel' => $rombel->id], $anggotaFilterParams)) }}"
                           class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Reset
                        </a>
                    @endif
                </form>

                <div class="mb-3 flex flex-col gap-1 text-xs text-gray-500 md:flex-row md:items-center md:justify-between">
                    <span>{{ $daftarSiswaView->count() }} siswa tersedia untuk ditambahkan</span>
                    <span>Total anggota aktif: {{ $jumlahAnggota }}{{ $kapasitas > 0 ? ' / '.$kapasitas : '' }}</span>
                </div>

                <form method="POST" action="{{ route('admin.rombel.anggota.store', $rombel) }}">
                    @csrf

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <div class="max-h-80 overflow-y-auto">
                            @forelse($daftarSiswaView as $siswa)
                                <label class="flex cursor-pointer items-center justify-between gap-4 border-b border-gray-100 px-5 py-3 last:border-b-0 hover:bg-gray-50">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox"
                                               name="siswa_ids[]"
                                               value="{{ $siswa->id }}"
                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">

                                        <div>
                                            <div class="text-sm font-semibold text-gray-800">
                                                {{ $siswa->nama }}
                                            </div>

                                            <div class="text-xs text-gray-500 md:hidden">
                                                {{ $siswa->nis ?? '-' }} / {{ $siswa->nisn ?? '-' }}
                                            </div>
                                        </div>
                                    </div>

                                    <span class="hidden text-xs text-gray-400 md:inline">
                                        {{ $siswa->nis ?? '-' }} / {{ $siswa->nisn ?? '-' }}
                                    </span>
                                </label>
                            @empty
                                <div class="px-5 py-10 text-center text-sm text-gray-500">
                                    Tidak ada siswa yang bisa ditambahkan.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                                class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                            Tambah ke Rombel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

{{-- MODAL KONFIRMASI KELUARKAN DARI ROMBEL --}}
@if($isRombelTahunAktif)
    <div id="modal-keluarkan-rombel"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="border-b px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Konfirmasi Keluarkan Siswa
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Pastikan tindakan ini sudah benar sebelum dilanjutkan.
                </p>
            </div>

            <div class="px-6 py-5">
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <span id="modal-keluarkan-rombel-text">
                        Siswa akan dikeluarkan dari rombel ini.
                    </span>
                </div>

                <p class="mt-3 text-xs leading-relaxed text-gray-500">
                    Siswa hanya akan dikeluarkan dari rombel ini. Status siswa tidak akan diubah menjadi pindah, keluar, atau lulus.
                </p>
            </div>

            <div class="flex justify-end gap-3 border-t px-6 py-4">
                <button type="button"
                        id="btn-batal-keluarkan-rombel"
                        class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Batal
                </button>

                <button type="button"
                        id="btn-lanjut-keluarkan-rombel"
                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    Ya, Keluarkan
                </button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modal-keluarkan-rombel');
        const modalText = document.getElementById('modal-keluarkan-rombel-text');
        const btnBatal = document.getElementById('btn-batal-keluarkan-rombel');
        const btnLanjut = document.getElementById('btn-lanjut-keluarkan-rombel');

        let formAktif = null;

        function bukaModal(form) {
            formAktif = form;

            const nama = form.dataset.nama || 'siswa ini';

            if (modalText) {
                modalText.textContent = nama + ' akan dikeluarkan dari rombel ini.';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function tutupModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            formAktif = null;
        }

        document.querySelectorAll('.form-keluarkan-rombel').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                bukaModal(form);
            });
        });

        if (btnBatal) {
            btnBatal.addEventListener('click', function () {
                tutupModal();
            });
        }

        if (btnLanjut) {
            btnLanjut.addEventListener('click', function () {
                if (formAktif) {
                    formAktif.submit();
                }
            });
        }

        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    tutupModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                tutupModal();
            }
        });
    });
    </script>
@endif
@endsection