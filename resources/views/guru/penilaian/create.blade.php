@extends('layouts.guru')

@section('content')
  @php
    $namaMapel = optional($jadwal->mataPelajaran)->nama_mapel
                ?? optional($jadwal->mapel)->nama_mapel
                ?? optional($jadwal->mataPelajaran)->nama
                ?? optional($jadwal->mapel)->nama
                ?? 'Mapel';

    $namaRombel = optional($jadwal->rombel)->nama_rombel
                ?? optional($jadwal->rombel)->nama_kelas
                ?? optional($jadwal->rombel)->nama
                ?? '-';

    $statusInfo = $statusInfo ?? ['status' => 'draft'];
    $isFinal = ($statusInfo['status'] ?? 'draft') === 'final';
    $readOnly = $readOnly ?? $isFinal;

    $prefixMap = [
      'LM1' => 'lm1',
      'LM2' => 'lm2',
      'LM3' => 'lm3',
      'LM4' => 'lm4',
    ];

    $komponen = strtoupper($komponen ?? 'LM1');
    $activePrefix = $prefixMap[$komponen] ?? 'lm1';

    $oldJenisTp1 = old('jenis_tp1', request('jenis_tp1', 'praktik'));
    $oldJenisTp2 = old('jenis_tp2', request('jenis_tp2', 'praktik'));
    $oldJenisTp3 = old('jenis_tp3', request('jenis_tp3', 'teori'));
    $oldJenisTp4 = old('jenis_tp4', request('jenis_tp4', 'teori'));

    $oldBobotPraktik = old('bobot_praktik', '');
    $oldBobotTeori = old('bobot_teori', '');

    $lmList = ['LM1', 'LM2', 'LM3', 'LM4'];

    $nextLm = null;

    foreach ($lmList as $lm) {
      if (($progress['missing'][$lm] ?? 0) > 0 && $lm !== $komponen) {
        $nextLm = $lm;
        break;
      }
    }

    if (!$nextLm && (($progress['missing'][$komponen] ?? 0) > 0)) {
      $nextLm = $komponen;
    }

    $semesterLabel = $semesterAktif ?? ($taAktif->semester ?? '');
    $tahunAjaranId = $taAktif->id ?? '';
  @endphp

  {{-- HEADER RINGKAS --}}
  <div class="mb-5 rounded-2xl border bg-white shadow-sm p-5">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          Input Nilai {{ $komponen }}
        </h1>

        <div class="mt-3 flex flex-wrap gap-2 text-sm">
          <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">
            Kelas: <strong>{{ $namaRombel }}</strong>
          </span>

          <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700">
            Mapel: <strong>{{ $namaMapel }}</strong>
          </span>

          <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">
            TA: <strong>{{ $taAktif->nama_tahun ?? '-' }}</strong>
          </span>

          <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">
            Semester: <strong>{{ ucfirst($semesterLabel ?: '-') }}</strong>
          </span>

          <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700">
            KKM: <strong>{{ isset($kkm) && $kkm !== null ? number_format((float) $kkm, 0) : '-' }}</strong>
          </span>
        </div>

        {{-- NAVIGASI LM --}}
        <div class="flex flex-wrap gap-2 mt-4">
          @foreach($lmList as $lm)
            <a href="{{ route('guru.penilaian.create', [
                'rombel_id' => $jadwal->rombel_id,
                'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                'komponen' => $lm,
              ]) }}"
              class="px-4 py-2 rounded-lg text-sm font-medium border transition
                {{ $komponen === $lm
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
              Isi {{ $lm }}
            </a>
          @endforeach
        </div>
      </div>

      <div>
        @if($isFinal)
          <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
            FINAL
          </span>
        @else
          <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-200">
            DRAFT
          </span>
        @endif

        @if($isFinal && !empty($statusInfo['finalized_at']))
          <div class="mt-2 text-xs text-gray-500">
            Difinalisasi: {{ \Illuminate\Support\Carbon::parse($statusInfo['finalized_at'])->translatedFormat('d M Y H:i') }}
          </div>
        @endif
      </div>
    </div>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <div class="font-semibold mb-1">Terjadi kesalahan:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST"
        action="{{ route('guru.penilaian.store') }}"
        class="bg-white rounded-lg shadow border mb-6"
        id="formNilai"
        data-jadwal-id="{{ $jadwal->id }}"
        data-rombel-id="{{ $jadwal->rombel_id }}"
        data-mapel-id="{{ $jadwal->mata_pelajaran_id }}"
        data-komponen="{{ $komponen }}"
        data-semester="{{ $semesterLabel }}"
        data-tahun-ajaran-id="{{ $tahunAjaranId }}">
    @csrf

    <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
    <input type="hidden" name="rombel_id" value="{{ $jadwal->rombel_id }}">
    <input type="hidden" name="mata_pelajaran_id" value="{{ $jadwal->mata_pelajaran_id }}">
    <input type="hidden" name="komponen" value="{{ $komponen }}">

    {{-- PENGATURAN BOBOT --}}
    <div class="px-4 py-3 border-b">
      <h2 class="font-semibold text-gray-800">Pengaturan Bobot {{ $komponen }}</h2>
      <p class="text-xs text-gray-500 mt-1">
        Pilih kategori setiap TP, lalu tentukan bobot Praktik dan Teori. Total bobot wajib 100%.
      </p>
    </div>

    <div class="px-4 py-4 border-b bg-gray-50">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white border rounded-xl p-4">
          <div class="font-semibold text-sm text-gray-800 mb-3">Kategori TP</div>

          <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            @for($tp = 1; $tp <= 4; $tp++)
              @php
                $oldJenis = ${"oldJenisTp{$tp}"};
              @endphp

              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">TP{{ $tp }}</label>
                <select
                  name="jenis_tp{{ $tp }}"
                  class="jenis-tp w-full rounded-lg border-gray-300 text-sm"
                  data-tp="{{ $tp }}"
                  {{ $readOnly ? 'disabled' : '' }}
                >
                  <option value="praktik" {{ $oldJenis === 'praktik' ? 'selected' : '' }}>Praktik</option>
                  <option value="teori" {{ $oldJenis === 'teori' ? 'selected' : '' }}>Teori</option>
                </select>
              </div>
            @endfor
          </div>
        </div>

        <div class="bg-white border rounded-xl p-4">
          <div class="font-semibold text-sm text-gray-800 mb-3">Bobot Komponen</div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Bobot Praktik (%)</label>
              <input
                type="number"
                name="bobot_praktik"
                id="bobotPraktik"
                class="w-full rounded-lg border-gray-300 text-sm text-right"
                min="0"
                max="100"
                step="0.01"
                value="{{ $oldBobotPraktik }}"
                placeholder="Isi bobot"
                {{ $readOnly ? 'disabled' : '' }}
              >
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Bobot Teori (%)</label>
              <input
                type="number"
                name="bobot_teori"
                id="bobotTeori"
                class="w-full rounded-lg border-gray-300 text-sm text-right"
                min="0"
                max="100"
                step="0.01"
                value="{{ $oldBobotTeori }}"
                placeholder="Isi bobot"
                {{ $readOnly ? 'disabled' : '' }}
              >
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Total Bobot</label>
              <div id="totalBobotBox" class="rounded-lg border px-3 py-2 text-sm font-semibold bg-gray-50 text-gray-500 text-center">
                —
              </div>
            </div>
          </div>

          <div id="bobotWarning" class="hidden mt-3 rounded-lg bg-red-50 border border-red-100 px-3 py-2 text-xs text-red-700">
            Total bobot Praktik dan Teori harus 100%.
          </div>
        </div>
      </div>
    </div>

    {{-- INPUT NILAI --}}
    <div class="px-4 py-3 border-b">
      <h2 class="font-semibold text-gray-800">Input Nilai {{ $komponen }}</h2>
      <p class="text-xs text-gray-500 mt-1">
        Nilai default 0. TP yang tidak digunakan boleh dibiarkan 0. Nilai {{ $komponen }} akan dihitung dari TP yang benar-benar diisi.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-indigo-50 text-indigo-700">
          <tr>
            <th class="px-4 py-3 text-left">No</th>
            <th class="px-4 py-3 text-left min-w-[220px]">Nama Siswa</th>
            <th class="px-4 py-3 text-left">
              <div>TP1</div>
              <div class="text-[11px] font-normal text-indigo-500 tp-label" data-label-tp="1">Praktik</div>
            </th>
            <th class="px-4 py-3 text-left">
              <div>TP2</div>
              <div class="text-[11px] font-normal text-indigo-500 tp-label" data-label-tp="2">Praktik</div>
            </th>
            <th class="px-4 py-3 text-left">
              <div>TP3</div>
              <div class="text-[11px] font-normal text-indigo-500 tp-label" data-label-tp="3">Teori</div>
            </th>
            <th class="px-4 py-3 text-left">
              <div>TP4</div>
              <div class="text-[11px] font-normal text-indigo-500 tp-label" data-label-tp="4">Teori</div>
            </th>
            <th class="px-4 py-3 text-left">
              <div>{{ $komponen }}</div>
              <div class="text-[11px] font-normal text-indigo-500">Nilai LM</div>
            </th>
          </tr>
        </thead>

        <tbody class="divide-y">
          @forelse($siswa as $i => $s)
            @php
              $row = $nilai[$s->id] ?? null;

              $tp1Val = old("nilai.{$s->id}.tp1", data_get($row, "{$activePrefix}_tp1"));
              $tp2Val = old("nilai.{$s->id}.tp2", data_get($row, "{$activePrefix}_tp2"));
              $tp3Val = old("nilai.{$s->id}.tp3", data_get($row, "{$activePrefix}_tp3"));
              $tp4Val = old("nilai.{$s->id}.tp4", data_get($row, "{$activePrefix}_tp4"));

              $currentLm = data_get($row, "{$activePrefix}_nilai");
            @endphp

            <tr class="hover:bg-gray-50 nilai-row" data-siswa-id="{{ $s->id }}">
              <td class="px-4 py-3">{{ $i + 1 }}</td>

              <td class="px-4 py-3">
                <div class="font-medium text-gray-900">{{ $s->nama }}</div>
                <div class="text-xs text-gray-500">{{ $s->nis ?? $s->nisn ?? '' }}</div>
              </td>

              @for($tp = 1; $tp <= 4; $tp++)
                @php
                  $fieldVal = ${"tp{$tp}Val"};
                  $displayVal = ($fieldVal !== null && $fieldVal !== '') ? $fieldVal : 0;
                @endphp

                <td class="px-4 py-3">
                  <input
                    type="number"
                    name="nilai[{{ $s->id }}][tp{{ $tp }}]"
                    class="nilai-tp w-24 rounded border-gray-300 text-right"
                    data-tp="{{ $tp }}"
                    min="0"
                    max="100"
                    step="0.01"
                    placeholder="0"
                    value="{{ $displayVal }}"
                    {{ $readOnly ? 'disabled' : '' }}
                  >
                </td>
              @endfor

              <td class="px-4 py-3 font-medium text-indigo-700">
                {{ $currentLm !== null ? number_format($currentLm, 2) : '0.00' }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                Tidak ada siswa pada kelas ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-4 border-t flex items-center justify-between gap-2 flex-wrap">
      <a href="{{ route('guru.penilaian.index') }}" class="px-4 py-2 rounded-md border hover:bg-gray-50">
        Kembali
      </a>

      <div class="flex items-center gap-2 flex-wrap">
        @if($nextLm)
          <a href="{{ route('guru.penilaian.create', [
              'rombel_id' => $jadwal->rombel_id,
              'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
              'komponen' => $nextLm,
            ]) }}"
            class="px-4 py-2 rounded-md border border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
            Isi LM Belum Lengkap: {{ $nextLm }}
          </a>
        @endif

        @unless($isFinal)
          <button type="submit" id="btnSimpanNilai" class="px-5 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
            Simpan Nilai
          </button>
        @endunless
      </div>
    </div>
  </form>

  {{-- RINGKASAN --}}
  <div class="bg-white rounded-lg shadow border mb-6">
    <div class="px-4 py-3 border-b">
      <h2 class="font-semibold text-gray-800">Ringkasan Penilaian</h2>
      <p class="text-xs text-gray-500 mt-1">
        Rekap LM1–LM4 dan nilai akhir. Status tuntas/tidak tuntas dihitung berdasarkan KKM mata pelajaran.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-700">
          <tr>
            <th class="px-4 py-3 text-left">No</th>
            <th class="px-4 py-3 text-left">Nama Siswa</th>
            <th class="px-4 py-3 text-left">LM1</th>
            <th class="px-4 py-3 text-left">LM2</th>
            <th class="px-4 py-3 text-left">LM3</th>
            <th class="px-4 py-3 text-left">LM4</th>
            <th class="px-4 py-3 text-left">Nilai Akhir</th>
            <th class="px-4 py-3 text-left">KKM</th>
            <th class="px-4 py-3 text-left">Status</th>
          </tr>
        </thead>

        <tbody class="divide-y">
          @forelse($siswa as $i => $s)
            @php
              $row = $nilai[$s->id] ?? null;
            @endphp

            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">{{ $i + 1 }}</td>

              <td class="px-4 py-3">
                <div class="font-medium text-gray-900">{{ $s->nama }}</div>
                <div class="text-xs text-gray-500">{{ $s->nis ?? $s->nisn ?? '' }}</div>
              </td>

              <td class="px-4 py-3">{{ $row?->lm1_nilai !== null ? number_format($row->lm1_nilai, 2) : '0.00' }}</td>
              <td class="px-4 py-3">{{ $row?->lm2_nilai !== null ? number_format($row->lm2_nilai, 2) : '0.00' }}</td>
              <td class="px-4 py-3">{{ $row?->lm3_nilai !== null ? number_format($row->lm3_nilai, 2) : '0.00' }}</td>
              <td class="px-4 py-3">{{ $row?->lm4_nilai !== null ? number_format($row->lm4_nilai, 2) : '0.00' }}</td>

              <td class="px-4 py-3 font-semibold">
                {{ $row?->nilai_akhir !== null ? number_format($row->nilai_akhir, 2) : '0.00' }}
              </td>

              <td class="px-4 py-3 font-medium text-gray-700">
                {{ isset($kkm) && $kkm !== null ? number_format((float) $kkm, 0) : '-' }}
              </td>

              <td class="px-4 py-3">
                @if(($row?->status ?? null) === 'tuntas')
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                    Tuntas
                  </span>
                @elseif(($row?->status ?? null) === 'tidak_tuntas' && $row?->nilai_akhir !== null)
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                    Tidak Tuntas
                  </span>
                @else
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-500 border border-gray-200">
                    Belum Dinilai
                  </span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                Belum ada data nilai.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-4 border-t flex items-center justify-between flex-wrap gap-3">
      <div class="text-sm text-gray-700">
        Kelengkapan:
        LM1 <span class="{{ ($progress['missing']['LM1'] ?? 0) ? 'text-red-600' : 'text-green-600' }}">
          {{ ($progress['total'] ?? 0) - ($progress['missing']['LM1'] ?? 0) }}/{{ $progress['total'] ?? 0 }}
        </span>,
        LM2 <span class="{{ ($progress['missing']['LM2'] ?? 0) ? 'text-red-600' : 'text-green-600' }}">
          {{ ($progress['total'] ?? 0) - ($progress['missing']['LM2'] ?? 0) }}/{{ $progress['total'] ?? 0 }}
        </span>,
        LM3 <span class="{{ ($progress['missing']['LM3'] ?? 0) ? 'text-red-600' : 'text-green-600' }}">
          {{ ($progress['total'] ?? 0) - ($progress['missing']['LM3'] ?? 0) }}/{{ $progress['total'] ?? 0 }}
        </span>,
        LM4 <span class="{{ ($progress['missing']['LM4'] ?? 0) ? 'text-red-600' : 'text-green-600' }}">
          {{ ($progress['total'] ?? 0) - ($progress['missing']['LM4'] ?? 0) }}/{{ $progress['total'] ?? 0 }}
        </span>
      </div>

      @unless($isFinal)
        <form id="form-finalisasi" method="POST" action="{{ route('guru.penilaian.finalize') }}">
          @csrf
          <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">

          <button type="button"
                  onclick="openModalFinalisasi()"
                  class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  {{ ($progress['missing_any'] ?? 0) > 0 ? 'disabled' : '' }}>
            Finalisasi & Kunci Nilai
          </button>
        </form>
      @endunless
    </div>
  </div>

  {{-- MODAL FINALISASI --}}
  @unless($isFinal)
    <div id="modalFinalisasi"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">

      <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden animate-modalFinalisasi">
        <div class="px-6 pt-6 pb-4 text-center">
          <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-7 w-7 text-emerald-600"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">
              <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <h3 class="text-lg font-semibold text-gray-900">
            Finalisasi Nilai?
          </h3>

          <p class="mt-2 text-sm leading-6 text-gray-600">
            Finalisasi nilai akan mengunci seluruh nilai pada semester ini.
            Setelah dikunci, nilai tidak dapat diedit kembali.
          </p>
        </div>

        <div class="flex items-center justify-end gap-2 border-t bg-gray-50 px-6 py-4">
          <button type="button"
                  onclick="closeModalFinalisasi()"
                  class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-100">
            Batal
          </button>

          <button type="button"
                  onclick="submitFinalisasi()"
                  class="px-4 py-2 rounded-lg bg-emerald-600 text-sm font-medium text-white hover:bg-emerald-700">
            Ya, Finalisasi
          </button>
        </div>
      </div>
    </div>
  @endunless

  <style>
    @keyframes modalFinalisasi {
      from {
        opacity: 0;
        transform: translateY(12px) scale(0.96);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .animate-modalFinalisasi {
      animation: modalFinalisasi 0.2s ease-out;
    }
  </style>

  {{-- SCRIPT FINALISASI + BOBOT LOCALSTORAGE --}}
  <script>
    function openModalFinalisasi() {
      const modal = document.getElementById('modalFinalisasi');

      if (!modal) {
        return;
      }

      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.classList.add('overflow-hidden');
    }

    function closeModalFinalisasi() {
      const modal = document.getElementById('modalFinalisasi');

      if (!modal) {
        return;
      }

      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.classList.remove('overflow-hidden');
    }

    function submitFinalisasi() {
      const form = document.getElementById('form-finalisasi');

      if (form) {
        form.submit();
      }
    }

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeModalFinalisasi();
      }
    });

    document.getElementById('modalFinalisasi')?.addEventListener('click', function(e) {
      if (e.target === this) {
        closeModalFinalisasi();
      }
    });

    document.addEventListener('DOMContentLoaded', function () {
      const formNilai = document.getElementById('formNilai');
      const jenisSelects = document.querySelectorAll('.jenis-tp');
      const bobotPraktikInput = document.getElementById('bobotPraktik');
      const bobotTeoriInput = document.getElementById('bobotTeori');
      const totalBobotBox = document.getElementById('totalBobotBox');
      const bobotWarning = document.getElementById('bobotWarning');
      const btnSimpan = document.getElementById('btnSimpanNilai');

      if (!formNilai) {
        return;
      }

      const jadwalId = String(formNilai.dataset.jadwalId || '');
      const rombelId = String(formNilai.dataset.rombelId || '');
      const mapelId = String(formNilai.dataset.mapelId || '');
      const komponen = String(formNilai.dataset.komponen || '').toUpperCase();
      const semesterRaw = String(formNilai.dataset.semester || '');
      const semesterLower = semesterRaw.toLowerCase();
      const tahunAjaranId = String(formNilai.dataset.tahunAjaranId || '');

      function getExcelStorageKeys() {
        return [
          ['bobot_penilaian', jadwalId, tahunAjaranId, semesterRaw, komponen].join('_'),
          ['bobot_penilaian', jadwalId, tahunAjaranId, semesterLower, komponen].join('_'),
        ];
      }

      function getManualStorageKey() {
        return `sia_penilaian_bobot_${tahunAjaranId}_${semesterRaw}_${jadwalId}_${rombelId}_${mapelId}_${komponen}`;
      }

      function findExcelConfigFromLocalStorage() {
        const keys = getExcelStorageKeys();

        for (const key of keys) {
          const saved = localStorage.getItem(key);

          if (saved) {
            return saved;
          }
        }

        const prefix = ['bobot_penilaian', jadwalId, tahunAjaranId].join('_');

        for (let i = 0; i < localStorage.length; i++) {
          const key = localStorage.key(i);

          if (
            key &&
            key.startsWith(prefix) &&
            key.toUpperCase().endsWith('_' + komponen)
          ) {
            const saved = localStorage.getItem(key);

            if (saved) {
              return saved;
            }
          }
        }

        return null;
      }

      function toNumber(value) {
        const parsed = parseFloat(String(value ?? '').replace(',', '.'));
        return Number.isFinite(parsed) ? parsed : 0;
      }

      function normalizeBobot(value) {
        let number = toNumber(value);

        if (number < 0) {
          number = 0;
        }

        if (number > 100) {
          number = 100;
        }

        return number;
      }

      function cleanBobotDisplay(value) {
        if (!Number.isFinite(value)) {
          return '';
        }

        return String(parseFloat(value.toFixed(2)));
      }

      function getJenisValue(tp, fallback) {
        return document.querySelector(`[name="jenis_tp${tp}"]`)?.value || fallback;
      }

      function makePayload() {
        return {
          jenis_tp1: getJenisValue(1, 'praktik'),
          jenis_tp2: getJenisValue(2, 'praktik'),
          jenis_tp3: getJenisValue(3, 'teori'),
          jenis_tp4: getJenisValue(4, 'teori'),
          bobot_praktik: bobotPraktikInput?.value || '',
          bobot_teori: bobotTeoriInput?.value || '',
          saved_at: new Date().toISOString()
        };
      }

      function saveKonfigurasiBobot() {
        const payload = makePayload();
        const manualKey = getManualStorageKey();

        localStorage.setItem(manualKey, JSON.stringify(payload));

        getExcelStorageKeys().forEach(function(key) {
          localStorage.setItem(key, JSON.stringify({
            jadwal_id: jadwalId,
            tahun_ajaran_id: tahunAjaranId,
            semester: semesterRaw,
            komponen: komponen,
            ...payload
          }));
        });
      }

      function setSelectValue(name, value) {
        const select = document.querySelector(`[name="${name}"]`);

        if (!select || !value) {
          return;
        }

        const targetValue = String(value).toLowerCase();

        Array.from(select.options).forEach(function(option) {
          if (String(option.value).toLowerCase() === targetValue) {
            select.value = option.value;
          }
        });
      }

      function loadKonfigurasiBobot() {
        let saved = findExcelConfigFromLocalStorage();

        if (!saved) {
          saved = localStorage.getItem(getManualStorageKey());
        }

        if (!saved) {
          return;
        }

        try {
          const payload = JSON.parse(saved);

          setSelectValue('jenis_tp1', payload.jenis_tp1 || 'praktik');
          setSelectValue('jenis_tp2', payload.jenis_tp2 || 'praktik');
          setSelectValue('jenis_tp3', payload.jenis_tp3 || 'teori');
          setSelectValue('jenis_tp4', payload.jenis_tp4 || 'teori');

          if (bobotPraktikInput && payload.bobot_praktik !== undefined && payload.bobot_praktik !== null) {
            bobotPraktikInput.value = cleanBobotDisplay(toNumber(payload.bobot_praktik));
          }

          if (bobotTeoriInput && payload.bobot_teori !== undefined && payload.bobot_teori !== null) {
            bobotTeoriInput.value = cleanBobotDisplay(toNumber(payload.bobot_teori));
          }
        } catch (error) {
          console.warn('Gagal membaca konfigurasi bobot:', error);
        }
      }

      function autoLengkapiBobot(activeId) {
        if (!bobotPraktikInput || !bobotTeoriInput) {
          return;
        }

        if (activeId === 'bobotPraktik') {
          const praktikRaw = bobotPraktikInput.value;

          if (praktikRaw === '') {
            bobotTeoriInput.value = '';
            saveKonfigurasiBobot();
            updateBobotState();
            return;
          }

          const praktik = normalizeBobot(praktikRaw);
          const teori = 100 - praktik;

          bobotPraktikInput.value = cleanBobotDisplay(praktik);
          bobotTeoriInput.value = cleanBobotDisplay(teori);
        }

        if (activeId === 'bobotTeori') {
          const teoriRaw = bobotTeoriInput.value;

          if (teoriRaw === '') {
            bobotPraktikInput.value = '';
            saveKonfigurasiBobot();
            updateBobotState();
            return;
          }

          const teori = normalizeBobot(teoriRaw);
          const praktik = 100 - teori;

          bobotTeoriInput.value = cleanBobotDisplay(teori);
          bobotPraktikInput.value = cleanBobotDisplay(praktik);
        }

        saveKonfigurasiBobot();
      }

      function getJenisMap() {
        const map = {};

        jenisSelects.forEach(function (select) {
          map[select.dataset.tp] = select.value;
        });

        return map;
      }

      function updateTpLabels() {
        const jenisMap = getJenisMap();

        document.querySelectorAll('.tp-label').forEach(function (label) {
          const tp = label.dataset.labelTp;
          const jenis = jenisMap[tp] || '-';

          label.textContent = jenis.charAt(0).toUpperCase() + jenis.slice(1);
        });
      }

      function updateBobotState() {
        const praktikRaw = bobotPraktikInput?.value ?? '';
        const teoriRaw = bobotTeoriInput?.value ?? '';

        const praktikKosong = praktikRaw === '';
        const teoriKosong = teoriRaw === '';

        const praktik = toNumber(praktikRaw);
        const teori = toNumber(teoriRaw);
        const total = praktik + teori;

        const sudahDiisi = !praktikKosong || !teoriKosong;
        const valid = !praktikKosong && !teoriKosong && Math.abs(total - 100) <= 0.01;

        if (totalBobotBox) {
          totalBobotBox.textContent = sudahDiisi
            ? total.toFixed(2).replace(/\.00$/, '') + '%'
            : '—';

          totalBobotBox.classList.remove(
            'border-green-200',
            'bg-green-50',
            'text-green-700',
            'border-red-200',
            'bg-red-50',
            'text-red-700',
            'border-gray-200',
            'bg-gray-50',
            'text-gray-500'
          );

          if (!sudahDiisi) {
            totalBobotBox.classList.add('border-gray-200', 'bg-gray-50', 'text-gray-500');
          } else if (valid) {
            totalBobotBox.classList.add('border-green-200', 'bg-green-50', 'text-green-700');
          } else {
            totalBobotBox.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
          }
        }

        if (bobotWarning) {
          bobotWarning.classList.toggle('hidden', !sudahDiisi || valid);
        }

        if (btnSimpan) {
          btnSimpan.disabled = !valid;
        }

        return valid;
      }

      function updateAllState() {
        updateTpLabels();
        updateBobotState();
      }

      loadKonfigurasiBobot();
      updateAllState();

      jenisSelects.forEach(function (select) {
        select.addEventListener('change', function () {
          saveKonfigurasiBobot();
          updateAllState();
        });
      });

      if (bobotPraktikInput) {
        bobotPraktikInput.addEventListener('input', function () {
          autoLengkapiBobot('bobotPraktik');
          updateAllState();
        });
      }

      if (bobotTeoriInput) {
        bobotTeoriInput.addEventListener('input', function () {
          autoLengkapiBobot('bobotTeori');
          updateAllState();
        });
      }

      if (formNilai) {
        formNilai.addEventListener('submit', function () {
          saveKonfigurasiBobot();
        });
      }
    });
  </script>
@endsection