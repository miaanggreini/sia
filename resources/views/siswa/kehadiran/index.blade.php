@extends('layouts.siswa')
@section('title', 'Rekap Kehadiran')

@section('content')
<div class="space-y-5">

  {{-- HEADER --}}
  <div class="rounded-2xl border bg-white p-6 shadow-sm">
    <h1 class="text-2xl font-semibold text-gray-900">Rekap Kehadiran</h1>
    <p class="mt-1 text-sm text-gray-500">
      Menampilkan rekap kehadiran berdasarkan tahun ajaran, semester, dan kelas.
    </p>
  </div>

  {{-- FILTER --}}
  <div class="rounded-2xl border bg-white p-5 shadow-sm">
    <form method="GET" id="filterKehadiranForm"
          class="grid grid-cols-1 gap-4 md:grid-cols-3 md:items-end">

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">
          Tahun Ajaran
        </label>
        <select name="tahun_ajaran_id"
                onchange="document.getElementById('filterKehadiranForm').submit()"
                class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Semua Tahun Ajaran</option>
          @foreach($taOptions as $id => $nama)
            <option value="{{ $id }}" @selected((string) $taId === (string) $id)>
              {{ $nama }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">
          Semester
        </label>
        <select name="semester"
                onchange="document.getElementById('filterKehadiranForm').submit()"
                class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          <option value="">Semua Semester</option>
          <option value="Ganjil" @selected(($semester ?? '') === 'Ganjil')>Ganjil</option>
          <option value="Genap" @selected(($semester ?? '') === 'Genap')>Genap</option>
        </select>
      </div>

      <div>
        <a href="{{ route('siswa.kehadiran.index') }}"
           class="inline-flex w-full items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
          Reset Filter
        </a>
      </div>

    </form>
  </div>

  @if($items->isEmpty())
    <div class="rounded-2xl border bg-white p-8 text-center text-gray-500 shadow-sm">
      Belum ada data kehadiran untuk tahun ajaran atau semester yang dipilih.
    </div>
  @else
    <div class="space-y-5">
      @foreach($groups as $groupKey => $rows)
        @php
          $parts = explode('|', $groupKey);
          $tahunAjaran = $parts[0] ?? '-';
          $semesterGroup = $parts[1] ?? '-';
          $rombel = $parts[2] ?? '-';

          $totalMapel = $rows->count();
          $totalPertemuan = $rows->sum('total');
          $totalHadir = $rows->sum('hadir');
          $totalIzin = $rows->sum('izin');
          $totalSakit = $rows->sum('sakit');
          $totalAlfa = $rows->sum('alfa');
          $totalBelum = $rows->sum('belum');

          $persenGroup = $totalPertemuan > 0
              ? round(($totalHadir / $totalPertemuan) * 100, 1)
              : 0;

          $accordionId = 'kehadiran-accordion-' . $loop->index;
        @endphp

        <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">

    {{-- ACCORDION HEADER COMPACT --}}
<button type="button"
        onclick="toggleKehadiranAccordion('{{ $accordionId }}')"
        class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left hover:bg-gray-50">
  <div>
    <h2 class="text-base font-semibold text-gray-900">
      Tahun Ajaran {{ $tahunAjaran }}
    </h2>

    <p class="mt-1 text-sm text-gray-500">
      Kelas {{ $rombel }} · Semester {{ $semesterGroup }} · {{ $totalMapel }} mapel
    </p>
  </div>

  <div class="flex items-center gap-4">
    <div class="hidden text-right md:block">
      <p class="text-xs text-gray-500">Rata-rata Kehadiran</p>
      <p class="text-sm font-semibold text-indigo-600">
        {{ number_format($persenGroup, 1) }}%
      </p>
    </div>

    <svg id="{{ $accordionId }}-icon"
         xmlns="http://www.w3.org/2000/svg"
         class="h-5 w-5 text-gray-500 transition-transform duration-200"
         fill="none"
         viewBox="0 0 24 24"
         stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 9l-7 7-7-7" />
    </svg>
  </div>
</button>

          {{-- ACCORDION CONTENT --}}
          <div id="{{ $accordionId }}" class="{{ $loop->first ? '' : 'hidden' }}">
{{-- SUMMARY COMPACT --}}
<div class="border-y bg-gray-50 px-5 py-3">
  <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">

    {{-- 3 INFO KECIL --}}
    <div class="grid grid-cols-3 gap-2 md:w-[460px]">
      <div class="rounded-xl border bg-white px-3 py-2">
        <p class="text-[11px] text-gray-500">Total</p>
        <p class="mt-0.5 text-sm font-bold text-gray-900">
          {{ $totalPertemuan }}
        </p>
      </div>

      <div class="rounded-xl border bg-white px-3 py-2">
        <p class="text-[11px] text-gray-500">Hadir</p>
        <p class="mt-0.5 text-sm font-bold text-emerald-600">
          {{ $totalHadir }}
        </p>
      </div>

      <div class="rounded-xl border bg-white px-3 py-2">
        <p class="text-[11px] text-gray-500">Tidak Hadir</p>
        <p class="mt-0.5 text-sm font-bold text-rose-600">
          {{ $totalIzin + $totalSakit + $totalAlfa + $totalBelum }}
        </p>
      </div>
    </div>

    {{-- PROGRESS COMPACT --}}
    <div class="w-full md:w-[360px]">
      <div class="mb-1 flex items-center justify-between text-xs">
        <span class="font-medium text-gray-600">Kehadiran semester</span>
        <span class="font-semibold text-gray-900">
          {{ number_format($persenGroup, 1) }}%
        </span>
      </div>

      <div class="h-2 overflow-hidden rounded-full bg-gray-200">
        <div class="h-full rounded-full bg-emerald-500"
             style="width: {{ min($persenGroup, 100) }}%"></div>
      </div>

      <p class="mt-1 text-[11px] text-gray-500">
        {{ $totalHadir }}/{{ $totalPertemuan }} pertemuan hadir
      </p>
    </div>

  </div>
</div>

            {{-- TABLE --}}
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-700">
                  <tr>
                    <th class="px-5 py-3 text-left">No</th>
                    <th class="px-5 py-3 text-left">Mata Pelajaran</th>
                    <th class="px-5 py-3 text-center">Hadir</th>
                    <th class="px-5 py-3 text-center">Izin</th>
                    <th class="px-5 py-3 text-center">Sakit</th>
                    <th class="px-5 py-3 text-center">Alfa</th>
                    <th class="px-5 py-3 text-center">Belum</th>
                    <th class="px-5 py-3 text-center">Total</th>
                    <th class="px-5 py-3 text-center">Persentase</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                  </tr>
                </thead>

                <tbody class="divide-y">
                  @foreach($rows as $it)
                    <tr class="hover:bg-gray-50">
                      <td class="px-5 py-4 text-gray-600">
                        {{ $loop->iteration }}
                      </td>

                      <td class="px-5 py-4">
                        <div class="font-semibold text-gray-900">
                          {{ $it->mapel }}
                        </div>
                      </td>

                      <td class="px-5 py-4 text-center">
                        <span class="inline-flex min-w-10 justify-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">
                          {{ $it->hadir }}
                        </span>
                      </td>

                      <td class="px-5 py-4 text-center">
                        <span class="inline-flex min-w-10 justify-center rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">
                          {{ $it->izin }}
                        </span>
                      </td>

                      <td class="px-5 py-4 text-center">
                        <span class="inline-flex min-w-10 justify-center rounded-full bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-700">
                          {{ $it->sakit }}
                        </span>
                      </td>

                      <td class="px-5 py-4 text-center">
                        <span class="inline-flex min-w-10 justify-center rounded-full bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700">
                          {{ $it->alfa }}
                        </span>
                      </td>

                      <td class="px-5 py-4 text-center">
                        <span class="inline-flex min-w-10 justify-center rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600">
                          {{ $it->belum }}
                        </span>
                      </td>

                      <td class="px-5 py-4 text-center font-medium text-gray-700">
                        {{ $it->total }}
                      </td>

                      <td class="px-5 py-4 text-center">
                        <div class="flex min-w-[120px] flex-col items-center gap-1">
                          <span class="text-sm font-semibold text-gray-900">
                            {{ number_format($it->persen, 1) }}%
                          </span>

                          <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                            <div class="h-full rounded-full bg-emerald-500"
                                 style="width: {{ min($it->persen, 100) }}%"></div>
                          </div>

                          <span class="text-[11px] text-gray-500">
                            {{ $it->hadir }}/{{ $it->total }} pertemuan
                          </span>
                        </div>
                      </td>

                      <td class="px-5 py-4 text-right">
                        <a href="{{ route('siswa.kehadiran.show', [$it->rombel_id, $it->mapel_id]) }}"
                           class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                          Detail
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

        </div>
      @endforeach
    </div>
  @endif

</div>

<script>
  function toggleKehadiranAccordion(id) {
    const content = document.getElementById(id);
    const icon = document.getElementById(id + '-icon');

    if (!content) return;

    content.classList.toggle('hidden');

    if (icon) {
      icon.classList.toggle('rotate-180');
    }
  }
</script>
@endsection