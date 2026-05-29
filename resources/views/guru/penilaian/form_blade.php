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

  $komponen = $komponen ?? 'LM1';
  $activePrefix = $prefixMap[$komponen] ?? 'lm1';

  $jenisTp1 = old('jenis_tp1', request('jenis_tp1', 'praktik'));
  $jenisTp2 = old('jenis_tp2', request('jenis_tp2', 'praktik'));
  $jenisTp3 = old('jenis_tp3', request('jenis_tp3', 'teori'));
  $jenisTp4 = old('jenis_tp4', request('jenis_tp4', 'teori'));

  $bobotPraktik = old('bobot_praktik', request('bobot_praktik', 50));
  $bobotTeori = old('bobot_teori', request('bobot_teori', 50));

  $progress = $progress ?? [
    'total' => $siswa->count() ?? 0,
    'missing' => ['LM1' => 0, 'LM2' => 0, 'LM3' => 0, 'LM4' => 0],
    'missing_any' => 0,
  ];
@endphp

<div class="mb-4">
  <h1 class="text-2xl font-semibold">Input Nilai</h1>
  <p class="text-sm text-gray-500 mt-1">
    Kelas: <span class="font-medium text-gray-700">{{ $namaRombel }}</span>
    <span class="mx-2">•</span>
    Mapel: <span class="font-medium text-indigo-700">{{ $namaMapel }}</span>
    <span class="mx-2">•</span>
    Komponen aktif: <span class="font-medium">{{ $komponen }}</span>

    @if(!empty($taAktif))
      <span class="mx-2">•</span>
      TA: <span class="font-medium">{{ $taAktif->nama_tahun ?? '—' }}</span>
      <span class="mx-2">•</span>
      Semester: <span class="font-medium">{{ ucfirst($semesterAktif ?? '—') }}</span>
    @endif
  </p>
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

<div class="bg-white rounded-lg shadow border mb-6">
  <div class="px-4 py-3 border-b flex items-center justify-between flex-wrap gap-2">
    <div>
      <h2 class="font-semibold text-gray-800">Informasi Penilaian</h2>
      <p class="text-xs text-gray-500 mt-1">
        Guru dapat mengisi nilai TP secara bertahap. TP bernilai 0 dianggap belum digunakan dan tidak ikut menghitung nilai {{ $komponen }}.
      </p>
    </div>

    <div class="text-xs">
      @if($isFinal)
        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
          Status: FINAL — terkunci
        </span>
        @if(!empty($statusInfo['finalized_at']))
          <div class="mt-2 text-gray-500">
            Difinalisasi: {{ \Illuminate\Support\Carbon::parse($statusInfo['finalized_at'])->translatedFormat('d M Y H:i') }}
          </div>
        @endif
      @else
        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
          Status: DRAFT
        </span>
      @endif
    </div>
  </div>

  <div class="px-4 py-4 grid grid-cols-1 sm:grid-cols-4 gap-3 text-sm text-gray-700">
    <div>
      <div class="text-gray-500">Mata Pelajaran</div>
      <div class="font-medium">{{ $namaMapel }}</div>
    </div>
    <div>
      <div class="text-gray-500">Kelas / Rombel</div>
      <div class="font-medium">{{ $namaRombel }}</div>
    </div>
    <div>
      <div class="text-gray-500">Tahun Ajaran</div>
      <div class="font-medium">{{ $taAktif->nama_tahun ?? '-' }}</div>
    </div>
    <div>
      <div class="text-gray-500">Semester</div>
      <div class="font-medium">{{ ucfirst($semesterAktif ?? '-') }}</div>
    </div>
  </div>
</div>

<div class="mb-4 flex flex-wrap gap-2">
  @foreach (['LM1', 'LM2', 'LM3', 'LM4'] as $lm)
    <a href="{{ route('guru.penilaian.create', [
        'rombel_id' => $jadwal->rombel_id,
        'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
        'komponen' => $lm,
    ]) }}"
    class="px-4 py-2 rounded-md border text-sm font-medium transition
      {{ $komponen === $lm
          ? 'bg-indigo-600 border-indigo-600 text-white'
          : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }}">
      {{ $lm }}
    </a>
  @endforeach
</div>

<form method="POST" action="{{ route('guru.penilaian.store') }}" class="bg-white rounded-lg shadow border mb-6">
  @csrf

  <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
  <input type="hidden" name="rombel_id" value="{{ $jadwal->rombel_id }}">
  <input type="hidden" name="mata_pelajaran_id" value="{{ $jadwal->mata_pelajaran_id }}">
  <input type="hidden" name="komponen" value="{{ $komponen }}">

  <div class="px-4 py-3 border-b">
    <h2 class="font-semibold text-gray-800">Pengaturan Bobot {{ $komponen }}</h2>
    <p class="text-xs text-gray-500 mt-1">
      Pilih kategori setiap TP, lalu tentukan bobot Praktik dan Teori. Total bobot wajib 100%.
      TP yang bernilai 0 tidak ikut dihitung.
    </p>
  </div>

  <div class="px-4 py-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="rounded-xl border p-4">
      <h3 class="font-semibold text-gray-800 mb-3">Kategori TP</h3>

      <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        @foreach([1, 2, 3, 4] as $tp)
          @php
            $jenisValue = ${"jenisTp{$tp}"};
          @endphp

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              TP{{ $tp }}
            </label>
            <select
              name="jenis_tp{{ $tp }}"
              class="w-full rounded-lg border-gray-300 text-sm"
              {{ $readOnly ? 'disabled' : '' }}
            >
              <option value="praktik" @selected($jenisValue === 'praktik')>Praktik</option>
              <option value="teori" @selected($jenisValue === 'teori')>Teori</option>
            </select>
          </div>
        @endforeach
      </div>
    </div>

    <div class="rounded-xl border p-4">
      <h3 class="font-semibold text-gray-800 mb-3">Bobot Komponen</h3>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Bobot Praktik (%)
          </label>
          <input
            type="number"
            name="bobot_praktik"
            value="{{ $bobotPraktik }}"
            min="0"
            max="100"
            step="0.01"
            class="w-full rounded-lg border-gray-300 text-right text-sm"
            {{ $readOnly ? 'disabled' : '' }}
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Bobot Teori (%)
          </label>
          <input
            type="number"
            name="bobot_teori"
            value="{{ $bobotTeori }}"
            min="0"
            max="100"
            step="0.01"
            class="w-full rounded-lg border-gray-300 text-right text-sm"
            {{ $readOnly ? 'disabled' : '' }}
          >
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Total Bobot
          </label>
          <div class="w-full rounded-lg border bg-gray-50 px-3 py-2 text-right text-sm text-gray-700">
            {{ number_format((float) $bobotPraktik + (float) $bobotTeori, 2) }}%
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="px-4 py-3 border-y bg-gray-50">
    <h2 class="font-semibold text-gray-800">Input Nilai {{ $komponen }}</h2>
    <p class="text-xs text-gray-500 mt-1">
      Nilai default 0. TP yang tidak digunakan boleh dibiarkan 0, dan sistem tetap bisa menyimpan nilai.
    </p>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-indigo-50 text-indigo-700">
        <tr>
          <th class="px-4 py-3 text-left">No</th>
          <th class="px-4 py-3 text-left">Nama Siswa</th>
          <th class="px-4 py-3 text-left">TP1</th>
          <th class="px-4 py-3 text-left">TP2</th>
          <th class="px-4 py-3 text-left">TP3</th>
          <th class="px-4 py-3 text-left">TP4</th>
          <th class="px-4 py-3 text-left">{{ $komponen }}</th>
          <th class="px-4 py-3 text-left">Nilai Akhir</th>
          <th class="px-4 py-3 text-left">Status</th>
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
            $currentAkhir = $row->nilai_akhir ?? null;
            $currentStatus = $row->status ?? null;
          @endphp

          <tr class="hover:bg-gray-50">
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
                  class="w-24 rounded border-gray-300 text-right"
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

            <td class="px-4 py-3 font-semibold text-gray-800">
              {{ $currentAkhir !== null ? number_format($currentAkhir, 2) : '0.00' }}
            </td>

            <td class="px-4 py-3">
              @if($currentStatus === 'tuntas')
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                  Tuntas
                </span>
              @elseif($currentStatus === 'tidak_tuntas' && $currentAkhir !== null)
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
              Tidak ada siswa pada kelas ini.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="p-4 border-t flex items-center justify-between gap-2">
    <a href="{{ route('guru.penilaian.index') }}" class="px-4 py-2 rounded-md border hover:bg-gray-50">
      Kembali
    </a>

    @unless($isFinal)
      <button type="submit" class="px-5 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
        Simpan Nilai
      </button>
    @endunless
  </div>
</form>

<div class="bg-white rounded-lg shadow border mb-6">
  <div class="px-4 py-3 border-b">
    <h2 class="font-semibold text-gray-800">Ringkasan Penilaian</h2>
    <p class="text-xs text-gray-500 mt-1">
      Rekap nilai LM1–LM4 dan nilai akhir semester.
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
            <td class="px-4 py-3 font-semibold">{{ $row?->nilai_akhir !== null ? number_format($row->nilai_akhir, 2) : '0.00' }}</td>

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
            <td colspan="8" class="px-4 py-6 text-center text-gray-500">
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
                class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-50"
                {{ ($progress['missing_any'] ?? 0) > 0 ? 'disabled' : '' }}>
          Finalisasi & Kunci Nilai
        </button>
      </form>
    @endunless
  </div>
</div>

<div id="modalFinalisasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
  <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
    <div class="px-6 pt-6 pb-4">
      <div class="mb-4 flex items-center gap-3">
        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 text-amber-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Finalisasi</h3>
          <p class="text-sm text-gray-500">Tindakan ini akan mengunci data nilai semester ini.</p>
        </div>
      </div>

      <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Finalisasi nilai akan <span class="font-semibold">mengunci seluruh nilai</span> pada semester ini.
        Setelah difinalisasi, data tidak bisa diedit lagi dari menu input nilai.
      </div>
    </div>

    <div class="flex items-center justify-end gap-2 border-t px-6 py-4">
      <button type="button"
              onclick="closeModalFinalisasi()"
              class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
        Batal
      </button>

      <button type="button"
              onclick="submitFinalisasi()"
              class="px-4 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700">
        Ya, Finalisasi
      </button>
    </div>
  </div>
</div>

<script>
  function openModalFinalisasi() {
    const modal = document.getElementById('modalFinalisasi');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }

  function closeModalFinalisasi() {
    const modal = document.getElementById('modalFinalisasi');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  function submitFinalisasi() {
    document.getElementById('form-finalisasi').submit();
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeModalFinalisasi();
    }
  });

  document.getElementById('modalFinalisasi')?.addEventListener('click', function(e) {
    if (e.target.id === 'modalFinalisasi') {
      closeModalFinalisasi();
    }
  });
</script>