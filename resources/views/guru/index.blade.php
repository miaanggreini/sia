@extends('layouts.guru')

@section('content')
  @php
    use Carbon\Carbon;

    Carbon::setLocale('id');

    $nama    = $guru?->nama ?? auth()->user()->name;
    $initial = strtoupper(mb_substr($nama ?? 'G', 0, 1));
    $fotoUrl = $guru?->foto ? asset('storage/'.ltrim($guru->foto, '/')) : null;

    $items = $jadwalHariIni ?? collect();

    $jumlahPertemuan = $items->count();

    $jumlahKelas = $items
        ->map(fn($j) => optional($j->rombel)->nama_rombel)
        ->filter()
        ->unique()
        ->count();

    $totalMenit = 0;

    foreach ($items as $j) {
        if ($j->jam_mulai && $j->jam_selesai) {
            try {
                $mulai = strlen($j->jam_mulai) === 5
                    ? Carbon::createFromFormat('H:i', $j->jam_mulai)
                    : Carbon::createFromFormat('H:i:s', $j->jam_mulai);

                $selesai = strlen($j->jam_selesai) === 5
                    ? Carbon::createFromFormat('H:i', $j->jam_selesai)
                    : Carbon::createFromFormat('H:i:s', $j->jam_selesai);

                $totalMenit += $selesai->diffInMinutes($mulai);
            } catch (\Exception $e) {
                // Abaikan format jam yang tidak sesuai
            }
        }
    }

    if ($totalMenit > 0) {
        $jam = intdiv($totalMenit, 60);
        $menit = $totalMenit % 60;
        $totalJamLabel = $jam
            ? $jam.' jam'.($menit ? ' '.$menit.' menit' : '')
            : $totalMenit.' menit';
    } else {
        $totalJamLabel = '-';
    }

    $labelTA = $taLabel ?? optional($taAktif)->nama_tahun ?? optional($taAktif)->label ?? '-';
    $labelSemester = $semester ?? optional($taAktif)->semester ?? null;
  @endphp

  <div class="space-y-6">

    <section class="flex items-center justify-between mb-2">
      <div class="flex items-center gap-3">

        @if($fotoUrl)
          <img src="{{ $fotoUrl }}"
               class="h-10 w-10 rounded-full object-cover">
        @else
          <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-bold">
            {{ $initial }}
          </div>
        @endif

        <div>
          <h1 class="text-lg font-semibold text-gray-900">
            {{ $nama }}
          </h1>
          <p class="text-xs text-gray-500">
            Ringkasan aktivitas hari ini
          </p>
        </div>
      </div>

      <div class="hidden md:flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
          TA {{ $labelTA }}
        </span>

        @if($labelSemester)
          <span class="inline-flex items-center rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
            Semester {{ ucfirst($labelSemester) }}
          </span>
        @endif
      </div>
    </section>

    {{-- RINGKASAN --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pertemuan Hari Ini</p>
        <div class="mt-2 flex items-end justify-between">
          <p class="text-3xl font-bold text-indigo-600">{{ $jumlahPertemuan }}</p>
          <span class="text-xs text-gray-400">sesi</span>
        </div>
      </div>

      <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kelas Diajar</p>
        <div class="mt-2 flex items-end justify-between">
          <p class="text-3xl font-bold text-sky-600">{{ $jumlahKelas }}</p>
          <span class="text-xs text-gray-400">kelas</span>
        </div>
      </div>

      <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Durasi Mengajar</p>
        <div class="mt-2 flex items-end justify-between">
          <p class="text-2xl font-bold text-emerald-600">{{ $totalJamLabel }}</p>
          <span class="text-xs text-gray-400">hari ini</span>
        </div>
      </div>
    </section>

    {{-- AKSI CEPAT --}}
    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 p-4">
      <div class="mb-3 flex items-center justify-between gap-3">
        <div>
          <h2 class="text-base font-semibold text-gray-900">Aksi Cepat</h2>
          <p class="text-xs text-gray-500">Menu yang paling sering digunakan guru.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        <a href="{{ route('guru.presensi.index') }}"
           class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 hover:bg-indigo-100 transition">
          <p class="text-sm font-semibold text-indigo-700">Presensi</p>
          <p class="mt-1 text-xs text-gray-600">Buka sesi presensi kelas.</p>
        </a>

        <a href="{{ route('guru.penilaian.index') }}"
           class="rounded-xl border border-emerald-100 bg-emerald-50/60 px-4 py-3 hover:bg-emerald-100 transition">
          <p class="text-sm font-semibold text-emerald-700">Penilaian</p>
          <p class="mt-1 text-xs text-gray-600">Input dan kelola nilai siswa.</p>
        </a>

        <a href="{{ route('guru.kehadiran.index') }}"
           class="rounded-xl border border-sky-100 bg-sky-50/70 px-4 py-3 hover:bg-sky-100 transition">
          <p class="text-sm font-semibold text-sky-700">Monitoring Kehadiran</p>
          <p class="mt-1 text-xs text-gray-600">Pantau rekap presensi siswa.</p>
        </a>

        <a href="{{ route('guru.wali.kelas-saya') }}"
           class="rounded-xl border border-amber-100 bg-amber-50/70 px-4 py-3 hover:bg-amber-100 transition">
          <p class="text-sm font-semibold text-amber-700">Wali Kelas</p>
          <p class="mt-1 text-xs text-gray-600">Kelola data kelas wali.</p>
        </a>
      </div>
    </section>

    {{-- JADWAL HARI INI --}}
    <section class="rounded-2xl overflow-hidden bg-white shadow-sm ring-1 ring-gray-100">
      <header class="px-5 py-4 border-b bg-gradient-to-r from-indigo-50 to-sky-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Jadwal Hari Ini</h2>
          <p class="text-xs text-indigo-700/80">
            Hari {{ $hariIni ?? now('Asia/Jakarta')->translatedFormat('l') }}
            <span class="mx-1">•</span>
            TA {{ $labelTA }}
          </p>
        </div>

        <a href="{{ route('guru.jadwal.index') }}"
           class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
          Lihat semua
        </a>
      </header>

      @if($items->isEmpty())
        <div class="px-5 py-10 text-center">
          <div class="mx-auto mb-3 h-12 w-12 rounded-full bg-gray-100 grid place-items-center text-gray-400">
            📅
          </div>
          <p class="text-sm text-gray-500">
            Tidak ada jadwal mengajar untuk hari {{ $hariIni ?? now('Asia/Jakarta')->translatedFormat('l') }}
            pada tahun ajaran {{ $labelTA }}.
          </p>
        </div>
      @else
        <div class="divide-y divide-gray-100">
          @foreach($items as $j)
            @php
              $namaMapel = optional($j->mataPelajaran)->nama_mapel
                  ?? optional($j->mapel)->nama_mapel
                  ?? optional($j->mapel)->nama
                  ?? 'Mata Pelajaran';
            @endphp

            <div class="px-5 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 hover:bg-indigo-50/40 transition">
              <div class="min-w-0">
                <div class="font-semibold text-gray-900 truncate">
                  {{ $namaMapel }}
                </div>

                <div class="mt-2 flex flex-wrap gap-2">
                  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200">
                    {{ optional($j->rombel)->nama_rombel ?? '-' }}
                  </span>

                  <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200">
                    {{ $j->hari ?? '-' }}
                  </span>
                </div>
              </div>

              <div class="shrink-0 flex items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-indigo-600 px-3 py-1 text-xs font-semibold text-white">
                  {{ $j->jam_mulai ? \Illuminate\Support\Str::of($j->jam_mulai)->substr(0,5) : '--:--' }}
                  <span class="mx-1 opacity-80">–</span>
                  {{ $j->jam_selesai ? \Illuminate\Support\Str::of($j->jam_selesai)->substr(0,5) : '--:--' }}
                </span>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </section>
  </div>
@endsection