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

    $isFinal = ($statusInfo['status'] ?? 'draft') === 'final';

    $prefixMap = [
        'LM1' => 'lm1',
        'LM2' => 'lm2',
        'LM3' => 'lm3',
        'LM4' => 'lm4',
    ];

    $activePrefix = $prefixMap[$komponen] ?? 'lm1';

    $kkmMapel = $kkm
        ?? optional($jadwal->mataPelajaran)->kkm
        ?? optional($jadwal->mapel)->kkm
        ?? '-';
@endphp

<div class="space-y-6">
    {{-- Header ringkas --}}
    <div class="rounded-2xl border bg-white shadow-sm">
        <div class="p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
                        Kelas: <span class="ml-1 font-semibold">{{ $namaRombel }}</span>
                    </span>

                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700">
                        Mapel: <span class="ml-1 font-semibold">{{ $namaMapel }}</span>
                    </span>

                    <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
                        TA: <span class="ml-1 font-semibold">{{ $taAktif->nama_tahun ?? '-' }}</span>
                    </span>

                    <span class="inline-flex items-center rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
                        Semester: <span class="ml-1 font-semibold">{{ ucfirst($semesterAktif ?? '-') }}</span>
                    </span>

                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">
                        KKM: <span class="ml-1 font-semibold">{{ $kkmMapel }}</span>
                    </span>
                </div>

                <div class="text-sm">
                    @if($isFinal)
                        <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-sm font-semibold text-green-700">
                            Status: FINAL
                        </span>
                        @if(!empty($statusInfo['finalized_at']))
                            <div class="mt-2 text-xs text-gray-500">
                                Difinalisasi:
                                {{ \Illuminate\Support\Carbon::parse($statusInfo['finalized_at'])->translatedFormat('d M Y H:i') }}
                            </div>
                        @endif
                    @else
                        <span class="inline-flex rounded-full border border-yellow-200 bg-yellow-50 px-3 py-1.5 text-sm font-semibold text-yellow-700">
                            Status: DRAFT
                        </span>
                    @endif
                </div>
            </div>

            {{-- Tab LM --}}
            <div class="mt-5 flex flex-wrap gap-3">
                @foreach (['LM1', 'LM2', 'LM3', 'LM4'] as $lm)
                    <a href="{{ route('guru.penilaian.create', [
                            'rombel_id' => $jadwal->rombel_id,
                            'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                            'komponen' => $lm,
                        ]) }}"
                       class="inline-flex items-center rounded-xl border px-5 py-2.5 text-sm font-semibold transition
                       {{ $komponen === $lm
                            ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm'
                            : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                        Isi {{ $lm }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Error --}}
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="mb-1 font-semibold">Terjadi kesalahan:</div>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Success --}}
    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form input nilai --}}
    <form method="POST" action="{{ route('guru.penilaian.store') }}" class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        @csrf

        <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
        <input type="hidden" name="rombel_id" value="{{ $jadwal->rombel_id }}">
        <input type="hidden" name="mata_pelajaran_id" value="{{ $jadwal->mata_pelajaran_id }}">
        <input type="hidden" name="komponen" value="{{ $komponen }}">

        {{-- Bobot --}}
        <div class="border-b">
            <div class="px-5 py-4">
                <h2 class="text-2xl font-semibold text-gray-900">Pengaturan Bobot {{ $komponen }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Pilih kategori setiap TP, lalu tentukan bobot Praktik dan Teori. Total bobot wajib 100%.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 px-5 pb-5 xl:grid-cols-2">
                {{-- Kategori TP --}}
                <div class="rounded-2xl border p-5">
                    <h3 class="mb-5 text-xl font-semibold text-gray-800">Kategori TP</h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @for($tp = 1; $tp <= 4; $tp++)
                            @php
                                $kategoriKey = strtolower($komponen) . "_tp{$tp}_kategori";
                                $selectedKategori = old(
                                    "kategori_tp.tp{$tp}",
                                    $bobot[$kategoriKey] ?? ($tp <= 2 ? 'praktik' : 'teori')
                                );
                            @endphp

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">TP{{ $tp }}</label>
                                <select
                                    name="kategori_tp[tp{{ $tp }}]"
                                    class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    {{ $isFinal ? 'disabled' : '' }}
                                >
                                    <option value="praktik" {{ $selectedKategori === 'praktik' ? 'selected' : '' }}>Praktik</option>
                                    <option value="teori" {{ $selectedKategori === 'teori' ? 'selected' : '' }}>Teori</option>
                                </select>
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- Bobot Komponen --}}
                <div class="rounded-2xl border p-5">
                    <h3 class="mb-5 text-xl font-semibold text-gray-800">Bobot Komponen</h3>

                    @php
                        $prefix = strtolower($komponen);

                        $bobotPraktik = old('bobot_praktik', $bobot["{$prefix}_bobot_praktik"] ?? '');
                        $bobotTeori   = old('bobot_teori', $bobot["{$prefix}_bobot_teori"] ?? '');
                        $totalBobot   = (is_numeric($bobotPraktik) ? (float) $bobotPraktik : 0)
                                      + (is_numeric($bobotTeori) ? (float) $bobotTeori : 0);
                    @endphp

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Bobot Praktik (%)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                name="bobot_praktik"
                                value="{{ $bobotPraktik }}"
                                class="w-full rounded-xl border-gray-300 text-right text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                {{ $isFinal ? 'disabled' : '' }}
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Bobot Teori (%)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                name="bobot_teori"
                                value="{{ $bobotTeori }}"
                                class="w-full rounded-xl border-gray-300 text-right text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                {{ $isFinal ? 'disabled' : '' }}
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Total Bobot</label>
                            <div class="flex h-[42px] items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-3 text-base font-semibold text-emerald-700">
                                {{ number_format($totalBobot, 0) }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel input --}}
        <div class="px-5 py-4">
            <h2 class="text-2xl font-semibold text-gray-900">Input Nilai {{ $komponen }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                Nilai default 0. TP yang tidak digunakan boleh dibiarkan 0. Nilai {{ $komponen }} akan dihitung dari TP yang benar-benar diisi.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-indigo-50 text-indigo-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">No</th>
                        <th class="px-4 py-3 text-left font-semibold">Nama Siswa</th>
                        <th class="px-4 py-3 text-center font-semibold">
                            TP1
                            <div class="mt-1 text-xs font-normal text-indigo-500">
                                {{ ucfirst(old('kategori_tp.tp1', $bobot[strtolower($komponen) . '_tp1_kategori'] ?? 'praktik')) }}
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold">
                            TP2
                            <div class="mt-1 text-xs font-normal text-indigo-500">
                                {{ ucfirst(old('kategori_tp.tp2', $bobot[strtolower($komponen) . '_tp2_kategori'] ?? 'praktik')) }}
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold">
                            TP3
                            <div class="mt-1 text-xs font-normal text-indigo-500">
                                {{ ucfirst(old('kategori_tp.tp3', $bobot[strtolower($komponen) . '_tp3_kategori'] ?? 'teori')) }}
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold">
                            TP4
                            <div class="mt-1 text-xs font-normal text-indigo-500">
                                {{ ucfirst(old('kategori_tp.tp4', $bobot[strtolower($komponen) . '_tp4_kategori'] ?? 'teori')) }}
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold">
                            {{ $komponen }}
                            <div class="mt-1 text-xs font-normal text-indigo-500">Nilai LM</div>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($siswa as $i => $s)
                        @php
                            $row = $nilai[$s->id] ?? null;

                            $tp1Val = old("nilai.{$s->id}.tp1", data_get($row, "{$activePrefix}_tp1", 0));
                            $tp2Val = old("nilai.{$s->id}.tp2", data_get($row, "{$activePrefix}_tp2", 0));
                            $tp3Val = old("nilai.{$s->id}.tp3", data_get($row, "{$activePrefix}_tp3", 0));
                            $tp4Val = old("nilai.{$s->id}.tp4", data_get($row, "{$activePrefix}_tp4", 0));

                            $currentLm = data_get($row, "{$activePrefix}_nilai");
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">{{ $i + 1 }}</td>

                            <td class="px-4 py-4">
                                <div class="font-semibold text-gray-900">{{ $s->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $s->nis ?? $s->nisn ?? '' }}</div>
                            </td>

                            @for($tp = 1; $tp <= 4; $tp++)
                                @php $fieldVal = ${"tp{$tp}Val"}; @endphp
                                <td class="px-4 py-4 text-center">
                                    <input
                                        type="number"
                                        name="nilai[{{ $s->id }}][tp{{ $tp }}]"
                                        class="w-28 rounded-xl border-gray-300 text-center text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        value="{{ $fieldVal }}"
                                        {{ $isFinal ? 'disabled' : '' }}
                                    >
                                </td>
                            @endfor

                            <td class="px-4 py-4 text-center font-semibold text-indigo-700">
                                {{ $currentLm !== null ? number_format($currentLm, 2, ',', '.') : '-' }}
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

        {{-- Action bawah --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-t px-5 py-4">
            <a href="{{ route('guru.penilaian.index') }}"
               class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Kembali
            </a>

            <div class="flex flex-wrap items-center justify-end gap-3">
                @if(!empty($nextKomponen))
                    <span class="inline-flex items-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-medium text-indigo-700">
                        Isi LM Belum Lengkap: {{ $nextKomponen }}
                    </span>
                @endif

                @unless($isFinal)
                    <button type="submit"
                            class="inline-flex items-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Simpan Nilai
                    </button>
                @endunless
            </div>
        </div>
    </form>

    {{-- Ringkasan penilaian --}}
    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="px-5 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-900">Ringkasan Penilaian</h2>
            <p class="mt-1 text-sm text-gray-500">
                Rekap LM1–LM4 dan nilai akhir. Status tuntas/tidak tuntas dihitung berdasarkan KKM mata pelajaran.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">No</th>
                        <th class="px-4 py-3 text-left font-semibold">Nama Siswa</th>
                        <th class="px-4 py-3 text-left font-semibold">LM1</th>
                        <th class="px-4 py-3 text-left font-semibold">LM2</th>
                        <th class="px-4 py-3 text-left font-semibold">LM3</th>
                        <th class="px-4 py-3 text-left font-semibold">LM4</th>
                        <th class="px-4 py-3 text-left font-semibold">KKM</th>
                        <th class="px-4 py-3 text-left font-semibold">Nilai Akhir</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse($siswa as $i => $s)
                        @php $row = $nilai[$s->id] ?? null; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $s->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $s->nis ?? $s->nisn ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $row?->lm1_nilai !== null ? number_format($row->lm1_nilai, 2, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3">{{ $row?->lm2_nilai !== null ? number_format($row->lm2_nilai, 2, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3">{{ $row?->lm3_nilai !== null ? number_format($row->lm3_nilai, 2, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3">{{ $row?->lm4_nilai !== null ? number_format($row->lm4_nilai, 2, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3">{{ $kkmMapel }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $row?->nilai_akhir !== null ? number_format($row->nilai_akhir, 2, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3">
                                @if(($row?->status ?? null) === 'tuntas')
                                    <span class="inline-flex items-center rounded-full border border-green-200 bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700">
                                        Tuntas
                                    </span>
                                @elseif(($row?->status ?? null) === 'tidak_tuntas')
                                    <span class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700">
                                        Tidak Tuntas
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
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

        <div class="flex flex-wrap items-center justify-between gap-3 border-t px-5 py-4">
            <div class="text-sm text-gray-700">
                Kelengkapan:
                LM1 <span class="{{ $progress['missing']['LM1'] ? 'text-red-600' : 'text-green-600' }}">
                    {{ $progress['total'] - $progress['missing']['LM1'] }}/{{ $progress['total'] }}
                </span>,
                LM2 <span class="{{ $progress['missing']['LM2'] ? 'text-red-600' : 'text-green-600' }}">
                    {{ $progress['total'] - $progress['missing']['LM2'] }}/{{ $progress['total'] }}
                </span>,
                LM3 <span class="{{ $progress['missing']['LM3'] ? 'text-red-600' : 'text-green-600' }}">
                    {{ $progress['total'] - $progress['missing']['LM3'] }}/{{ $progress['total'] }}
                </span>,
                LM4 <span class="{{ $progress['missing']['LM4'] ? 'text-red-600' : 'text-green-600' }}">
                    {{ $progress['total'] - $progress['missing']['LM4'] }}/{{ $progress['total'] }}
                </span>
            </div>

            @unless($isFinal)
                <form id="form-finalisasi" method="POST" action="{{ route('guru.penilaian.finalize') }}">
                    @csrf
                    <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">

                    <button type="button"
                            onclick="openModalFinalisasi()"
                            class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                            {{ $progress['missing_any'] > 0 ? 'disabled' : '' }}>
                        Finalisasi & Kunci Nilai
                    </button>
                </form>
            @endunless
        </div>
    </div>
</div>

{{-- Modal Finalisasi --}}
<div id="modalFinalisasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
        <div class="px-6 pb-4 pt-6">
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
                Finalisasi nilai akan <span class="font-semibold">mengunci seluruh nilai</span>
                pada semester ini. Setelah difinalisasi, data tidak bisa diedit lagi dari menu input nilai.
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t px-6 py-4">
            <button type="button"
                    onclick="closeModalFinalisasi()"
                    class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50">
                Batal
            </button>

            <button type="button"
                    onclick="submitFinalisasi()"
                    class="rounded-xl bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
                Ya, Finalisasi
            </button>
        </div>
    </div>
</div>

<script>
    function openModalFinalisasi() {
        const modal = document.getElementById('modalFinalisasi');
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeModalFinalisasi() {
        const modal = document.getElementById('modalFinalisasi');
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    function submitFinalisasi() {
        document.getElementById('form-finalisasi')?.submit();
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModalFinalisasi();
        }
    });

    document.getElementById('modalFinalisasi')?.addEventListener('click', function (e) {
        if (e.target.id === 'modalFinalisasi') {
            closeModalFinalisasi();
        }
    });
</script>