{{-- resources/views/guru/ekskul/presensi_detail.blade.php --}}
@extends('layouts.guru')

@section('title','Detail Presensi Ekskul')

@section('content')
  @php
    $tglLabel = $tanggalObj->translatedFormat('d F Y');
  @endphp

  <div class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold">Detail Presensi Ekskul</h1>
      <p class="text-sm text-gray-500">
        Ekskul <span class="font-semibold">{{ $ekskul->nama }}</span>
        &middot;
        Tahun ajaran:
        <span class="font-semibold">
          {{ $tahunAktif->nama_tahun ?? $tahunAktif->nama ?? '-' }}
        </span>
        &middot;
        Tanggal:
        <span class="font-semibold">{{ $tglLabel }}</span>
      </p>
    </div>

    <a href="{{ route('guru.ekskul.presensi', [$ekskul, 'tanggal' => $tanggalObj->toDateString()]) }}"
       class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
      Kembali ke Presensi
    </a>
  </div>

  {{-- RINGKASAN TANGGAL INI --}}
  <div class="mb-6 rounded-2xl border bg-white p-4 shadow-sm">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Ringkasan Presensi Tanggal Ini</h2>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
      <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2">
        <div class="text-xs text-emerald-700 uppercase">Hadir</div>
        <div class="text-lg font-semibold text-emerald-900">{{ $ringkasan['H'] }}</div>
      </div>
      <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2">
        <div class="text-xs text-blue-700 uppercase">Izin</div>
        <div class="text-lg font-semibold text-blue-900">{{ $ringkasan['I'] }}</div>
      </div>
      <div class="rounded-xl border border-amber-100 bg-amber-50 px-3 py-2">
        <div class="text-xs text-amber-700 uppercase">Sakit</div>
        <div class="text-lg font-semibold text-amber-900">{{ $ringkasan['S'] }}</div>
      </div>
      <div class="rounded-xl border border-rose-100 bg-rose-50 px-3 py-2">
        <div class="text-xs text-rose-700 uppercase">Alfa</div>
        <div class="text-lg font-semibold text-rose-900">{{ $ringkasan['A'] }}</div>
      </div>
      <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2">
        <div class="text-xs text-gray-600 uppercase">Total</div>
        <div class="text-lg font-semibold text-gray-900">{{ $ringkasan['total'] }}</div>
      </div>
    </div>
  </div>

  {{-- TABEL PRESENSI TANGGAL INI --}}
  <div class="mb-6 rounded-2xl border bg-white p-4 shadow-sm">
    <div class="mb-3 flex items-center justify-between">
      <h2 class="text-sm font-semibold text-gray-800">
        Daftar Presensi Siswa – {{ $tglLabel }}
      </h2>
      <p class="text-xs text-gray-500">
        Menampilkan siapa saja yang hadir / izin / sakit / alfa pada tanggal ini.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
            <th class="px-4 py-2 text-left">No</th>
            <th class="px-4 py-2 text-left">NIS / NISN</th>
            <th class="px-4 py-2 text-left">Nama Siswa</th>
            <th class="px-4 py-2 text-left">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @php $no = 1; @endphp
          @forelse ($anggota as $row)
            @php
              $p = $presensiHariIni[$row->siswa_id] ?? null;
              $status = $p->status ?? null;
              $badgeClass = match($status) {
                  'H' => 'bg-emerald-100 text-emerald-700',
                  'I' => 'bg-blue-100 text-blue-700',
                  'S' => 'bg-amber-100 text-amber-700',
                  'A' => 'bg-rose-100 text-rose-700',
                  default => 'bg-gray-100 text-gray-600',
              };
              $labelStatus = match($status) {
                  'H' => 'Hadir',
                  'I' => 'Izin',
                  'S' => 'Sakit',
                  'A' => 'Alfa',
                  default => 'Belum diisi',
              };
            @endphp
            <tr class="hover:bg-gray-50/60">
              <td class="px-4 py-2 align-middle text-xs text-gray-500">
                {{ $no++ }}
              </td>
              <td class="px-4 py-2 align-middle">
                <div class="text-sm text-gray-800">
                  {{ $row->siswa->nis ?? '-' }}
                </div>
                <div class="text-[11px] text-gray-400">
                  NISN: {{ $row->siswa->nisn ?? '-' }}
                </div>
              </td>
              <td class="px-4 py-2 align-middle text-sm text-gray-900">
                {{ $row->siswa->nama ?? '-' }}
              </td>
              <td class="px-4 py-2 align-middle">
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $badgeClass }}">
                  {{ $labelStatus }}
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                Belum ada anggota aktif pada tahun ajaran ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- REKAP KEHADIRAN PER SISWA (SEMUA PERTEMUAN) --}}
  <div class="rounded-2xl border bg-white p-4 shadow-sm">
    <div class="mb-3 flex items-center justify-between">
      <h2 class="text-sm font-semibold text-gray-800">
        Rekap Kehadiran Siswa (Semua Pertemuan Ekskul)
      </h2>
      <p class="text-xs text-gray-500">
        Total Hadir / Izin / Sakit / Alfa tiap siswa selama tahun ajaran aktif.
      </p>
    </div>

    @if ($anggota->isEmpty())
      <div class="py-4 text-center text-sm text-gray-500">
        Belum ada anggota ekskul untuk tahun ajaran ini.
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
              <th class="px-4 py-2 text-left">NIS / Nama</th>
              <th class="px-4 py-2 text-center">Hadir</th>
              <th class="px-4 py-2 text-center">Izin</th>
              <th class="px-4 py-2 text-center">Sakit</th>
              <th class="px-4 py-2 text-center">Alfa</th>
              <th class="px-4 py-2 text-center">Total</th>
              <th class="px-4 py-2 text-center">%</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach ($anggota as $row)
              @php
                $sId    = $row->siswa_id;
                $data   = $rekapSiswa[$sId] ?? ['H'=>0,'I'=>0,'S'=>0,'A'=>0];
                $total  = $data['H'] + $data['I'] + $data['S'] + $data['A'];
                $persen = $total > 0 ? round(($data['H'] / $total) * 100) : 0;
              @endphp
              <tr class="hover:bg-gray-50/60">
                <td class="px-4 py-2 align-middle">
                  <div class="text-sm text-gray-900">
                    {{ $row->siswa->nis ?? '-' }} – {{ $row->siswa->nama ?? '-' }}
                  </div>
                  <div class="text-[11px] text-gray-400">
                    NISN: {{ $row->siswa->nisn ?? '-' }}
                  </div>
                </td>
                <td class="px-4 py-2 align-middle text-center">
                  {{ $data['H'] }}
                </td>
                <td class="px-4 py-2 align-middle text-center">
                  {{ $data['I'] }}
                </td>
                <td class="px-4 py-2 align-middle text-center">
                  {{ $data['S'] }}
                </td>
                <td class="px-4 py-2 align-middle text-center">
                  {{ $data['A'] }}
                </td>
                <td class="px-4 py-2 align-middle text-center font-semibold text-gray-900">
                  {{ $total }}
                </td>
                <td class="px-4 py-2 align-middle text-center">
                  {{ $persen }}%
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
@endsection
