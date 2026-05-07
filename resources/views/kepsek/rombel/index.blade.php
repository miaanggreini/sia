@extends('layouts.kepsek')

@section('content')
@php
    $items = $items ?? $rombels ?? collect();

    $selectedTahun = $selectedTahun ?? request('tahun_ajaran_id', optional($tahunAjaranAktif ?? null)->id);
    $selectedTingkat = $selectedTingkat ?? request('tingkat');
    $q = $q ?? request('q');

    $filterParams = array_filter([
        'tahun_ajaran_id' => $selectedTahun,
        'tingkat' => $selectedTingkat,
        'q' => $q,
        'page' => request('page'),
    ], fn ($value) => $value !== null && $value !== '');

    $tingkatOptions = ['X', 'XI', 'XII'];
@endphp

<div class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Rombel</h1>
      <p class="mt-1 text-sm text-gray-500">
        Kelola data rombongan belajar, wali kelas, dan tahun ajaran.
      </p>
    </div>
  </div>

  {{-- Alert --}}
  @if(session('ok'))
    <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
      {{ session('ok') }}
    </div>
  @endif

  @if(session('err'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ session('err') }}
    </div>
  @endif

  {{-- Filter --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
    <form id="filterRombelForm"
          method="GET"
          action="{{ route('kepala_sekolah.data.rombel.index') }}"
          class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end">

      <div class="lg:col-span-3">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">
          Tahun Ajaran
        </label>
        <select name="tahun_ajaran_id"
                class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
          @foreach($tahunAjaranList as $ta)
            @php
              $taLabel = $ta->nama_tahun ?? $ta->nama ?? $ta->label ?? '-';
              $isAktif = ($ta->status ?? null) === 'aktif';
            @endphp
            <option value="{{ $ta->id }}" @selected((int)$selectedTahun === (int)$ta->id)>
              {{ $taLabel }} @if($isAktif) — Aktif @endif
            </option>
          @endforeach
        </select>
      </div>

      <div class="lg:col-span-3">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">
          Tingkat
        </label>
        <select name="tingkat"
                class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
          <option value="">Semua Tingkat</option>
          @foreach($tingkatOptions as $tingkat)
            <option value="{{ $tingkat }}" @selected($selectedTingkat === $tingkat)>
              {{ $tingkat }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="lg:col-span-3">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">
          Cari
        </label>
        <input type="text"
               id="searchRombelInput"
               name="q"
               value="{{ $q }}"
               placeholder="Cari rombel atau wali kelas..."
               autocomplete="off"
               class="w-full rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
      </div>

      <div class="flex gap-2 lg:col-span-3">
        <button type="submit"
                class="inline-flex h-[42px] flex-1 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700">
          Terapkan
        </button>

        <a href="{{ route('kepala_sekolah.data.rombel.index') }}"
           class="inline-flex h-[42px] items-center justify-center rounded-xl border border-gray-300 bg-white px-5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
          Reset
        </a>
      </div>
    </form>
  </div>

  {{-- Table --}}
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
    <div class="border-b px-5 py-4">
      <h2 class="text-base font-semibold text-gray-900">
        Daftar Rombel
      </h2>
      <p class="mt-1 text-sm text-gray-500">
        Default menampilkan rombel pada tahun ajaran aktif. Gunakan filter untuk melihat tahun ajaran lain.
      </p>
    </div>

    @if($items->isEmpty())
      <div class="px-5 py-12 text-center">
        <div class="flex flex-col items-center">
          <div class="mb-3 text-3xl">🏫</div>
          <h3 class="text-sm font-semibold text-gray-700">Belum ada rombel</h3>
          <p class="mt-1 text-sm text-gray-500">
            Tidak ada rombel yang sesuai dengan filter.
          </p>

          <a href="{{ route('kepala_sekolah.data.rombel.create') }}"
             class="mt-4 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
            Tambah Rombel
          </a>
        </div>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b bg-gray-50">
            <tr>
              <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">No</th>
              <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nama Rombel</th>
              <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tingkat</th>
              <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Wali Kelas</th>
              <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Mapel</th>
              <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Tahun Ajaran</th>
              <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Jumlah Siswa</th>
              <th class="sticky right-0 whitespace-nowrap bg-gray-50 px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @foreach($items as $i => $row)
              @php
                $mapelRows = collect();

                if (isset($row->mataPelajaran)) {
                    $mapelRows = $row->mataPelajaran;
                } elseif (isset($row->mapel)) {
                    $mapelRows = $row->mapel;
                }

                $tahunLabel = $row->tahunAjaran->nama_tahun
                    ?? $row->tahunAjaran->nama
                    ?? $row->tahunAjaran->label
                    ?? '-';
              @endphp

              <tr class="transition hover:bg-gray-50">
                <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                  {{ method_exists($items, 'firstItem') ? $items->firstItem() + $i : $loop->iteration }}
                </td>

                <td class="px-5 py-3 font-semibold text-gray-800">
                  {{ $row->nama_rombel }}
                </td>

                <td class="whitespace-nowrap px-5 py-3">
                  <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700">
                    {{ $row->tingkat ?? '-' }}
                  </span>
                </td>

                <td class="whitespace-nowrap px-5 py-3">
                  <div class="font-semibold text-gray-800">
                    {{ $row->waliKelas->nama ?? '-' }}
                  </div>
                  <div class="text-xs text-gray-500">Wali Kelas</div>
                </td>

                <td class="px-5 py-3">
                  <div class="flex max-w-md flex-wrap gap-1.5">
                    @forelse($mapelRows as $mapel)
                      <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                        {{ $mapel->nama_mapel }}
                      </span>
                    @empty
                      <span class="text-xs text-gray-400">Belum ada mapel</span>
                    @endforelse
                  </div>
                </td>

                <td class="whitespace-nowrap px-5 py-3 text-center">
                  <span class="inline-flex rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                    {{ $tahunLabel }}
                  </span>
                </td>

<td class="whitespace-nowrap px-5 py-3 text-center">
  <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
    {{ $row->jumlah_siswa ?? 0 }} siswa
  </span>
</td>


                <td class="sticky right-0 bg-white px-5 py-3 min-w-[220px]">
                  <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                    <a href="{{ route('kepala_sekolah.data.rombel.anggota', array_merge(['rombel' => $row->id], $filterParams)) }}"
                       class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                      Anggota
                    </a>

                    <a href="{{ route('kepala_sekolah.data.rombel.edit', array_merge(['rombel' => $row->id], $filterParams)) }}"
                       class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                      Edit
                    </a>

                    <button type="button"
                            onclick="openDeleteModal('{{ route('kepala_sekolah.data.rombel.destroy', $row) }}', '{{ addslashes($row->nama_rombel) }}')"
                            class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      @if(method_exists($items, 'links'))
        <div class="border-t bg-gray-50 px-5 py-3">
          {{ $items->withQueryString()->links() }}
        </div>
      @endif
    @endif
  </div>
</div>

{{-- Modal Hapus --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/50" onclick="closeDeleteModal()"></div>

  <div class="relative flex min-h-screen items-center justify-center px-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
      <h2 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus</h2>

      <p class="mt-2 text-sm leading-6 text-gray-600">
        Yakin ingin menghapus rombel
        <span id="deleteRombelName" class="font-semibold text-gray-900"></span>?
      </p>

      <p class="mt-2 text-sm text-red-600">
        Jika rombel sudah digunakan pada data akademik, sistem akan menolak penghapusan.
      </p>

      <div class="mt-6 flex justify-end gap-3">
        <button type="button"
                onclick="closeDeleteModal()"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
          Batal
        </button>

        <form id="deleteForm" method="POST">
          @csrf
          @method('DELETE')

          <button type="submit"
                  class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
            Ya, Hapus
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function openDeleteModal(actionUrl, rombelName) {
    document.getElementById('deleteForm').action = actionUrl;
    document.getElementById('deleteRombelName').textContent = rombelName;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }

  function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteForm').action = '';
    document.getElementById('deleteRombelName').textContent = '';
    document.body.classList.remove('overflow-hidden');
  }
</script>
@endsection