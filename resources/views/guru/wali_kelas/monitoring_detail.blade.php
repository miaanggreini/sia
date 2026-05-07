{{-- resources/views/guru/wali_kelas/monitoring_detail.blade.php --}}
@extends('layouts.guru')

@section('content')
  @if($mode === 'presensi')
    {{-- DETAIL PRESENSI --}}
    <div class="flex items-center justify-between mb-4">
      <div>
        <h1 class="text-2xl font-semibold">Detail Presensi</h1>
        <p class="text-sm text-gray-500">
          {{ $mapel?->nama_mapel ?? 'Mapel' }} —
          {{ \Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('d M Y') }}
        </p>
      </div>

      <a href="{{ route('guru.wali.monitoring-presensi', ['rombel_id' => $rombelId]) }}"
         class="px-4 py-2 rounded-xl border text-gray-700 hover:bg-gray-50">
        ← Kembali
      </a>
    </div>

    <div class="bg-white rounded-2xl shadow border overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left w-16">No</th>
            <th class="px-3 py-2 text-left">NIS</th>
            <th class="px-3 py-2 text-left">Nama</th>
            <th class="px-3 py-2 text-left">Status</th>
            <th class="px-3 py-2 text-left">Dipindai Pada</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse($items as $i => $row)
            <tr>
              <td class="px-3 py-2">{{ $i+1 }}</td>
              <td class="px-3 py-2 whitespace-nowrap">{{ $row->nis }}</td>
              <td class="px-3 py-2">{{ $row->nama }}</td>
              <td class="px-3 py-2">
                @php
                  $badge = [
                    'hadir'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'terlambat' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'izin'      => 'bg-sky-50 text-sky-700 border-sky-200',
                    'sakit'     => 'bg-sky-50 text-sky-700 border-sky-200',
                    'alfa'      => 'bg-rose-50 text-rose-700 border-rose-200',
                  ][$row->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                @endphp
                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-medium {{ $badge }}">
                  {{ ucfirst($row->status) }}
                </span>
              </td>
              <td class="px-3 py-2 whitespace-nowrap">
                {{ $row->dipindai_pada
                      ? \Illuminate\Support\Carbon::parse($row->dipindai_pada)->format('H:i:s')
                      : '—' }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                Tidak ada data presensi.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  @else
    {{-- DETAIL PENILAIAN --}}
    <div class="flex items-center justify-between mb-4">
      <div>
        <h1 class="text-2xl font-semibold">Detail Nilai Mapel</h1>
        <p class="text-sm text-gray-500">
          {{ $mapel?->nama_mapel ?? 'Mapel' }} — KKM {{ $kkm }}
        </p>
      </div>

      <a href="{{ route('guru.wali.monitoring-penilaian', ['rombel_id'=>$rombelId,'kkm'=>$kkm]) }}"
         class="px-4 py-2 rounded-xl border text-gray-700 hover:bg-gray-50">
        ← Kembali
      </a>
    </div>

    <div class="bg-white rounded-2xl shadow border overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left w-16">No</th>
            <th class="px-3 py-2 text-left">NIS</th>
            <th class="px-3 py-2 text-left">Nama</th>
            <th class="px-3 py-2 text-center">UH</th>
            <th class="px-3 py-2 text-center">Tugas</th>
            <th class="px-3 py-2 text-center">UTS</th>
            <th class="px-3 py-2 text-center">UAS</th>
            <th class="px-3 py-2 text-center">Rata-rata</th>
            <th class="px-3 py-2 text-center">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse($items as $i => $row)
            @php $tuntas = $row->rata_rata >= $kkm; @endphp
            <tr>
              <td class="px-3 py-2">{{ $i+1 }}</td>
              <td class="px-3 py-2 whitespace-nowrap">{{ $row->nis }}</td>
              <td class="px-3 py-2">{{ $row->nama }}</td>
              <td class="px-3 py-2 text-center">{{ $row->nilai_uh }}</td>
              <td class="px-3 py-2 text-center">{{ $row->nilai_tugas }}</td>
              <td class="px-3 py-2 text-center">{{ $row->nilai_uts }}</td>
              <td class="px-3 py-2 text-center">{{ $row->nilai_uas }}</td>
              <td class="px-3 py-2 text-center font-semibold">
                {{ number_format($row->rata_rata, 2) }}
              </td>
              <td class="px-3 py-2 text-center">
                <span class="inline-flex px-3 py-1 rounded-full border text-xs font-medium
                  {{ $tuntas ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                             : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                  {{ $tuntas ? 'Tuntas' : 'Belum Tuntas' }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="px-3 py-6 text-center text-gray-500">
                Tidak ada data nilai.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  @endif
@endsection
