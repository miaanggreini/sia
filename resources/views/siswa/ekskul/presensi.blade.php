{{-- resources/views/siswa/ekskul/presensi.blade.php --}}
@extends('layouts.siswa')

@section('title','Presensi Ekskul')

@section('content')
  <div class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">
        Presensi Ekskul – {{ $ekskul->nama }}
      </h1>
      <p class="text-sm text-gray-500">
        Pembina:
        <span class="font-medium text-gray-700">
          {{ $ekskul->pembina->nama ?? '-' }}
        </span>
        @if($tahunAktif)
          · Tahun ajaran:
          <span class="font-medium text-gray-700">
            {{ $tahunAktif->nama ?? $tahunAktif->tahun_ajaran ?? '-' }}
          </span>
        @endif
      </p>
    </div>

    <a href="{{ route('siswa.ekskul.index') }}"
       class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">
      ← Kembali
    </a>
  </div>

  <div class="grid gap-4 md:grid-cols-2 mb-6">
    {{-- RINGKASAN KEHADIRAN --}}
    <div class="rounded-2xl border bg-white p-4 shadow-sm">
      <h2 class="text-sm font-semibold text-gray-800 mb-2">Ringkasan Kehadiran</h2>

      @if($rekap['total'])
        <div class="mb-2 text-sm text-gray-700">
          Total pertemuan:
          <span class="font-semibold">{{ $rekap['total'] }}</span>
        </div>

        <div class="flex flex-wrap gap-3 text-xs text-gray-600 mb-3">
          <span>Hadir: <span class="font-semibold text-emerald-700">{{ $rekap['hadir'] }}</span></span>
          <span>Izin: <span class="font-semibold text-blue-700">{{ $rekap['izin'] }}</span></span>
          <span>Sakit: <span class="font-semibold text-amber-700">{{ $rekap['sakit'] }}</span></span>
          <span>Alfa: <span class="font-semibold text-rose-700">{{ $rekap['alfa'] }}</span></span>
        </div>

        @if(!is_null($rekap['persen_hadir']))
          <div class="mb-1 flex items-center justify-between text-xs text-gray-600">
            <span>Persentase hadir</span>
            <span class="font-semibold text-gray-800">{{ $rekap['persen_hadir'] }}%</span>
          </div>
          <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
            <div class="h-2 rounded-full bg-emerald-500"
                 style="width: {{ min($rekap['persen_hadir'], 100) }}%"></div>
          </div>
        @endif
      @else
        <p class="text-sm text-gray-500">
          Belum ada presensi tercatat untuk ekskul ini.
        </p>
      @endif
    </div>

    {{-- NILAI AKHIR EKSKUL --}}
    <div class="rounded-2xl border bg-white p-4 shadow-sm">
      <h2 class="text-sm font-semibold text-gray-800 mb-2">Nilai Akhir Ekskul</h2>

      @if($anggotaEkskul->nilai_akhir !== null || $anggotaEkskul->predikat !== null)
        <div class="mb-3 flex items-baseline gap-3">
          <div>
            <div class="text-xs text-gray-500 mb-1">Nilai akhir</div>
            <div class="text-3xl font-bold text-gray-900">
              {{ $anggotaEkskul->nilai_akhir ?? '-' }}
            </div>
          </div>
          <div>
            <div class="text-xs text-gray-500 mb-1">Predikat</div>
            @php
              $p = $anggotaEkskul->predikat;
              $badgeClass = match($p) {
                  'A' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                  'B' => 'bg-sky-50 text-sky-700 border-sky-200',
                  'C' => 'bg-amber-50 text-amber-700 border-amber-200',
                  'D' => 'bg-rose-50 text-rose-700 border-rose-200',
                  default => 'bg-gray-50 text-gray-500 border-gray-200',
              };
            @endphp
            <span class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold {{ $badgeClass }}">
              {{ $p ?? '-' }}
            </span>
          </div>
        </div>

        @if($anggotaEkskul->deskripsi)
          <div class="text-xs text-gray-600">
            <div class="mb-1 font-semibold text-gray-700">Catatan Pembina</div>
            <p class="whitespace-pre-line">
              {{ $anggotaEkskul->deskripsi }}
            </p>
          </div>
        @else
          <p class="text-xs text-gray-500">
            Belum ada catatan khusus dari pembina.
          </p>
        @endif
      @else
        <p class="text-sm text-gray-500">
          Nilai akhir ekskul <span class="font-semibold">{{ $ekskul->nama }}</span>
          belum diinput oleh pembina.
        </p>
      @endif
    </div>
  </div>

  {{-- RIWAYAT PRESENSI --}}
  <div class="rounded-2xl border bg-white p-4 shadow-sm overflow-x-auto">
    <div class="mb-3 flex items-center justify-between">
      <h2 class="text-sm font-semibold text-gray-800">Riwayat Presensi</h2>
      <p class="text-xs text-gray-500">Status kehadiran per pertemuan</p>
    </div>

    <table class="min-w-full text-sm">
      <thead>
        <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
          <th class="px-4 py-2 text-left">Tanggal</th>
          <th class="px-4 py-2 text-left">Status</th>
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
                $label = strtoupper($row->status);
                $class = match($row->status) {
                    'hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'izin'  => 'bg-sky-50 text-sky-700 border-sky-200',
                    'sakit' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'alfa'  => 'bg-rose-50 text-rose-700 border-rose-200',
                    default => 'bg-gray-50 text-gray-500 border-gray-200',
                };
              @endphp
              <span class="inline-flex items-center rounded-full border px-3 py-0.5 text-xs font-medium {{ $class }}">
                {{ $label }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-500">
              Belum ada presensi tercatat untuk ekskul ini.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
