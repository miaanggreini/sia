@extends('layouts.admin')

@section('title','Dashboard Admin')

@section('content')
@php
  $genderL = $genderCounts['L'] ?? 0;
  $genderP = $genderCounts['P'] ?? 0;
  $totalGender = $genderL + $genderP;

  $persenL = $totalGender > 0 ? round(($genderL / $totalGender) * 100) : 0;
  $persenP = $totalGender > 0 ? round(($genderP / $totalGender) * 100) : 0;

  $tahunAjaranAktif = $tahunAjaranAktif ?? '—';
@endphp

{{-- ================= HEADER DASHBOARD ================= --}}
<div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
  <div>
    <h1 class="text-3xl font-bold tracking-tight text-slate-900">
      halooo Admin
    </h1>
    <p class="mt-1 text-sm text-slate-500">
      Selamat datang, {{ auth()->user()->name ?? 'Admin' }}. Berikut ringkasan data akademik sekolah.
    </p>
  </div>

  {{-- Kartu Tahun Ajaran Aktif --}}
  <div class="w-full rounded-2xl border border-indigo-100 bg-indigo-50 px-5 py-4 shadow-sm md:w-auto">
    <div class="flex items-center gap-3">
      <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-6 w-6"
             viewBox="0 0 24 24"
             fill="currentColor">
          <path d="M6 2a2 2 0 00-2 2v16l8-4 8 4V4a2 2 0 00-2-2H6z" />
        </svg>
      </div>

      <div>
        <p class="text-sm font-semibold text-indigo-700">
          Tahun Ajaran Aktif
        </p>
        <p class="text-xl font-bold tracking-tight text-indigo-900">
          {{ $tahunAjaranAktif }}
        </p>
      </div>
    </div>
  </div>
</div>

{{-- ================= QUICK STATS ================= --}}
<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
  {{-- Jumlah Guru --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-4">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-6 w-6"
             viewBox="0 0 24 24"
             fill="currentColor">
          <path d="M8 7a3 3 0 116 0 3 3 0 01-6 0zM4 19a6 6 0 1116 0v1H4v-1z"/>
        </svg>
      </div>

      <div>
        <p class="text-sm font-medium text-slate-500">
          Jumlah Guru
        </p>
        <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
          {{ number_format($totalGuru) }}
        </p>
      </div>
    </div>
  </div>

  {{-- Jumlah Siswa --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-4">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-6 w-6"
             viewBox="0 0 24 24"
             fill="currentColor">
          <path d="M12 12a3 3 0 100-6 3 3 0 000 6zM2 20a8 8 0 0116 0v1H2v-1zM18 8a2 2 0 114 0 2 2 0 01-4 0zM20 12c1.657 0 3 1.79 3 4v1h-2v-1c0-1.657-.895-3-1-3z"/>
        </svg>
      </div>

      <div>
        <p class="text-sm font-medium text-slate-500">
          Jumlah Siswa
        </p>
        <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
          {{ number_format($totalSiswa) }}
        </p>
      </div>
    </div>
  </div>

  {{-- Jumlah Kelas --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-4">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-6 w-6"
             viewBox="0 0 24 24"
             fill="currentColor">
          <path d="M4 6h16v10H4z"/>
          <path d="M10 20h4"/>
        </svg>
      </div>

      <div>
        <p class="text-sm font-medium text-slate-500">
          Jumlah Kelas
        </p>
        <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900">
          {{ number_format($totalKelas) }}
        </p>
      </div>
    </div>
  </div>
</div>

{{-- ================= RINGKASAN PENGUMUMAN ================= --}}
<div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
  <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
    <div class="grid flex-1 grid-cols-1 gap-4 md:grid-cols-2">
      {{-- Pending Approval --}}
      <div class="flex items-center gap-4">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-yellow-50 text-yellow-700">
          <svg xmlns="http://www.w3.org/2000/svg"
               class="h-5 w-5"
               fill="none"
               viewBox="0 0 24 24"
               stroke="currentColor"
               stroke-width="2">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>

        <div>
          <p class="text-sm font-medium text-slate-500">
            Menunggu Approval
          </p>
          <p class="mt-1 text-2xl font-bold text-slate-900">
            {{ number_format($pengumumanPendingCount) }}
          </p>
        </div>
      </div>

      {{-- Siap Publish --}}
      <div class="flex items-center gap-4">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700">
          <svg xmlns="http://www.w3.org/2000/svg"
               class="h-5 w-5"
               fill="none"
               viewBox="0 0 24 24"
               stroke="currentColor"
               stroke-width="2">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>

        <div>
          <p class="text-sm font-medium text-slate-500">
            Siap Dipublikasikan
          </p>
          <p class="mt-1 text-2xl font-bold text-slate-900">
            {{ number_format($pengumumanSiapPublishCnt) }}
          </p>
        </div>
      </div>
    </div>

    <a href="{{ route('admin.pengumuman.index') }}"
       class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
      Kelola Pengumuman
    </a>
  </div>
</div>

{{-- ================= KONTEN UTAMA ================= --}}
<div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">

  {{-- Komposisi Siswa --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-start justify-between gap-3">
      <div>
        <h3 class="text-base font-bold text-slate-900">
          Komposisi Siswa
        </h3>
        <p class="mt-1 text-xs text-slate-500">
          Berdasarkan jenis kelamin
        </p>
      </div>

      <div class="rounded-full bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
        L: {{ $genderL }} · P: {{ $genderP }}
      </div>
    </div>

    <div class="h-44">
      <canvas id="genderBar"></canvas>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-3">
      <div class="rounded-xl bg-blue-50 px-3 py-2">
        <p class="text-xs font-medium text-blue-700">
          Laki-laki
        </p>
        <p class="mt-1 text-sm font-bold text-blue-900">
          {{ $genderL }} siswa
          <span class="font-medium text-blue-700">
            ({{ $persenL }}%)
          </span>
        </p>
      </div>

      <div class="rounded-xl bg-orange-50 px-3 py-2">
        <p class="text-xs font-medium text-orange-700">
          Perempuan
        </p>
        <p class="mt-1 text-sm font-bold text-orange-900">
          {{ $genderP }} siswa
          <span class="font-medium text-orange-700">
            ({{ $persenP }}%)
          </span>
        </p>
      </div>
    </div>
  </div>

  {{-- Jadwal Hari Ini --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h3 class="text-base font-bold text-slate-900">
          Jadwal Hari Ini
        </h3>
        <p class="mt-1 text-xs text-slate-500">
          Ringkasan jadwal pembelajaran yang berjalan hari ini.
        </p>
      </div>

      <span class="text-sm font-medium text-slate-500">
        {{ \Illuminate\Support\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d M Y') }}
      </span>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50">
            <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
              <th class="px-4 py-3">
                Jam
              </th>
              <th class="px-4 py-3">
                Kelas
              </th>
              <th class="px-4 py-3">
                Mata Pelajaran
              </th>
              <th class="px-4 py-3">
                Guru
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100 bg-white">
            @forelse(($jadwalHariIni ?? collect())->take(8) as $j)
              <tr class="text-slate-700">
                <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-900">
                  {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }}
                  -
                  {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }}
                </td>
                <td class="px-4 py-3">
                  {{ $j->rombel->nama_rombel ?? '-' }}
                </td>
                <td class="px-4 py-3">
                  {{ $j->mataPelajaran->nama_mapel ?? '-' }}
                </td>
                <td class="px-4 py-3">
                  {{ $j->guru->nama ?? '-' }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-4 py-10 text-center">
                  <div class="mx-auto flex max-w-sm flex-col items-center">
                    <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-slate-50 text-slate-400">
                      <svg xmlns="http://www.w3.org/2000/svg"
                           class="h-5 w-5"
                           fill="none"
                           viewBox="0 0 24 24"
                           stroke="currentColor"
                           stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M8 7V3m8 4V3M5 11h14M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                      </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-700">
                      Tidak ada jadwal hari ini
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                      Jadwal akan tampil jika terdapat pembelajaran pada hari ini.
                    </p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection

@if(isset($pengumumanBaru) && $pengumumanBaru)
<div id="popupPengumuman"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">

  <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
    <button onclick="closePopup()"
            type="button"
            class="absolute right-4 top-4 text-xl text-slate-400 hover:text-slate-600">
      ×
    </button>

    <div class="mb-4 flex items-center gap-3">
      <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-5 w-5"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor"
             stroke-width="2">
          <path stroke-linecap="round"
                stroke-linejoin="round"
                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592L5.5 14H4a2 2 0 01-2-2V9a2 2 0 012-2h1.5l2.083-5.832A1.76 1.76 0 0111 1.76v4.122zM19 8a3 3 0 010 6M21 5a7 7 0 010 12"/>
        </svg>
      </div>

      <div>
        <h2 class="text-lg font-bold text-slate-900">
          Pengumuman Baru
        </h2>
        <p class="text-sm text-slate-500">
          Informasi terbaru dari sistem.
        </p>
      </div>
    </div>

    <h3 class="font-semibold text-slate-900">
      {{ $pengumumanBaru->judul }}
    </h3>

    <p class="mt-2 text-sm leading-relaxed text-slate-600">
      {{ Str::limit(strip_tags($pengumumanBaru->isi), 150) }}
    </p>

    <a href="{{ route('admin.pengumuman.index') }}"
       class="mt-5 inline-flex rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
      Baca Selengkapnya
    </a>
  </div>
</div>

<script>
  function closePopup() {
    const popup = document.getElementById('popupPengumuman');
    if (popup) {
      popup.style.display = 'none';
    }
  }
</script>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  (function () {
    const el = document.getElementById('genderBar');
    if (!el) return;

    const L = {{ (int) $genderL }};
    const P = {{ (int) $genderP }};

    new Chart(el, {
      type: 'bar',
      data: {
        labels: ['Laki-laki', 'Perempuan'],
        datasets: [{
          data: [L, P],
          backgroundColor: ['#3b82f6', '#f97316'],
          borderRadius: 8,
          maxBarThickness: 44,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: (ctx) => {
                const total = (L + P) || 1;
                const val = ctx.parsed.y || 0;
                const pct = Math.round((val / total) * 100);
                return ` ${val} siswa (${pct}%)`;
              }
            }
          }
        },
        layout: {
          padding: {
            top: 4,
            right: 8,
            bottom: 0,
            left: 4
          }
        },
        scales: {
          x: {
            grid: {
              display: false
            },
            ticks: {
              color: '#64748b',
              font: {
                size: 11,
                weight: '500'
              }
            }
          },
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(15, 23, 42, 0.06)'
            },
            ticks: {
              color: '#64748b',
              precision: 0,
              stepSize: Math.max(1, Math.ceil((L + P) / 5))
            }
          }
        }
      }
    });
  })();
</script>
@endpush