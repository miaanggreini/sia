{{-- resources/views/siswa/jadwal/index.blade.php --}}
@extends('layouts.siswa')

@section('content')
@php
    use Illuminate\Support\Collection;
    use Carbon\Carbon;

    /*
    |--------------------------------------------------------------------------
    | Normalisasi data jadwal
    |--------------------------------------------------------------------------
    | Controller bisa mengirim $jadwal dalam bentuk:
    | - collection datar
    | - grouped collection per hari
    |
    | Di sini diratakan supaya mudah ditampilkan dalam tabel.
    */
    $items = collect($jadwal ?? []);

    if ($items->first() instanceof Collection) {
        $items = $items->flatten(1);
    }

    /*
    |--------------------------------------------------------------------------
    | Hari sekolah
    |--------------------------------------------------------------------------
    | Sabtu dimasukkan karena di dashboard siswa bisa menampilkan hari Sabtu.
    | Kalau sekolah tidak memakai Sabtu, nanti otomatis kosong saja.
    */
    $validDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    $items = $items
        ->filter(fn ($j) => in_array((string) data_get($j, 'hari', ''), $validDays, true))
        ->values();

    $dayOrder = ['Semua', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    /*
    |--------------------------------------------------------------------------
    | Rombel dan tahun ajaran
    |--------------------------------------------------------------------------
    | Jangan mengambil rombel dari jadwal pertama, karena kalau jadwal kosong
    | rombel akan ikut kosong. Rombel harus diambil dari $rombelAktif.
    */
    $rombelName = data_get($rombelAktif ?? null, 'nama_rombel')
        ?? data_get($rombelAktif ?? null, 'nama')
        ?? data_get($rombelAktif ?? null, 'nama_kelas')
        ?? '—';

    $tahunAjaranLabel = data_get($tahunAjaranAktif ?? null, 'tahun_ajaran')
        ?? data_get($tahunAjaranAktif ?? null, 'nama_tahun_ajaran')
        ?? data_get($tahunAjaranAktif ?? null, 'nama_tahun')
        ?? data_get($rombelAktif ?? null, 'tahunAjaran.tahun_ajaran')
        ?? data_get($rombelAktif ?? null, 'tahunAjaran.nama_tahun_ajaran')
        ?? data_get($rombelAktif ?? null, 'tahunAjaran.nama_tahun')
        ?? '—';

    $fmtHm = function ($t) {
        if (!$t) {
            return '--:--';
        }

        try {
            return Carbon::parse($t)->format('H:i');
        } catch (\Throwable $e) {
            return substr((string) $t, 0, 5);
        }
    };
@endphp

<div class="mb-6">
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-900 tracking-tight">
        Jadwal Pelajaran
    </h1>
    <p class="text-sm text-gray-500">
        Ringkasan jadwal pelajaran Anda
    </p>

    <div class="mt-4 flex flex-wrap gap-2">
        <span class="inline-flex items-center rounded-full bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 border border-indigo-100">
            Rombel: {{ $rombelName }}
        </span>

        <span class="inline-flex items-center rounded-full bg-cyan-50 px-4 py-2 text-sm font-medium text-cyan-700 border border-cyan-100">
            TA: {{ $tahunAjaranLabel }}
        </span>
    </div>
</div>

@if(!$rombelAktif)
    <div class="bg-white p-8 rounded-2xl shadow border text-center text-gray-500">
        Siswa belum memiliki rombel aktif pada tahun ajaran ini.
    </div>
@elseif($items->isEmpty())
    <div class="bg-white p-8 rounded-2xl shadow border text-center text-gray-500">
        Belum ada jadwal pelajaran untuk rombel
        <span class="font-semibold text-gray-700">{{ $rombelName }}</span>
        pada tahun ajaran
        <span class="font-semibold text-gray-700">{{ $tahunAjaranLabel }}</span>.
    </div>
@else
    {{-- Filter hari --}}
    <div class="mb-5 flex flex-wrap gap-2" id="filterHariWrapper">
        @foreach($dayOrder as $hari)
            <button
                type="button"
                class="filter-hari-btn inline-flex items-center rounded-full px-5 py-2 text-sm font-medium border transition cursor-pointer select-none
                    {{ $hari === 'Semua'
                        ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50 hover:border-blue-400 hover:text-blue-700' }}"
                data-hari="{{ $hari }}"
            >
                {{ $hari }}
            </button>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left text-slate-700">
                        <th class="px-5 py-4 font-semibold w-16">No</th>
                        <th class="px-5 py-4 font-semibold">Mata Pelajaran</th>
                        <th class="px-5 py-4 font-semibold">Guru</th>
                        <th class="px-5 py-4 font-semibold">Hari</th>
                        <th class="px-5 py-4 font-semibold">Jam Ke</th>
                        <th class="px-5 py-4 font-semibold">Waktu</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach($items->values() as $index => $row)
                        @php
                            $hari = ucfirst(strtolower(data_get($row, 'hari', '-')));

                            $jamMulai = $fmtHm(data_get($row, 'jam_mulai'));
                            $jamSelesai = $fmtHm(data_get($row, 'jam_selesai'));
                            $waktuLabel = $jamMulai . ' - ' . $jamSelesai;

                            $namaMapel = data_get($row, 'mataPelajaran.nama')
                                ?? data_get($row, 'mataPelajaran.nama_mapel')
                                ?? data_get($row, 'mapel.nama_mapel')
                                ?? data_get($row, 'mapel.nama')
                                ?? '-';

                            $namaGuru = data_get($row, 'guru.nama')
                                ?? data_get($row, 'guru.nama_guru')
                                ?? '-';

                            $jamKe = data_get($row, 'slot_kode')
                                ?? data_get($row, 'jam_ke')
                                ?? '-';
                        @endphp

                        <tr class="hover:bg-slate-50/70 transition jadwal-row" data-hari="{{ $hari }}">
                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $index + 1 }}
                            </td>

                            <td class="px-5 py-4 align-middle font-medium text-slate-900">
                                {{ $namaMapel }}
                            </td>

                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $namaGuru }}
                            </td>

                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $hari }}
                            </td>

                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $jamKe }}
                            </td>

                            <td class="px-5 py-4 align-middle text-slate-700 font-medium">
                                {{ $waktuLabel }}
                            </td>
                        </tr>
                    @endforeach

                    <tr id="emptyFilteredRow" class="hidden">
                        <td colspan="6" class="px-5 py-8 text-center text-gray-500">
                            Tidak ada jadwal untuk hari yang dipilih.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    (() => {
        const buttons = document.querySelectorAll('.filter-hari-btn');
        const rows = document.querySelectorAll('.jadwal-row');
        const emptyRow = document.getElementById('emptyFilteredRow');

        if (!buttons.length) return;

        function applyFilter(selectedDay) {
            let visibleCount = 0;

            rows.forEach((row) => {
                const rowDay = row.dataset.hari || '';
                const shouldShow = selectedDay === 'Semua' || rowDay === selectedDay;

                row.classList.toggle('hidden', !shouldShow);

                if (shouldShow) {
                    visibleCount++;
                }
            });

            if (emptyRow) {
                emptyRow.classList.toggle('hidden', visibleCount !== 0);
            }

            buttons.forEach((btn) => {
                const isActive = btn.dataset.hari === selectedDay;

                btn.classList.remove(
                    'bg-blue-600',
                    'text-white',
                    'border-blue-600',
                    'shadow-sm',
                    'bg-white',
                    'text-gray-700',
                    'border-gray-300',
                    'hover:bg-blue-50',
                    'hover:border-blue-400',
                    'hover:text-blue-700',
                    'hover:bg-blue-700'
                );

                if (isActive) {
                    btn.classList.add(
                        'bg-blue-600',
                        'text-white',
                        'border-blue-600',
                        'shadow-sm',
                        'hover:bg-blue-700'
                    );
                } else {
                    btn.classList.add(
                        'bg-white',
                        'text-gray-700',
                        'border-gray-300',
                        'hover:bg-blue-50',
                        'hover:border-blue-400',
                        'hover:text-blue-700'
                    );
                }
            });
        }

        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                applyFilter(btn.dataset.hari || 'Semua');
            });
        });

        applyFilter('Semua');
    })();
</script>
@endpush