@extends('layouts.admin')

@section('content')
  @php
    $namaRombel = $jadwal->rombel->nama_rombel ?? $jadwal->rombel->nama ?? '-';
    $namaMapel  = $jadwal->mataPelajaran->nama_mapel ?? $jadwal->mataPelajaran->nama ?? '-';
    $namaGuru   = $jadwal->guru->nama ?? '-';

    $tahunAjaranLabel = $taDipilih->nama_tahun ?? '-';
    $semesterLabel = $semesterDipilih ?? ($taDipilih->semester ?? '-');

    $taLabel = $tahunAjaranLabel . ' / ' . $semesterLabel;

    $num = function ($v) {
        return $v !== null ? number_format((float) $v, 2) : '—';
    };
  @endphp

  <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">
        Detail Monitoring Penilaian
      </h1>
      <p class="mt-1 text-sm text-gray-500">
        Pantau hasil penilaian siswa per mata pelajaran dan semester.
      </p>
    </div>

    <a href="{{ route('admin.penilaian.index', ['tahun_ajaran_id' => $tahunAjaranRombelId]) }}"
       class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
      Kembali
    </a>
  </div>

  {{-- INFORMASI PENILAIAN --}}
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
    <div class="px-5 py-4 border-b border-gray-200">
      <h2 class="text-base font-semibold text-gray-900">
        Informasi Penilaian
      </h2>
    </div>

    <div class="px-5 py-4 space-y-4">
      <p class="text-sm text-gray-700 leading-7">
        <span class="font-semibold">Kelas:</span> {{ $namaRombel }}
        <span class="mx-2 text-gray-300">|</span>

        <span class="font-semibold">Mata Pelajaran:</span> {{ $namaMapel }}
        <span class="mx-2 text-gray-300">|</span>

        <span class="font-semibold">Guru:</span> {{ $namaGuru }}
        <span class="mx-2 text-gray-300">|</span>

        <span class="font-semibold">Tahun Ajaran / Semester:</span> {{ $taLabel }}
      </p>

      {{-- FILTER SEMESTER --}}
      <form method="GET"
            action="{{ route('admin.penilaian.show', ['jadwal' => $jadwal->id]) }}"
            class="grid grid-cols-1 gap-4 rounded-xl border bg-gray-50 p-4 md:grid-cols-12 md:items-end">

        <div class="md:col-span-4">
          <label class="mb-1 block text-sm font-semibold text-gray-700">
            Tahun Ajaran
          </label>

          <input type="text"
                 value="{{ $tahunAjaranLabel }}"
                 readonly
                 class="w-full rounded-xl border-gray-300 bg-white text-sm text-gray-700">
        </div>

        <div class="md:col-span-3">
          <label class="mb-1 block text-sm font-semibold text-gray-700">
            Semester
          </label>

          <select name="semester"
                  class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="Ganjil" @selected($semesterLabel === 'Ganjil')>
              Ganjil
            </option>
            <option value="Genap" @selected($semesterLabel === 'Genap')>
              Genap
            </option>
          </select>
        </div>

        <div class="md:col-span-3">
        </div>

        <div class="flex gap-2 md:col-span-2 md:justify-end">
          <button type="submit"
                  class="inline-flex min-w-[110px] items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
            Terapkan
          </button>

          <a href="{{ route('admin.penilaian.show', ['jadwal' => $jadwal->id]) }}"
             class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- TABEL NILAI RINGKAS --}}
  <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200">
      <h2 class="text-base font-semibold text-gray-900">
        Daftar Nilai Siswa
      </h2>
      <p class="text-xs text-gray-500 mt-1">
        Menampilkan ringkasan nilai per siswa pada semester {{ $semesterLabel }}.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-gray-800 border-collapse">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-4 py-3 text-left border border-blue-500">No</th>
            <th class="px-4 py-3 text-left border border-blue-500">NIS</th>
            <th class="px-4 py-3 text-left border border-blue-500">Nama Siswa</th>
            <th class="px-4 py-3 text-center border border-blue-500">LM1</th>
            <th class="px-4 py-3 text-center border border-blue-500">LM2</th>
            <th class="px-4 py-3 text-center border border-blue-500">LM3</th>
            <th class="px-4 py-3 text-center border border-blue-500">LM4</th>
            <th class="px-4 py-3 text-center border border-blue-500">Nilai Akhir</th>
            <th class="px-4 py-3 text-center border border-blue-500">Status</th>
            <th class="px-4 py-3 text-center border border-blue-500">Finalisasi</th>
          </tr>
        </thead>

        <tbody>
          @forelse($nilai as $i => $row)
            @php
              $isFinal = ($row->status_penilaian ?? 'draft') === 'final';
            @endphp

            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 border border-gray-200">
                {{ $i + 1 }}
              </td>

              <td class="px-4 py-3 border border-gray-200">
                {{ $row->nis ?? '-' }}
              </td>

              <td class="px-4 py-3 border border-gray-200 font-medium text-gray-900 whitespace-nowrap">
                {{ $row->nama ?? '-' }}
              </td>

              <td class="px-4 py-3 border border-gray-200 text-center">
                {{ $num($row->lm1_nilai) }}
              </td>

              <td class="px-4 py-3 border border-gray-200 text-center">
                {{ $num($row->lm2_nilai) }}
              </td>

              <td class="px-4 py-3 border border-gray-200 text-center">
                {{ $num($row->lm3_nilai) }}
              </td>

              <td class="px-4 py-3 border border-gray-200 text-center">
                {{ $num($row->lm4_nilai) }}
              </td>

              <td class="px-4 py-3 border border-gray-200 text-center font-semibold text-gray-900">
                {{ $num($row->nilai_akhir) }}
              </td>

              <td class="px-4 py-3 border border-gray-200 text-center">
                @if(($row->status ?? null) === 'tuntas')
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    Tuntas
                  </span>
                @elseif(($row->status ?? null) === 'tidak_tuntas')
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                    Tidak Tuntas
                  </span>
                @else
                  <span class="text-gray-400">—</span>
                @endif
              </td>

              <td class="px-4 py-3 border border-gray-200 text-center">
                @if($isFinal)
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    Final
                  </span>
                @else
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                    Draft
                  </span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500">
                Belum ada data siswa atau nilai pada semester {{ $semesterLabel }}.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection