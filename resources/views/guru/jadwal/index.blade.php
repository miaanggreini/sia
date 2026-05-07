@extends('layouts.guru')

@section('content')
  @php
    $items = $items ?? collect();
    $dayOrder = ['Semua', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
  @endphp

  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-800 sm:text-3xl">
      Jadwal Mengajar
    </h1>
    <p class="mt-1 text-sm text-gray-500 sm:text-base">
      Ringkasan jadwal mengajar Anda
    </p>
  </div>

  @if($items->isEmpty())
    <div class="rounded-2xl border bg-white p-8 text-center text-gray-500 shadow">
      Belum ada jadwal mengajar yang tercatat.
    </div>
  @else

    {{-- Filter hari --}}
    <div class="mb-5 flex flex-wrap gap-2" id="filterHariWrapper">
      @foreach($dayOrder as $hari)
        <button
          type="button"
          class="filter-hari-btn inline-flex items-center justify-center rounded-full border px-5 py-2 text-sm font-semibold transition duration-150 cursor-pointer select-none
                 {{ $hari === 'Semua'
                    ? 'bg-blue-600 text-white border-blue-600 shadow-sm hover:bg-blue-700 hover:text-white'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-400' }}"
          data-hari="{{ $hari }}"
        >
          {{ $hari }}
        </button>
      @endforeach
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-[760px] w-full text-sm">
          <thead class="border-b border-slate-200 bg-slate-50">
            <tr class="text-left text-slate-700">
              <th class="px-5 py-4 font-semibold w-16">No</th>
              <th class="px-5 py-4 font-semibold">Kelas</th>
              <th class="px-5 py-4 font-semibold">Mata Pelajaran</th>
              <th class="px-5 py-4 font-semibold">Hari</th>
              <th class="px-5 py-4 font-semibold">Jam Ke</th>
              <th class="px-5 py-4 font-semibold">Waktu</th>
              <th class="px-5 py-4 font-semibold text-center">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100">
            @foreach($items->values() as $index => $row)
              @php
                $hari = ucfirst(strtolower($row->hari ?? '-'));
                $labelMulai = $row->jam_mulai ? substr($row->jam_mulai, 0, 5) : '--:--';
                $labelSelesai = $row->jam_selesai ? substr($row->jam_selesai, 0, 5) : '--:--';
                $waktuLabel = $labelMulai . ' - ' . $labelSelesai;
              @endphp

              <tr class="jadwal-row transition hover:bg-slate-50/70" data-hari="{{ $hari }}">
                <td class="px-5 py-4 align-middle text-slate-700">
                  {{ $index + 1 }}
                </td>

                <td class="px-5 py-4 align-middle font-medium text-slate-700">
                  {{ $row->rombel->nama_rombel ?? '-' }}
                </td>

                <td class="px-5 py-4 align-middle font-medium text-slate-900">
                  {{ $row->mapel->nama_mapel ?? 'Mata Pelajaran' }}
                </td>

                <td class="px-5 py-4 align-middle text-slate-700">
                  <span class="inline-flex rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                    {{ $hari }}
                  </span>
                </td>

                <td class="px-5 py-4 align-middle text-slate-700">
                  {{ $row->slot_kode ?? '-' }}
                </td>

                <td class="px-5 py-4 align-middle font-medium text-slate-700">
                  {{ $waktuLabel }}
                </td>

                <td class="px-5 py-4 align-middle text-center">
                  <a href="{{ route('guru.jadwal.siswa', $row) }}"
                     class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3Zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3Zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h7v-2.5c0-2.33-4.67-3.5-7-3.5Z"/>
                    </svg>
                    Lihat Siswa
                  </a>
                </td>
              </tr>
            @endforeach

            <tr id="emptyFilteredRow" class="hidden">
              <td colspan="7" class="px-5 py-8 text-center text-gray-500">
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

    if (!buttons.length || !rows.length) return;

    const activeClasses = [
      'bg-blue-600',
      'text-white',
      'border-blue-600',
      'shadow-sm',
      'hover:bg-blue-700',
      'hover:text-white'
    ];

    const inactiveClasses = [
      'bg-white',
      'text-gray-700',
      'border-gray-300',
      'hover:bg-blue-50',
      'hover:text-blue-700',
      'hover:border-blue-400'
    ];

    function setButtonState(button, isActive) {
      button.classList.remove(...activeClasses, ...inactiveClasses);

      if (isActive) {
        button.classList.add(...activeClasses);
      } else {
        button.classList.add(...inactiveClasses);
      }
    }

    function applyFilter(selectedDay) {
      let visibleCount = 0;

      rows.forEach((row) => {
        const rowDay = row.dataset.hari || '';
        const shouldShow = selectedDay === 'Semua' || rowDay === selectedDay;

        row.classList.toggle('hidden', !shouldShow);

        if (shouldShow) visibleCount++;
      });

      if (emptyRow) {
        emptyRow.classList.toggle('hidden', visibleCount !== 0);
      }

      buttons.forEach((button) => {
        const isActive = button.dataset.hari === selectedDay;
        setButtonState(button, isActive);
      });
    }

    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        applyFilter(button.dataset.hari);
      });
    });

    applyFilter('Semua');
  })();
</script>
@endpush