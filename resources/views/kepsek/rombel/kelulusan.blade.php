{{-- resources/views/kepsek/rombel/kelulusan.blade.php --}}
@extends('layouts.kepsek')

@section('content')
@php
    $evaluasiMap = collect($evaluasiRows ?? [])->keyBy('siswa_id');
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">
                Kelulusan — {{ $rombel->nama_rombel }}
            </h1>
            <p class="text-xs text-gray-500">
                Tingkat: {{ $rombel->tingkat ?? '–' }}
                • Wali: {{ $rombel->waliKelas->nama ?? '–' }}
                • TA: {{ $rombel->tahunAjaran->nama ?? '-' }}
            </p>
        </div>

        <a href="{{ route('kepala_sekolah.data.rombel.anggota', $rombel) }}"
           class="text-sm px-4 py-2 rounded bg-gray-100 text-gray-700 hover:bg-gray-200">
            Kembali
        </a>
    </div>

    {{-- ALERT SUCCESS --}}
    @if (session('ok'))
        <div class="px-4 py-3 rounded bg-emerald-50 text-emerald-700 text-sm border border-emerald-200">
            {{ session('ok') }}
        </div>
    @endif

    {{-- ALERT ERROR --}}
    @if ($errors->any())
        <div class="px-4 py-3 rounded bg-red-50 text-red-700 text-sm border border-red-200">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- INFO --}}
    <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 text-sm text-emerald-800">
        <div class="font-medium mb-1">Aturan evaluasi kelulusan</div>
        <ul class="list-disc pl-5 space-y-1 text-xs">
            <li>Nilai harus lengkap berdasarkan hasil pembelajaran siswa.</li>
            <li>Kehadiran minimal <strong>90%</strong>.</li>
            <li>Sikap minimal <strong>Baik</strong>.</li>
            <li>Siswa yang dicentang dan memenuhi syarat akan diproses menjadi <strong>lulus</strong>.</li>
        </ul>
    </div>

    {{-- FORM --}}
    <form method="POST"
          action="{{ route('kepala_sekolah.data.rombel.kelulusan.proses', $rombel) }}"
          class="space-y-6">
        @csrf

        {{-- TABEL EVALUASI --}}
        <div class="bg-white rounded-lg shadow border overflow-hidden">
            <div class="px-4 py-3 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">Evaluasi Siswa untuk Kelulusan</h2>
                    <p class="text-xs text-gray-500">
                        Sistem menilai kelengkapan nilai dan kehadiran. Sikap diisi manual untuk penetapan akhir.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button"
                            id="btn-check-layak"
                            class="px-3 py-2 rounded bg-emerald-600 text-white text-xs font-medium hover:bg-emerald-700">
                        Pilih Semua yang Layak
                    </button>

                    <button type="button"
                            id="btn-uncheck-all"
                            class="px-3 py-2 rounded bg-gray-100 text-gray-700 text-xs font-medium hover:bg-gray-200">
                        Hapus Semua Pilihan
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="px-4 py-3 w-12">
                                <input type="checkbox" id="check-all" class="rounded border-gray-300">
                            </th>
                            <th class="px-4 py-3">NIS</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Nilai</th>
                            <th class="px-4 py-3">Kehadiran</th>
                            <th class="px-4 py-3">Sikap</th>
                            <th class="px-4 py-3">Rekomendasi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($siswa as $s)
                            @php
                                $ev = $evaluasiMap->get($s->id);
                                $oldSikap = old("sikap.{$s->id}", '');
                            @endphp

                            <tr class="hover:bg-gray-50"
                                data-row
                                data-siswa-id="{{ $s->id }}"
                                data-nilai-ok="{{ $ev && $ev->nilai_lengkap ? 1 : 0 }}"
                                data-hadir-ok="{{ $ev && $ev->kehadiran_memenuhi ? 1 : 0 }}">
                                <td class="px-4 py-3 align-top">
                                    <input type="checkbox"
                                           name="siswa_ids[]"
                                           value="{{ $s->id }}"
                                           class="row-check rounded border-gray-300 mt-1"
                                           {{ in_array($s->id, old('siswa_ids', [])) ? 'checked' : '' }}>
                                </td>

                                <td class="px-4 py-3 align-top">{{ $s->nis }}</td>

                                <td class="px-4 py-3 align-top">
                                    <div class="font-medium text-gray-900">{{ $s->nama }}</div>
                                </td>

                                <td class="px-4 py-3 align-top">
                                    @if($ev && $ev->nilai_lengkap)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                                            Lengkap
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $ev->jumlah_nilai ?? 0 }}/{{ $ev->jumlah_mapel ?? 0 }} mapel
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-xs font-semibold">
                                            Belum Lengkap
                                        </span>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $ev->jumlah_nilai ?? 0 }}/{{ $ev->jumlah_mapel ?? 0 }} mapel
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-3 align-top">
                                    @if($ev && $ev->kehadiran_memenuhi)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                                            {{ rtrim(rtrim(number_format($ev->persen_hadir ?? 0, 2, '.', ''), '0'), '.') }}%
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-xs font-semibold">
                                            {{ $ev ? rtrim(rtrim(number_format($ev->persen_hadir ?? 0, 2, '.', ''), '0'), '.') : '0' }}%
                                        </span>
                                    @endif

                                    <div class="text-xs text-gray-500 mt-1">
                                        Hadir {{ $ev->hadir ?? 0 }} / {{ $ev->total_presensi ?? 0 }} pertemuan
                                    </div>
                                </td>

                                <td class="px-4 py-3 align-top">
                                    <select name="sikap[{{ $s->id }}]"
                                            class="sikap-select w-full min-w-[130px] border-gray-300 rounded-md text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">-- Pilih --</option>
                                        <option value="baik" {{ $oldSikap === 'baik' ? 'selected' : '' }}>Baik</option>
                                        <option value="cukup" {{ $oldSikap === 'cukup' ? 'selected' : '' }}>Cukup</option>
                                        <option value="kurang" {{ $oldSikap === 'kurang' ? 'selected' : '' }}>Kurang</option>
                                    </select>
                                    <div class="text-xs text-gray-500 mt-1">
                                    </div>
                                </td>

                                <td class="px-4 py-3 align-top">
                                    <span class="rekomendasi-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                        Belum lengkap
                                    </span>
                                    <div class="rekomendasi-text text-xs text-gray-500 mt-1">
                                        Pilih sikap untuk melihat rekomendasi akhir.
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                    Belum ada siswa pada rombel ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TOMBOL SUBMIT --}}
        <div class="flex justify-end">
            <button type="submit"
                    class="px-5 py-2.5 rounded bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">
                Proses Kelulusan
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('[data-row]');
    const checkAll = document.getElementById('check-all');
    const btnCheckLayak = document.getElementById('btn-check-layak');
    const btnUncheckAll = document.getElementById('btn-uncheck-all');

    function updateRowRecommendation(row) {
        const nilaiOk = row.dataset.nilaiOk === '1';
        const hadirOk = row.dataset.hadirOk === '1';
        const sikap = row.querySelector('.sikap-select')?.value || '';
        const badge = row.querySelector('.rekomendasi-badge');
        const text = row.querySelector('.rekomendasi-text');

        let label = 'Belum lengkap';
        let badgeClass = 'bg-gray-100 text-gray-700';
        let desc = 'Pilih sikap untuk melihat rekomendasi akhir.';
        let layak = '0';

        if (!nilaiOk && !hadirOk) {
            label = 'Tidak Layak';
            badgeClass = 'bg-red-50 text-red-700';
            desc = 'Nilai belum lengkap dan kehadiran belum memenuhi 90%.';
        } else if (!nilaiOk) {
            label = 'Tidak Layak';
            badgeClass = 'bg-red-50 text-red-700';
            desc = 'Nilai belum lengkap.';
        } else if (!hadirOk) {
            label = 'Tidak Layak';
            badgeClass = 'bg-red-50 text-red-700';
            desc = 'Kehadiran belum memenuhi 90%.';
        } else if (sikap === '') {
            label = 'Menunggu Sikap';
            badgeClass = 'bg-amber-50 text-amber-700';
            desc = 'Nilai dan kehadiran memenuhi. Pilih sikap untuk keputusan akhir.';
        } else if (sikap === 'baik') {
            label = 'Layak Lulus';
            badgeClass = 'bg-emerald-50 text-emerald-700';
            desc = 'Memenuhi nilai, kehadiran, dan sikap minimal Baik.';
            layak = '1';
        } else {
            label = 'Perlu Tinjauan';
            badgeClass = 'bg-amber-50 text-amber-700';
            desc = 'Nilai dan kehadiran memenuhi, tetapi sikap belum minimal Baik.';
        }

        badge.className = 'rekomendasi-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ' + badgeClass;
        badge.textContent = label;
        text.textContent = desc;
        row.dataset.layakFinal = layak;
    }

    rows.forEach((row) => {
        const sikapSelect = row.querySelector('.sikap-select');
        if (sikapSelect) {
            sikapSelect.addEventListener('change', function () {
                updateRowRecommendation(row);
            });
        }
        updateRowRecommendation(row);
    });

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            const checked = this.checked;
            document.querySelectorAll('.row-check').forEach((cb) => {
                cb.checked = checked;
            });
        });
    }

    if (btnUncheckAll) {
        btnUncheckAll.addEventListener('click', function () {
            document.querySelectorAll('.row-check').forEach((cb) => {
                cb.checked = false;
            });
            if (checkAll) checkAll.checked = false;
        });
    }

    if (btnCheckLayak) {
        btnCheckLayak.addEventListener('click', function () {
            document.querySelectorAll('[data-row]').forEach((row) => {
                const cb = row.querySelector('.row-check');
                cb.checked = row.dataset.layakFinal === '1';
            });

            if (checkAll) {
                const allChecks = Array.from(document.querySelectorAll('.row-check'));
                checkAll.checked = allChecks.length > 0 && allChecks.every((cb) => cb.checked);
            }
        });
    }
});
</script>
@endsection