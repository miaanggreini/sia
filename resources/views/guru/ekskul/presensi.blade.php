{{-- resources/views/guru/ekskul/presensi.blade.php --}}
@extends('layouts.guru')

@section('title','Presensi Ekskul')

@section('content')
  <div class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold">Presensi Ekskul</h1>
      <p class="text-sm text-gray-500">
        Ekskul <span class="font-semibold">{{ $ekskul->nama }}</span>
        &middot;
        Tahun ajaran:
        <span class="font-semibold">
          {{ $tahunAktif->nama_tahun ?? $tahunAktif->nama ?? '-' }}
        </span>
      </p>
    </div>

    <a href="{{ route('guru.ekskul.index') }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50">
      Kembali
    </a>
  </div>

  {{-- FLASH MESSAGE --}}
  @if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
      {{ session('success') }}
    </div>
  @endif

  @if (session('error'))
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
      {{ session('error') }}
    </div>
  @endif

  {{-- ============= FORM PRESENSI (PER TANGGAL) ============= --}}
  <div class="mb-6 rounded-2xl border bg-white p-4 shadow-sm">
    <form method="POST" action="{{ route('guru.ekskul.presensi.store', $ekskul) }}">
      @csrf

      {{-- TANGGAL --}}
      <div class="mb-4 flex items-center gap-4">
        <div>
          <label class="text-sm text-gray-600">Tanggal</label>
          <input type="date"
                 name="tanggal"
                 value="{{ old('tanggal', $tanggal) }}"
                 class="mt-1 h-9 rounded-lg border-gray-300 text-sm">
          @error('tanggal')
            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
          @enderror
        </div>
        <p class="text-xs text-gray-500">
          Ubah tanggal jika ingin mengisi presensi untuk hari lain, atau untuk mengedit presensi yang sudah tersimpan.
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
            @forelse ($anggota as $index => $row)
              @php
                $presensi = $presensiAda[$row->siswa_id] ?? null;
                $status = old('presensi.'.$row->siswa_id, $presensi->status ?? 'H');
              @endphp
              <tr class="hover:bg-gray-50/60">
                <td class="px-4 py-2 align-middle text-xs text-gray-500">
                  {{ $index + 1 }}
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
                  <select name="presensi[{{ $row->siswa_id }}]"
                          class="h-9 rounded-lg border-gray-300 text-sm">
                    @foreach (['H' => 'Hadir','I' => 'Izin','S' => 'Sakit','A' => 'Alfa'] as $kode => $label)
                      <option value="{{ $kode }}" @selected($status === $kode)>{{ $label }}</option>
                    @endforeach
                  </select>
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

      @if ($anggota->count())
        <div class="mt-4 flex justify-end">
          <button type="submit"
                  class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Simpan Presensi
          </button>
        </div>
      @endif
    </form>
  </div>

  {{-- ============= REKAP + RIWAYAT PRESENSI (SATU CARD) ============= --}}
  <div class="rounded-2xl border bg-white p-4 shadow-sm">
    <div class="mb-3 flex items-center justify-between">
      <h2 class="text-sm font-semibold text-gray-800">Rekap Presensi per Tanggal</h2>
      <p class="text-xs text-gray-500">
        Riwayat sekaligus rekap jumlah siswa Hadir / Izin / Sakit / Alfa pada setiap pertemuan.
      </p>
    </div>

    @if ($rekapTanggal->isEmpty())
      <div class="py-4 text-center text-sm text-gray-500">
        Belum ada data presensi ekskul yang tersimpan.
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
              <th class="px-4 py-2 text-left">Tanggal</th>
              <th class="px-4 py-2 text-center">Hadir</th>
              <th class="px-4 py-2 text-center">Izin</th>
              <th class="px-4 py-2 text-center">Sakit</th>
              <th class="px-4 py-2 text-center">Alfa</th>
              <th class="px-4 py-2 text-center">Total</th>
              <th class="px-4 py-2 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach ($rekapTanggal as $tgl => $items)
              @php
                $counts = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
                foreach ($items as $row) {
                    $counts[$row->status] = $row->jumlah;
                }
                $total = array_sum($counts);
                $tanggalLabel = \Illuminate\Support\Carbon::parse($tgl)->translatedFormat('d F Y');
              @endphp
              <tr class="hover:bg-gray-50/60">
                <td class="px-4 py-2 align-middle text-sm text-gray-900">
                  {{ $tanggalLabel }}
                </td>
                <td class="px-4 py-2 align-middle text-center text-sm">
                  {{ $counts['H'] }}
                </td>
                <td class="px-4 py-2 align-middle text-center text-sm">
                  {{ $counts['I'] }}
                </td>
                <td class="px-4 py-2 align-middle text-center text-sm">
                  {{ $counts['S'] }}
                </td>
                <td class="px-4 py-2 align-middle text-center text-sm">
                  {{ $counts['A'] }}
                </td>
                <td class="px-4 py-2 align-middle text-center text-sm font-semibold text-gray-900">
                  {{ $total }}
                </td>
                <td class="px-4 py-2 align-middle text-right">
                  {{-- Lihat/Ubah mengarahkan ke tanggal tsb di form atas --}}
                  <a href="{{ route('guru.ekskul.presensi.detail', [$ekskul, $tgl]) }}"
   class="inline-flex items-center rounded-full border border-gray-300 px-3 py-1.5 text-[11px] font-medium text-gray-700 hover:bg-gray-50">
  Lihat / Ubah
</a>

                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
@endsection
