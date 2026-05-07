@extends('layouts.admin')

@section('title','Anggota Ekskul')

@section('content')
  @php
    $isAdmin     = true;
    $routePrefix = 'admin.';
    $currentTab  = $tab ?? request('tab', 'anggota');

    $presensiSummary     = $presensiSummary ?? collect();
    $presensiTanggalList = $presensiTanggalList ?? collect();
    $presensiMatrixRows  = $presensiMatrixRows ?? collect();
    $bulanOptions        = $bulanOptions ?? collect();
    $bulanDipilih        = $bulanDipilih ?? null;
    $riwayatAnggota      = $riwayatAnggota ?? collect();

    $statusBadge = function ($status) {
        return match ($status) {
            'H' => 'bg-emerald-50 text-emerald-700',
            'I' => 'bg-blue-50 text-blue-700',
            'S' => 'bg-amber-50 text-amber-700',
            'A' => 'bg-rose-50 text-rose-700',
            default => 'bg-gray-50 text-gray-500',
        };
    };

    $statusLabel = function ($status) {
        return match ($status) {
            'H' => 'H',
            'I' => 'I',
            'S' => 'S',
            'A' => 'A',
            default => '-',
        };
    };
  @endphp

<div class="mb-4 flex items-center justify-between">
  <div>
    <h1 class="text-2xl font-semibold">
      Anggota Ekskul – {{ $ekskul->nama }}
    </h1>
    <p class="text-sm text-gray-500">
      Monitoring keanggotaan, presensi, penilaian akhir, dan riwayat anggota ekskul.
    </p>
  </div>

  <a href="{{ route('admin.ekskul.index') }}"
     class="inline-flex items-center gap-2 px-3 py-2 rounded-md border bg-white text-gray-700 hover:bg-gray-50">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd"
            d="M7.707 14.707a1 1 0 01-1.414 0L2.586 11l3.707-3.707a1 1 0 011.414 1.414L5.414 10H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z"
            clip-rule="evenodd" />
    </svg>
    Kembali
  </a>
</div>

<div class="bg-white rounded-2xl border shadow-sm p-5 mb-5">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <div class="text-xs text-gray-500">Nama Ekskul</div>
      <div class="text-lg font-semibold text-gray-900">{{ $ekskul->nama }}</div>
    </div>

    <div>
      <div class="text-xs text-gray-500">Pembina</div>
      <div class="text-sm font-medium text-gray-900">
        {{ $ekskul->pembina->nama ?? '-' }}
      </div>
    </div>

    <div class="md:text-right">
      <div class="text-xs text-gray-500">Jadwal & Lokasi</div>
      <div class="text-sm font-medium text-gray-900">
        @if($ekskul->hari)
          {{ $ekskul->hari }},
        @endif

        @if($ekskul->jam_mulai || $ekskul->jam_selesai)
          {{ $ekskul->jam_mulai ? \Illuminate\Support\Str::substr($ekskul->jam_mulai, 0, 5) : '–' }}
          –
          {{ $ekskul->jam_selesai ? \Illuminate\Support\Str::substr($ekskul->jam_selesai, 0, 5) : '–' }},
        @endif

        {{ $ekskul->lokasi ?? '-' }}
      </div>
    </div>
  </div>
</div>

<div class="mb-4">
  <form method="GET" class="flex flex-col md:flex-row md:items-center gap-3">
    <div class="flex items-center gap-2">
      <label class="text-sm text-gray-600 font-medium">Tahun ajaran</label>
      <select name="tahun_ajaran_id"
              class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach($tahunList as $ta)
          <option value="{{ $ta->id }}" {{ optional($tahunDipilih)->id == $ta->id ? 'selected' : '' }}>
            {{ $ta->nama ?? $ta->nama_tahun }}
            @if(($ta->status ?? null) === 'aktif') (aktif) @endif
          </option>
        @endforeach
      </select>
    </div>

    @if($currentTab === 'presensi')
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-600 font-medium">Bulan</label>
        <select name="bulan"
                class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
          @forelse($bulanOptions as $bln)
            <option value="{{ $bln['value'] }}" {{ $bulanDipilih === $bln['value'] ? 'selected' : '' }}>
              {{ $bln['label'] }}
            </option>
          @empty
            <option value="">Belum ada data</option>
          @endforelse
        </select>
      </div>
    @endif

    <input type="hidden" name="tab" value="{{ $currentTab }}">

    <button type="submit"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
      Terapkan
    </button>
  </form>
</div>

<div class="mb-4 border-b text-sm flex gap-4">
  <a href="{{ route($routePrefix.'ekskul.anggota', [
            'ekskul'          => $ekskul->id,
            'tahun_ajaran_id' => optional($tahunDipilih)->id,
            'tab'             => 'anggota',
        ]) }}"
     class="px-3 pb-2 border-b-2 {{ $currentTab === 'anggota'
          ? 'border-indigo-600 text-indigo-600 font-semibold'
          : 'border-transparent text-gray-500 hover:text-indigo-600' }}">
    Anggota
  </a>

  <a href="{{ route($routePrefix.'ekskul.anggota', [
            'ekskul'          => $ekskul->id,
            'tahun_ajaran_id' => optional($tahunDipilih)->id,
            'tab'             => 'presensi',
            'bulan'           => $bulanDipilih,
        ]) }}"
     class="px-3 pb-2 border-b-2 {{ $currentTab === 'presensi'
          ? 'border-indigo-600 text-indigo-600 font-semibold'
          : 'border-transparent text-gray-500 hover:text-indigo-600' }}">
    Presensi
  </a>

  <a href="{{ route($routePrefix.'ekskul.anggota', [
            'ekskul'          => $ekskul->id,
            'tahun_ajaran_id' => optional($tahunDipilih)->id,
            'tab'             => 'penilaian',
        ]) }}"
     class="px-3 pb-2 border-b-2 {{ $currentTab === 'penilaian'
          ? 'border-indigo-600 text-indigo-600 font-semibold'
          : 'border-transparent text-gray-500 hover:text-indigo-600' }}">
    Penilaian
  </a>

  <a href="{{ route($routePrefix.'ekskul.anggota', [
            'ekskul'          => $ekskul->id,
            'tahun_ajaran_id' => optional($tahunDipilih)->id,
            'tab'             => 'riwayat',
        ]) }}"
     class="px-3 pb-2 border-b-2 {{ $currentTab === 'riwayat'
          ? 'border-indigo-600 text-indigo-600 font-semibold'
          : 'border-transparent text-gray-500 hover:text-indigo-600' }}">
    Riwayat
  </a>
</div>

@if($currentTab === 'anggota')
  <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
    <table class="min-w-full text-sm divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NIS / NISN</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Siswa</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Gabung</th>
          <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse ($anggota as $i => $row)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-2 text-gray-700">{{ $i + 1 }}</td>
            <td class="px-4 py-2 text-gray-700">
              {{ $row->siswa->nis ?? '-' }}<br>
              <span class="text-xs text-gray-500">
                NISN: {{ $row->siswa->nisn ?? '-' }}
              </span>
            </td>
            <td class="px-4 py-2 text-gray-900 font-medium">{{ $row->siswa->nama ?? '-' }}</td>
            <td class="px-4 py-2 text-gray-700">
              {{ $row->tanggal_gabung ? \Carbon\Carbon::parse($row->tanggal_gabung)->format('d M Y') : '-' }}
            </td>
            <td class="px-4 py-2 text-right">
              <form method="POST"
                    action="{{ route('admin.ekskul.anggota.keluarkan', [$ekskul->id, $row->id]) }}"
                    class="form-keluarkan-anggota"
                    data-nama="{{ $row->siswa->nama ?? 'siswa ini' }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-3 py-1.5 rounded-full bg-red-50 text-xs font-medium text-red-700 hover:bg-red-100">
                  Keluarkan
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
              Belum ada anggota aktif pada tahun ajaran ini.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endif

@if($currentTab === 'presensi')
  <div class="space-y-4">
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
      <div class="px-4 py-3 border-b text-sm text-gray-600">
        Rekap presensi ekskul per tanggal pada bulan yang dipilih.
      </div>

      <table class="min-w-full text-sm divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hadir</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Izin</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sakit</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Alfa</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($presensiSummary as $i => $row)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-2 text-gray-700">{{ $i + 1 }}</td>
              <td class="px-4 py-2 text-gray-900 font-medium">
                {{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y') }}
              </td>
              <td class="px-4 py-2 text-gray-700">{{ $row->hadir ?? 0 }}</td>
              <td class="px-4 py-2 text-gray-700">{{ $row->izin ?? 0 }}</td>
              <td class="px-4 py-2 text-gray-700">{{ $row->sakit ?? 0 }}</td>
              <td class="px-4 py-2 text-gray-700">{{ $row->alfa ?? 0 }}</td>
              <td class="px-4 py-2 text-gray-900 font-semibold">{{ $row->total ?? 0 }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                Belum ada data presensi ekskul pada bulan ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
      <div class="px-4 py-3 border-b text-sm text-gray-600">
        Matriks presensi anggota per bulan.
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-[1100px] w-full text-sm divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10 min-w-[240px]">
                Nama Siswa
              </th>

              @forelse($presensiTanggalList as $tgl)
                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider min-w-[72px]">
                  {{ \Carbon\Carbon::parse($tgl)->format('d M') }}
                </th>
              @empty
                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                  Belum ada tanggal
                </th>
              @endforelse

              <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">H</th>
              <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">I</th>
              <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">S</th>
              <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">A</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @forelse($presensiMatrixRows as $row)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 sticky left-0 bg-white z-10">
                  <div class="font-medium text-gray-900">{{ $row->nama_siswa }}</div>
                  <div class="text-xs text-gray-500">
                    {{ $row->nis ?? '-' }} · NISN: {{ $row->nisn ?? '-' }}
                  </div>
                </td>

                @foreach($presensiTanggalList as $tgl)
                  @php
                    $status = $row->statuses[$tgl] ?? null;
                  @endphp
                  <td class="px-3 py-3 text-center">
                    <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 rounded-full text-xs font-semibold {{ $statusBadge($status) }}">
                      {{ $statusLabel($status) }}
                    </span>
                  </td>
                @endforeach

                <td class="px-3 py-3 text-center font-semibold text-emerald-700">{{ $row->hadir }}</td>
                <td class="px-3 py-3 text-center font-semibold text-blue-700">{{ $row->izin }}</td>
                <td class="px-3 py-3 text-center font-semibold text-amber-700">{{ $row->sakit }}</td>
                <td class="px-3 py-3 text-center font-semibold text-rose-700">{{ $row->alfa }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="{{ max($presensiTanggalList->count() + 5, 6) }}"
                    class="px-4 py-6 text-center text-sm text-gray-500">
                  Belum ada data detail presensi untuk bulan ini.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="px-4 py-3 border-t text-xs text-gray-500">
        Keterangan:
        <span class="font-medium text-emerald-700">H</span> = Hadir,
        <span class="font-medium text-blue-700">I</span> = Izin,
        <span class="font-medium text-amber-700">S</span> = Sakit,
        <span class="font-medium text-rose-700">A</span> = Alfa
      </div>
    </div>
  </div>
@endif

@if($currentTab === 'penilaian')
  <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b text-sm text-gray-600">
      Rekap nilai akhir ekskul per anggota aktif.
    </div>

    <table class="min-w-full text-sm divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NIS / NISN</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Siswa</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nilai Akhir</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Predikat</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Deskripsi</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        @php
          $rowsPenilaian = $anggota->filter(function ($row) {
            return !is_null($row->nilai_akhir) || !is_null($row->predikat) || !is_null($row->deskripsi);
          })->values();
        @endphp

        @forelse ($rowsPenilaian as $i => $row)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-2 text-gray-700">{{ $i + 1 }}</td>
            <td class="px-4 py-2 text-gray-700">
              {{ $row->siswa->nis ?? '-' }}<br>
              <span class="text-xs text-gray-500">
                NISN: {{ $row->siswa->nisn ?? '-' }}
              </span>
            </td>
            <td class="px-4 py-2 text-gray-900 font-medium">{{ $row->siswa->nama ?? '-' }}</td>
            <td class="px-4 py-2 text-gray-900">{{ $row->nilai_akhir ?? '-' }}</td>
            <td class="px-4 py-2">
              @if($row->predikat)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                  {{ $row->predikat }}
                </span>
              @else
                <span class="text-gray-400 text-xs">-</span>
              @endif
            </td>
            <td class="px-4 py-2 text-gray-700">{{ $row->deskripsi ?? '-' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
              Belum ada nilai akhir yang diinput untuk tahun ajaran ini.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endif

@if($currentTab === 'riwayat')
  <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b text-sm text-gray-600">
      Riwayat siswa yang sudah keluar dari ekskul pada tahun ajaran yang dipilih.
    </div>

    <table class="min-w-full text-sm divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NIS / NISN</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Siswa</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Gabung</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Keluar</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @forelse ($riwayatAnggota as $i => $row)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-2 text-gray-700">{{ $i + 1 }}</td>
            <td class="px-4 py-2 text-gray-700">
              {{ $row->siswa->nis ?? '-' }}<br>
              <span class="text-xs text-gray-500">
                NISN: {{ $row->siswa->nisn ?? '-' }}
              </span>
            </td>
            <td class="px-4 py-2 text-gray-900 font-medium">{{ $row->siswa->nama ?? '-' }}</td>
            <td class="px-4 py-2 text-gray-700">
              {{ $row->tanggal_gabung ? \Carbon\Carbon::parse($row->tanggal_gabung)->format('d M Y') : '-' }}
            </td>
            <td class="px-4 py-2 text-gray-700">
              {{ $row->tanggal_keluar ? \Carbon\Carbon::parse($row->tanggal_keluar)->format('d M Y') : '-' }}
            </td>
            <td class="px-4 py-2">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 text-xs font-semibold">
                Keluar
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
              Belum ada riwayat siswa keluar pada tahun ajaran ini.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endif

{{-- MODAL KONFIRMASI KELUARKAN ANGGOTA --}}
<div id="modal-keluarkan-anggota"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
  <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
    <div class="border-b px-6 py-4">
      <h3 class="text-lg font-semibold text-gray-900">
        Konfirmasi Keluarkan Anggota
      </h3>
      <p class="mt-1 text-sm text-gray-500">
        Pastikan tindakan ini sudah sesuai sebelum dilanjutkan.
      </p>
    </div>

    <div class="px-6 py-5">
      <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <span id="modal-keluarkan-text">
          Siswa akan dikeluarkan dari ekskul ini.
        </span>
      </div>

      <p class="mt-3 text-xs text-gray-500">
        Data keanggotaan akan dipindahkan ke riwayat keluar pada tahun ajaran yang dipilih.
      </p>
    </div>

    <div class="flex justify-end gap-3 border-t px-6 py-4">
      <button type="button"
              id="btn-batal-keluarkan"
              class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
        Batal
      </button>

      <button type="button"
              id="btn-lanjut-keluarkan"
              class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
        Ya, Keluarkan
      </button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modal-keluarkan-anggota');
    const modalText = document.getElementById('modal-keluarkan-text');
    const btnBatal = document.getElementById('btn-batal-keluarkan');
    const btnLanjut = document.getElementById('btn-lanjut-keluarkan');

    let formAktif = null;

    function bukaModal(form) {
        formAktif = form;

        const nama = form.dataset.nama || 'siswa ini';

        if (modalText) {
            modalText.textContent = 'Keluarkan ' + nama + ' dari ekskul ini?';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        formAktif = null;
    }

    document.querySelectorAll('.form-keluarkan-anggota').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            bukaModal(form);
        });
    });

    if (btnBatal) {
        btnBatal.addEventListener('click', function () {
            tutupModal();
        });
    }

    if (btnLanjut) {
        btnLanjut.addEventListener('click', function () {
            if (formAktif) {
                formAktif.submit();
            }
        });
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                tutupModal();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            tutupModal();
        }
    });
});
</script>
@endsection