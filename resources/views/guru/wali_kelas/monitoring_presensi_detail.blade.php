@extends('layouts.guru')

@section('content')
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-semibold text-gray-800">
        Detail Presensi (Wali Kelas)
      </h1>
      <p class="text-sm text-gray-500 mt-1">
        Rincian kehadiran siswa untuk satu sesi presensi.
      </p>
    </div>

    <div class="bg-white rounded-2xl shadow border">
      <div class="px-5 py-4 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <div class="text-xs text-gray-500">Kelas Wali (TA Aktif)</div>
          <div class="text-lg font-semibold text-gray-800">
            {{ $rombel->nama_rombel ?? '-' }}
          </div>
          <div class="text-xs text-gray-500 mt-1">
            {{ $sesi->mataPelajaran->nama_mapel ?? '-' }} •
            {{ $sesi->mulai_pada?->timezone('Asia/Jakarta')->translatedFormat('l, d M Y • H:i') ?? '-' }}
          </div>
        </div>
        <div>
          <a href="{{ route('guru.wali.monitoring-presensi') }}"
             class="inline-flex items-center px-3 py-2 rounded-lg border text-sm text-gray-700 hover:bg-gray-50">
            ← Kembali
          </a>
        </div>
      </div>

      <div class="px-5 pb-5 pt-4">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-2 text-left w-10">No</th>
                <th class="px-3 py-2 text-left">Nama</th>
                <th class="px-3 py-2 text-left">NIS</th>
                <th class="px-3 py-2 text-left">Status</th>
                <th class="px-3 py-2 text-left">Waktu Presensi</th>
                <th class="px-3 py-2 text-center w-24">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($siswaList as $i => $s)
                @php
                  $status = $s->status_presensi;
                  $badge  = match($status) {
                    'hadir'     => 'bg-emerald-100 text-emerald-800',
                    'terlambat' => 'bg-amber-100 text-amber-800',
                    'izin'      => 'bg-blue-100 text-blue-800',
                    'sakit'     => 'bg-sky-100 text-sky-800',
                    'alfa'      => 'bg-rose-100 text-rose-800',
                    default     => 'bg-slate-100 text-slate-700',
                  };
                  $label = $status ? strtoupper($status) : 'BELUM';
                @endphp
                <tr class="border-b last:border-0">
                  <td class="px-3 py-2 text-gray-500">{{ $i + 1 }}</td>
                  <td class="px-3 py-2">{{ $s->nama }}</td>
                  <td class="px-3 py-2">{{ $s->nis }}</td>
                  <td class="px-3 py-2">
                    <span class="px-2 py-0.5 rounded {{ $badge }}">{{ $label }}</span>
                  </td>
                  <td class="px-3 py-2">
                    @if($s->dipindai_pada)
                      {{ \Illuminate\Support\Carbon::parse($s->dipindai_pada)->timezone('Asia/Jakarta')->translatedFormat('d M Y • H:i') }}
                    @else
                      -
                    @endif
                  </td>
                  <td class="px-3 py-2 text-center">
                    {{-- Detail riwayat kehadiran siswa ini untuk mapel ini --}}
                    <a href="{{ route('guru.wali.monitoring-presensi.siswa', [$rombel->id, $sesi->mata_pelajaran_id, $s->id]) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold
                              bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100">
                      Detail
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="px-3 py-6 text-center text-gray-500">
                    Belum ada data presensi untuk sesi ini.
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
