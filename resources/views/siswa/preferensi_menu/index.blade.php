@extends('layouts.siswa')

@section('title', 'Pilih Menu Rombel')

@section('content')
@php
  $sudahMemilih = (bool) ($preferensi ?? null);
  $periodeDibuka = (bool) ($bolehMemilih ?? false);

  $alasanTidakBolehMemilih = $alasanTidakBolehMemilih
      ?? 'Kamu belum dapat mengikuti pemilihan rombel saat ini.';

  $pilihanUtama = $preferensi
      ? ($preferensi->pilihan1 ?? $daftarMenu->firstWhere('id', $preferensi->pilihan_1_menu_id))
      : null;

  $pilihanCadangan = $preferensi
      ? ($preferensi->pilihan2 ?? $daftarMenu->firstWhere('id', $preferensi->pilihan_2_menu_id))
      : null;

  $menuDiterima = $preferensi
      ? ($preferensi->menuDiterima ?? $daftarMenu->firstWhere('id', $preferensi->menu_diterima_id))
      : null;

  $rekomUtama = $rekomendasiHasil['utama'] ?? [];
  $rekomCadangan = $rekomendasiHasil['cadangan'] ?? [];

  $namaRombelAsal = $rombelLayakPilih->nama_rombel ?? null;
  $tahunAsal = $rombelLayakPilih->nama_tahun_asal ?? null;
  $semesterAsal = $rombelLayakPilih->semester_asal ?? null;

  $statusBadge = function ($status) {
      return match ($status) {
          'diterima' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
          'cadangan' => 'bg-amber-100 text-amber-700 border-amber-200',
          default => 'bg-gray-100 text-gray-700 border-gray-200',
      };
  };

  $statusLabel = function ($status) {
      return match ($status) {
          'diterima' => 'Diterima',
          'cadangan' => 'Cadangan',
          default => 'Belum Tertempatkan',
      };
  };
@endphp

<div class="space-y-6">

  {{-- HEADER --}}
  <div class="rounded-2xl border bg-white p-6 shadow-sm">
    <div class="flex items-start gap-4">
      <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
        📋
      </div>

      <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900">
          Pilih Menu Rombel
        </h1>

        @if($periode)
          <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-600">
            <span>
              Periode:
              <span class="font-semibold text-gray-800">
                {{ $periode->nama_periode ?? $periode->nama ?? '-' }}
              </span>
            </span>

            <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
              {{ \Carbon\Carbon::parse($periode->tanggal_mulai)->translatedFormat('d M Y H:i') }}
              —
              {{ \Carbon\Carbon::parse($periode->tanggal_selesai)->translatedFormat('d M Y H:i') }}
            </span>

            @if($periodeDibuka)
              <span class="rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                Dibuka
              </span>
            @endif
          </div>
        @else
          <p class="mt-1 text-sm text-gray-500">
            Saat ini belum ada periode pemilihan menu rombel yang sedang dibuka.
          </p>
        @endif

      </div>
    </div>
  </div>

  {{-- ALERT --}}
  @if(session('ok'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
      {{ session('ok') }}
    </div>
  @endif

  @if(session('err'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ session('err') }}
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <ul class="list-disc space-y-1 pl-5">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- INFO KONDISI AKSES --}}
  @if(!$periodeDibuka)
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-700">
      <div class="font-semibold">
        Pemilihan rombel belum dapat dilakukan.
      </div>
      <p class="mt-1">
        {{ $alasanTidakBolehMemilih }}
      </p>
    </div>
  @endif

  {{-- INFO ATURAN --}}
  @if($periodeDibuka)
    <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-5 py-4 text-sm text-indigo-800">
      <div class="mb-1 font-semibold">
        Ketentuan pemilihan menu rombel
      </div>
      <ul class="list-disc space-y-1 pl-5">
        <li>Pemilihan hanya dapat dilakukan oleh siswa kelas X yang sudah diproses layak naik.</li>
        <li>Pilih satu menu utama dan satu menu cadangan.</li>
        <li>Pilihan dapat diubah selama periode pemilihan masih dibuka.</li>
        <li>Hasil akhir rombel ditentukan setelah proses penempatan oleh admin.</li>
      </ul>
    </div>
  @endif

  {{-- RINGKASAN PILIHAN / HASIL --}}
  @if($sudahMemilih)
    <div id="ringkasan-pilihan" class="rounded-2xl border bg-white shadow-sm">
      <div class="border-b px-5 py-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">
              Pilihan Menu Rombel Kamu
            </h2>
            <p class="mt-1 text-sm text-gray-500">
              Ringkasan pilihan menu rombel yang pernah kamu ajukan.
            </p>
          </div>

          @if($periodeDibuka && !$menuDiterima)
            <button type="button"
                    id="btn-ubah-pilihan"
                    class="inline-flex justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
              Ubah Pilihan
            </button>
          @endif
        </div>
      </div>

      <div class="px-5 py-4">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

          {{-- PILIHAN UTAMA --}}
          <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-indigo-700">
              Pilihan Utama
            </div>

            <h3 class="mt-2 text-lg font-bold text-gray-900">
              {{ $pilihanUtama->nama ?? '-' }}
            </h3>

            <p class="mt-1 text-sm text-gray-600 leading-relaxed">
              {{ $pilihanUtama?->mapel?->pluck('nama_mapel')->join(', ') ?: 'Belum ada data mapel' }}
            </p>

            @if(count($rekomUtama))
              <div class="mt-3">
                <p class="mb-2 text-xs font-medium text-gray-500">
                  Rekomendasi prodi:
                </p>

                <div class="flex flex-wrap gap-1.5">
                  @foreach(array_slice($rekomUtama, 0, 5) as $item)
                    <span class="inline-flex items-center rounded-full border border-indigo-200 bg-white px-2.5 py-1 text-[11px] font-medium text-indigo-700">
                      {{ $item['prodi'] }}
                    </span>
                  @endforeach
                </div>
              </div>
            @endif
          </div>

          {{-- PILIHAN CADANGAN --}}
          <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-700">
              Pilihan Cadangan
            </div>

            <h3 class="mt-2 text-lg font-bold text-gray-900">
              {{ $pilihanCadangan->nama ?? '-' }}
            </h3>

            <p class="mt-1 text-sm text-gray-600 leading-relaxed">
              {{ $pilihanCadangan?->mapel?->pluck('nama_mapel')->join(', ') ?: 'Belum ada data mapel' }}
            </p>

            @if(count($rekomCadangan))
              <div class="mt-3">
                <p class="mb-2 text-xs font-medium text-gray-500">
                  Rekomendasi prodi:
                </p>

                <div class="flex flex-wrap gap-1.5">
                  @foreach(array_slice($rekomCadangan, 0, 5) as $item)
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700">
                      {{ $item['prodi'] }}
                    </span>
                  @endforeach
                </div>
              </div>
            @endif
          </div>

        </div>
      </div>
    </div>
  @endif

  {{-- FORM PEMILIHAN --}}
  @if($periodeDibuka)
    <form id="form-pilih-menu"
          method="POST"
          action="{{ url('/siswa/pilih-menu') }}"
          class="{{ $sudahMemilih ? 'hidden' : '' }} rounded-2xl border bg-white p-5 shadow-sm">
      @csrf

      <div class="mb-5">
        <h2 class="text-base font-semibold text-gray-900">
          Form Pemilihan Menu Rombel
        </h2>
        <p class="mt-1 text-sm text-gray-500">
          Pilih menu utama dan menu cadangan. Keduanya tidak boleh sama.
        </p>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label class="mb-1 block text-sm font-semibold text-gray-700">
            Pilihan Utama
          </label>

          <select name="pilihan_1_menu_id"
                  id="pilihan_1_menu_id"
                  class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                  required>
            <option value="">-- Pilih menu utama --</option>
            @foreach($daftarMenu as $menu)
              <option value="{{ $menu->id }}"
                      @selected(old('pilihan_1_menu_id', $preferensiAktif->pilihan_1_menu_id ?? null) == $menu->id)>
                {{ $menu->nama }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="mb-1 block text-sm font-semibold text-gray-700">
            Pilihan Cadangan
          </label>

          <select name="pilihan_2_menu_id"
                  id="pilihan_2_menu_id"
                  class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                  required>
            <option value="">-- Pilih menu cadangan --</option>
            @foreach($daftarMenu as $menu)
              <option value="{{ $menu->id }}"
                      @selected(old('pilihan_2_menu_id', $preferensiAktif->pilihan_2_menu_id ?? null) == $menu->id)>
                {{ $menu->nama }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach($daftarMenu as $menu)
          <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <div class="font-semibold text-gray-900">
              {{ $menu->nama }}
            </div>

            <div class="mt-1 text-xs text-gray-500">
              Kapasitas: {{ $menu->kapasitas_total ?? '-' }} siswa
            </div>

            <div class="mt-2 text-xs text-gray-600">
              <span class="font-semibold">Mapel:</span>
              {{ $menu->mapel->pluck('nama_mapel')->join(', ') ?: '-' }}
            </div>

            <div class="mt-3">
              <div class="text-xs font-semibold text-gray-700">
                Rekomendasi prodi:
              </div>

              @if(!empty($rekomendasiMenu[$menu->id]))
                <div class="mt-2 flex flex-wrap gap-1.5">
                  @foreach(array_slice($rekomendasiMenu[$menu->id], 0, 5) as $item)
                    <span class="rounded-full border border-indigo-100 bg-white px-2.5 py-1 text-[11px] font-medium text-indigo-700">
                      {{ $item['prodi'] }}
                    </span>
                  @endforeach
                </div>
              @else
                <p class="mt-1 text-xs text-gray-500">
                  Belum ada rekomendasi.
                </p>
              @endif
            </div>
          </div>
        @endforeach
      </div>

      <div class="mt-6 flex justify-end gap-3">
        @if($sudahMemilih)
          <button type="button"
                  id="btn-batal-ubah"
                  class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Batal
          </button>
        @endif

        <button type="submit"
                class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
          Simpan Pilihan
        </button>
      </div>
    </form>
  @endif

  {{-- RIWAYAT PEMILIHAN --}}
  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
    <div class="border-b px-5 py-4">
      <h2 class="text-lg font-semibold text-gray-900">
        Riwayat Pemilihan dan Penempatan Rombel
      </h2>
      <p class="mt-1 text-sm text-gray-500">
        Menampilkan riwayat pilihan menu rombel dan hasil penempatan yang pernah kamu ikuti.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
          <tr>
            <th class="px-5 py-3 text-left">No</th>
            <th class="px-5 py-3 text-left">Tahun Ajaran</th>
            <th class="px-5 py-3 text-left">Periode</th>
            <th class="px-5 py-3 text-left">Pilihan</th>
            <th class="px-5 py-3 text-left">Diterima</th>
            <th class="px-5 py-3 text-center">Status</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          @forelse($riwayatPilihan as $item)
            @php
              $ta = $item->periode->tahunAjaran ?? null;
              $tahun = $ta->nama_tahun ?? '-';
              $semester = $ta->semester ?? '-';
              $statusClass = $statusBadge($item->status);
              $statusText = $statusLabel($item->status);
            @endphp

            <tr class="hover:bg-gray-50">
              <td class="px-5 py-4 text-gray-500">
                {{ $loop->iteration }}
              </td>

              <td class="px-5 py-4">
                <div class="font-semibold text-gray-900">
                  {{ $tahun }}
                </div>
                <div class="text-xs text-gray-500">
                  Semester {{ $semester }}
                </div>
              </td>

              <td class="px-5 py-4">
                <div class="font-semibold text-gray-900">
                  {{ $item->periode->nama_periode ?? $item->periode->nama ?? '-' }}
                </div>
                <div class="text-xs text-gray-500">
                  Tujuan kelas {{ $item->periode->tingkat ?? '-' }}
                </div>
              </td>

              <td class="px-5 py-4">
                <div class="space-y-1">
                  <div>1. {{ $item->pilihan1->nama ?? '-' }}</div>
                  <div>2. {{ $item->pilihan2->nama ?? '-' }}</div>
                </div>
              </td>

              <td class="px-5 py-4">
                @if($item->menuDiterima)
                  <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ $item->menuDiterima->nama }}
                  </span>
                @else
                  <span class="text-gray-400">
                    Belum ditempatkan
                  </span>
                @endif
              </td>

              <td class="px-5 py-4 text-center">
                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                  {{ $statusText }}
                </span>
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                Belum ada riwayat pemilihan rombel.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnUbah = document.getElementById('btn-ubah-pilihan');
    const btnBatal = document.getElementById('btn-batal-ubah');
    const form = document.getElementById('form-pilih-menu');
    const ringkasan = document.getElementById('ringkasan-pilihan');

    if (btnUbah && form) {
        btnUbah.addEventListener('click', function () {
            form.classList.remove('hidden');

            if (ringkasan) {
                ringkasan.classList.add('hidden');
            }

            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    if (btnBatal && form) {
        btnBatal.addEventListener('click', function () {
            form.classList.add('hidden');

            if (ringkasan) {
                ringkasan.classList.remove('hidden');
                ringkasan.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
});
</script>
@endsection