@extends('layouts.kepsek')

@section('title','Dashboard Kepala Sekolah')

@section('content')
@php
    $totalPresensi = $rekapHariIni->total ?? 0;
    $safeTotalPresensi = max(1, $totalPresensi);

    $pHadir = (($rekapHariIni->hadir ?? 0) / $safeTotalPresensi) * 100;
    $pIzin  = (($rekapHariIni->izin ?? 0) / $safeTotalPresensi) * 100;
    $pSakit = (($rekapHariIni->sakit ?? 0) / $safeTotalPresensi) * 100;
    $pAlfa  = (($rekapHariIni->alfa ?? 0) / $safeTotalPresensi) * 100;

    $safeTotalMapel = max(1, $totalMapel ?? 0);
    $pFinal = round((($finalMapel ?? 0) / $safeTotalMapel) * 100);
@endphp

<div class="space-y-6">

  {{-- HEADER --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
      <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
        Dashboard Kepala Sekolah
      </h1>

      <p class="mt-1 text-sm text-gray-500">
        {{ $today->translatedFormat('l, d F Y') }}

        @if(!empty($taAktif))
          · Tahun ajaran aktif:
          <span class="font-semibold text-indigo-700">
            {{ $taAktif->nama_tahun ?? $taAktif->tahun ?? '-' }}
            @if(isset($taAktif->semester))
              (Semester {{ strtoupper($taAktif->semester) }})
            @endif
          </span>
        @endif
      </p>
    </div>

    <div class="flex flex-wrap gap-2 text-xs">
      <span class="inline-flex items-center gap-1 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-emerald-700">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
        Monitoring presensi
      </span>

      <span class="inline-flex items-center gap-1 rounded-full border border-sky-100 bg-sky-50 px-3 py-1 text-sky-700">
        <span class="h-2 w-2 rounded-full bg-sky-500"></span>
        Monitoring nilai
      </span>

      <span class="inline-flex items-center gap-1 rounded-full border border-amber-100 bg-amber-50 px-3 py-1 text-amber-700">
        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
        Persetujuan pengumuman
      </span>
    </div>
  </div>

  {{-- SUMMARY CARDS --}}
  <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

    <div class="rounded-2xl border bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Jumlah Kelas</p>
          <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalRombel }}</p>
        </div>
        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-indigo-50 text-2xl">🏫</div>
      </div>

      <a href="{{ route('kepala_sekolah.data.rombel.index') }}"
         class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700">
        Lihat data rombel →
      </a>
    </div>

    <div class="rounded-2xl border bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Jumlah Guru</p>
          <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalGuru }}</p>
        </div>
        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-50 text-2xl">👩‍🏫</div>
      </div>

      <a href="{{ route('kepala_sekolah.data.guru') }}"
         class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-800">
        Lihat data guru →
      </a>
    </div>

    <div class="rounded-2xl border bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Jumlah Siswa</p>
          <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalSiswa }}</p>
        </div>
        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-amber-50 text-2xl">👨‍🎓</div>
      </div>

      <a href="{{ route('kepala_sekolah.data.siswa') }}"
         class="mt-4 inline-flex items-center gap-1 text-xs font-semibold text-amber-700 hover:text-amber-800">
        Lihat data siswa →
      </a>
    </div>

    <div class="rounded-2xl border bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pending Pengumuman</p>
          <p class="mt-2 text-3xl font-bold {{ $pendingPengumuman > 0 ? 'text-rose-600' : 'text-gray-900' }}">
            {{ $pendingPengumuman }}
          </p>
        </div>
        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-rose-50 text-2xl">📢</div>
      </div>

      <a href="{{ route('kepala_sekolah.approvals.pengumuman.index') }}"
         class="mt-4 inline-flex items-center gap-1 text-xs font-semibold {{ $pendingPengumuman > 0 ? 'text-rose-600 hover:text-rose-700' : 'text-gray-500 hover:text-gray-600' }}">
        Lihat pengumuman →
      </a>
    </div>
  </div>

  {{-- PRESENSI + NILAI --}}
  <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

    {{-- PRESENSI --}}
    <div class="rounded-2xl border bg-white p-5 shadow-sm lg:col-span-2">
      <div class="mb-4 flex items-start justify-between gap-3">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Ringkasan Presensi Hari Ini</h2>
          <p class="mt-1 text-xs text-gray-500">
            Berdasarkan seluruh sesi presensi tanggal
            <span class="font-medium">{{ $today->translatedFormat('d M Y') }}</span>.
          </p>
        </div>

        <a href="{{ route('kepala_sekolah.monitor.presensi.index') }}"
           class="inline-flex items-center gap-1.5 rounded-full border bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100">
          Lihat monitoring →
        </a>
      </div>

      @if($totalPresensi == 0)
        <div class="rounded-xl border border-dashed bg-gray-50 px-4 py-8 text-center">
          <div class="text-sm font-semibold text-gray-700">
            Belum ada data presensi hari ini.
          </div>
          <p class="mt-1 text-xs text-gray-500">
            Ringkasan akan tampil setelah guru membuka sesi presensi dan siswa melakukan presensi.
          </p>
        </div>
      @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
          <div class="md:col-span-2">
            <p class="mb-1 text-xs text-gray-500">Persentase kehadiran hari ini</p>

            <div class="flex items-end gap-2">
              <p class="text-3xl font-bold text-emerald-600">
                {{ $persenHadir !== null ? $persenHadir.'%' : '—' }}
              </p>

              <p class="mb-1 text-xs text-gray-500">
                dari {{ $totalPresensi }} absensi tercatat
              </p>
            </div>

            <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-gray-100">
              <div class="flex h-2">
                <div style="width: {{ $pHadir }}%" class="bg-emerald-500"></div>
                <div style="width: {{ $pIzin }}%" class="bg-blue-400"></div>
                <div style="width: {{ $pSakit }}%" class="bg-cyan-400"></div>
                <div style="width: {{ $pAlfa }}%" class="bg-rose-400"></div>
              </div>
            </div>

            <p class="mt-2 text-[11px] text-gray-500">
              Komposisi presensi: hadir, izin, sakit, dan alfa.
            </p>
          </div>

          <div class="grid grid-cols-2 gap-3 md:col-span-3">
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-3">
              <p class="text-xs font-medium text-emerald-700">Hadir</p>
              <p class="mt-1 text-2xl font-semibold text-emerald-700">{{ $rekapHariIni->hadir ?? 0 }}</p>
            </div>

            <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-3">
              <p class="text-xs font-medium text-blue-700">Izin</p>
              <p class="mt-1 text-2xl font-semibold text-blue-700">{{ $rekapHariIni->izin ?? 0 }}</p>
            </div>

            <div class="rounded-xl border border-cyan-100 bg-cyan-50 px-3 py-3">
              <p class="text-xs font-medium text-cyan-700">Sakit</p>
              <p class="mt-1 text-2xl font-semibold text-cyan-700">{{ $rekapHariIni->sakit ?? 0 }}</p>
            </div>

            <div class="rounded-xl border border-rose-100 bg-rose-50 px-3 py-3">
              <p class="text-xs font-medium text-rose-700">Alfa</p>
              <p class="mt-1 text-2xl font-semibold text-rose-700">{{ $rekapHariIni->alfa ?? 0 }}</p>
            </div>
          </div>
        </div>
      @endif
    </div>

    {{-- NILAI --}}
    <div class="rounded-2xl border bg-white p-5 shadow-sm">
      <div class="mb-4 flex items-start justify-between gap-3">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Progres Finalisasi Nilai</h2>
          <p class="mt-1 text-xs text-gray-500">
            Rekap mapel yang sudah final.
          </p>
        </div>

        <a href="{{ route('kepala_sekolah.monitor.nilai.index') }}"
           class="inline-flex items-center gap-1.5 rounded-full border bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100">
          Detail →
        </a>
      </div>

      <div class="space-y-3 text-sm">
        <div class="flex items-center justify-between">
          <p class="text-xs text-gray-500">Mapel final</p>
          <p class="text-sm font-semibold text-emerald-700">
            {{ $finalMapel }} / {{ $totalMapel }} ({{ $pFinal }}%)
          </p>
        </div>

        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
          <div class="h-2 bg-emerald-500" style="width: {{ $pFinal }}%"></div>
        </div>

        <div class="mt-3 flex items-center justify-between text-xs text-gray-600">
          <div class="flex flex-col">
            <span class="font-semibold text-emerald-700">{{ $finalMapel }}</span>
            <span>Sudah FINAL</span>
          </div>

          <div class="flex flex-col text-right">
            <span class="font-semibold text-amber-700">{{ $draftMapel }}</span>
            <span>Belum final</span>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- KELAS ALFA TERTINGGI (VERSI RINGKAS) --}}
<div class="rounded-2xl border bg-white p-5 shadow-sm">
  <div class="mb-4 flex items-center justify-between">
    <div>
      <h2 class="text-lg font-semibold text-gray-900">Kelas dengan Alfa Tertinggi</h2>
      <p class="mt-1 text-xs text-gray-500">
        7 hari terakhir (termasuk hari ini)
      </p>
    </div>

    <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-1 text-xs text-rose-600">
      Fokus pembinaan
    </span>
  </div>

  @if($kelasAlfaTinggi->isEmpty())
    <div class="rounded-xl border border-dashed bg-gray-50 px-4 py-6 text-center text-sm text-gray-500">
      Belum ada data alfa.
    </div>
  @else

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="text-xs text-gray-500 border-b">
          <tr>
            <th class="px-3 py-2 text-left">Kelas</th>
            <th class="px-3 py-2 text-left">Jumlah Alfa</th>
            <th class="px-3 py-2 text-left">Status</th>
          </tr>
        </thead>

        <tbody class="divide-y">
          @foreach($kelasAlfaTinggi as $row)
            @php
              if ($row->total_alfa >= 20) {
                  $statusLabel = 'Perlu perhatian';
                  $statusClass = 'bg-rose-100 text-rose-700';
              } elseif ($row->total_alfa >= 10) {
                  $statusLabel = 'Perlu dipantau';
                  $statusClass = 'bg-amber-100 text-amber-700';
              } else {
                  $statusLabel = 'Rendah';
                  $statusClass = 'bg-emerald-100 text-emerald-700';
              }
            @endphp

            <tr class="hover:bg-gray-50">
              <td class="px-3 py-2 font-semibold text-gray-900">
                {{ $row->nama_rombel }}
              </td>

              <td class="px-3 py-2">
                <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700">
                  {{ $row->total_alfa }} alfa
                </span>
              </td>

              <td class="px-3 py-2">
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">
                  {{ $statusLabel }}
                </span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  @endif
</div>

</div>
@endsection