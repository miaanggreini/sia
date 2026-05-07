{{-- resources/views/siswa/ekskul/presensi.blade.php --}}
@extends('layouts.siswa')

@section('title','Presensi Ekskul')

@section('content')
  <div class="flex items-center justify-between mb-4">
    <div>
      <h1 class="text-2xl font-semibold">Presensi Ekskul</h1>
      <p class="text-sm text-gray-500">
        Ekskul <span class="font-semibold">{{ $ekskul->nama }}</span>
        – Tahun ajaran {{ $tahunAktif->nama ?? '-' }}.
      </p>
    </div>

    <a href="{{ route('siswa.ekskul.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-md border bg-white text-gray-700 hover:bg-gray-50">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd"
              d="M7.707 14.707a1 1 0 01-1.414 0L2.586 11l3.707-3.707a1 1 0 011.414 1.414L5.414 10H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z"
              clip-rule="evenodd" />
      </svg>
      Kembali
    </a>
  </div>

  {{-- KARTU INFO EKSKUL --}}
  <div class="bg-white rounded-xl border shadow-sm p-4 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <div class="text-xs text-gray-500">Pembina</div>
        <div class="text-sm font-medium text-gray-900">
          {{ $ekskul->pembina->nama ?? '-' }}
        </div>
      </div>

      <div>
        <div class="text-xs text-gray-500">Jadwal</div>
        <div class="text-sm text-gray-900">
          @if($ekskul->hari)
            {{ $ekskul->hari }},
          @endif
          @if($ekskul->jam_mulai || $ekskul->jam_selesai)
            {{ $ekskul->jam_mulai ? \Illuminate\Support\Str::substr($ekskul->jam_mulai,0,5) : '–' }}
            –
            {{ $ekskul->jam_selesai ? \Illuminate\Support\Str::substr($ekskul->jam_selesai,0,5) : '–' }}
          @endif
        </div>
      </div>

      <div>
        <div class="text-xs text-gray-500">Tanggal bergabung</div>
        <div class="text-sm text-gray-900">
          {{ $keanggotaan->tanggal_gabung ?? '-' }}
        </div>
      </div>
    </div>
  </div>

  {{-- REKAP SINGKAT --}}
  <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3">
      <div class="text-xs text-emerald-700">Hadir</div>
      <div class="text-2xl font-semibold text-emerald-900">{{ $rekap['hadir'] }}</div>
    </div>
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
      <div class="text-xs text-blue-700">Izin</div>
      <div class="text-2xl font-semibold text-blue-900">{{ $rekap['izin'] }}</div>
    </div>
    <div class="bg-amber-50 border border-amber-100 rounded-xl p-3">
      <div class="text-xs text-amber-700">Sakit</div>
      <div class="text-2xl font-semibold text-amber-900">{{ $rekap['sakit'] }}</div>
    </div>
    <div class="bg-rose-50 border border-rose-100 rounded-xl p-3">
      <div class="text-xs text-rose-700">Alfa</div>
      <div class="text-2xl font-semibold text-rose-900">{{ $rekap['alfa'] }}</div>
    </div>
  </div>

  {{-- TABEL RIWAYAT PRESENSI --}}
  <div class="bg-white rounded-xl border shadow-sm overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="bg-indigo-600 text-white text-xs uppercase tracking-wide">
          <th class="px-4 py-2 text-left w-40">Tanggal</th>
          <th class="px-4 py-2 text-left w-32">Status</th>
          <th class="px-4 py-2 text-left">Keterangan</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse($riwayat as $row)
          <tr>
            <td class="px-4 py-2">
              {{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d F Y') }}
            </td>
            <td class="px-4 py-2">
              @php
                $label = [
                  'hadir' => 'Hadir',
                  'izin'  => 'Izin',
                  'sakit' => 'Sakit',
                  'alfa'  => 'Alfa',
                ][$row->status] ?? $row->status;
                $badgeClass = match($row->status) {
                  'hadir' => 'bg-emerald-100 text-emerald-800',
                  'izin'  => 'bg-blue-100 text-blue-800',
                  'sakit' => 'bg-amber-100 text-amber-800',
                  'alfa'  => 'bg-rose-100 text-rose-800',
                  default => 'bg-gray-100 text-gray-800',
                };
              @endphp
              <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                {{ $label }}
              </span>
            </td>
            <td class="px-4 py-2">
              {{ $row->keterangan ?? '-' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="px-4 py-6 text-center text-gray-500">
              Belum ada presensi yang dicatat pembina untuk ekskul ini.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
