@extends('layouts.siswa')
@section('title', 'Detail Kehadiran')

@php
  $tz = 'Asia/Jakarta';
@endphp

@section('content')
<div class="space-y-5">
  <div class="flex items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Detail Kehadiran</h1>
      <p class="text-sm text-gray-500 mt-1">
        Riwayat kehadiran per pertemuan untuk mata pelajaran yang dipilih.
      </p>
    </div>

    <a href="{{ route('siswa.kehadiran.index') }}"
       class="inline-flex items-center px-4 py-2 rounded-xl border bg-white text-sm text-gray-700 hover:bg-gray-50">
      Kembali
    </a>
  </div>

  <div class="rounded-2xl border bg-white p-5">
    <div class="text-xl font-semibold text-gray-900">{{ $mapel->nama_mapel }}</div>
    <div class="text-sm text-gray-600">{{ $rombel->nama_rombel }}</div>

    <div class="mt-5 grid grid-cols-2 md:grid-cols-5 gap-3">
      <div class="rounded-xl border bg-gray-50 p-4">
        <div class="text-xs text-gray-500">Total Pertemuan</div>
        <div class="mt-1 text-2xl font-bold text-gray-900">{{ $total }}</div>
      </div>

      <div class="rounded-xl border bg-emerald-50 p-4">
        <div class="text-xs text-emerald-700">Hadir</div>
        <div class="mt-1 text-2xl font-bold text-emerald-800">{{ $count['hadir'] }}</div>
      </div>

      <div class="rounded-xl border bg-blue-50 p-4">
        <div class="text-xs text-blue-700">Izin</div>
        <div class="mt-1 text-2xl font-bold text-blue-800">{{ $count['izin'] }}</div>
      </div>

      <div class="rounded-xl border bg-sky-50 p-4">
        <div class="text-xs text-sky-700">Sakit</div>
        <div class="mt-1 text-2xl font-bold text-sky-800">{{ $count['sakit'] }}</div>
      </div>

      <div class="rounded-xl border bg-rose-50 p-4">
        <div class="text-xs text-rose-700">Alfa</div>
        <div class="mt-1 text-2xl font-bold text-rose-800">{{ $count['alfa'] }}</div>
      </div>
    </div>

    <div class="mt-5">
      <div class="flex items-baseline gap-2">
        <div class="text-3xl font-bold text-gray-900">{{ number_format($persen, 1) }}%</div>
        <div class="text-sm text-gray-500">kehadiran</div>
      </div>

      <div class="mt-3 h-2.5 w-full rounded-full bg-gray-200 overflow-hidden">
        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $persen }}%"></div>
      </div>

      <div class="mt-2 text-sm text-gray-600">
        Hadir {{ $count['hadir'] }} dari {{ $total }} pertemuan yang tercatat.
      </div>
    </div>
  </div>

  <div class="rounded-2xl border bg-white p-5">
    <div class="text-lg font-semibold text-gray-900 mb-4">Riwayat Pertemuan</div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="border-b bg-gray-50 text-gray-600">
          <tr>
            <th class="px-4 py-3 text-left">Pertemuan</th>
            <th class="px-4 py-3 text-left">Tanggal</th>
            <th class="px-4 py-3 text-left">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse($rows as $i => $r)
@php
  $waktuSesi = $r->mulai_pada
      ? \Illuminate\Support\Carbon::parse($r->mulai_pada)->timezone($tz)
      : null;

  $waktuPresensi = null;

  if (!empty($r->dipindai_pada)) {
      $waktuPresensi = \Illuminate\Support\Carbon::parse($r->dipindai_pada)->timezone($tz);
  } elseif (!empty($r->presensi_updated_at)) {
      $waktuPresensi = \Illuminate\Support\Carbon::parse($r->presensi_updated_at)->timezone($tz);
  } elseif (!empty($r->presensi_created_at)) {
      $waktuPresensi = \Illuminate\Support\Carbon::parse($r->presensi_created_at)->timezone($tz);
  }

  $status = $r->status ?? 'alfa';

  $badge = match($status){
    'hadir' => 'bg-emerald-100 text-emerald-800',
    'izin'  => 'bg-blue-100 text-blue-800',
    'sakit' => 'bg-sky-100 text-sky-800',
    'alfa'  => 'bg-rose-100 text-rose-800',
    default => 'bg-rose-100 text-rose-800',
  };
@endphp

            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">Pertemuan {{ $i + 1 }}</td>
              <td class="px-4 py-3">{{$tanggal ->translatedFormat('d M Y • H:i') }} WIB</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">
                  {{ strtoupper($status) }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="px-4 py-6 text-center text-gray-500">
                Belum ada pertemuan yang tercatat.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection