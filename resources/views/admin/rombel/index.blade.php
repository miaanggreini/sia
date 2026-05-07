@extends(auth()->user()?->role === 'kepala_sekolah' ? 'layouts.kepsek' : 'layouts.admin')

@section('content')
@php
  use App\Models\TahunAjaran;

  $tahunAjaranList = $tahunAjaranList ?? TahunAjaran::orderByDesc('id')->get();
  $tahunAjaranAktif = $tahunAjaranAktif ?? TahunAjaran::where('status', 'aktif')->first();

  $selectedTahun = request('tahun_ajaran_id', optional($tahunAjaranAktif)->id);
  $selectedTingkat = request('tingkat');
  $q = request('q');

  $filterParams = array_filter([
      'tahun_ajaran_id' => $selectedTahun,
      'tingkat' => $selectedTingkat,
      'q' => $q,
      'page' => request('page'),
  ], fn ($value) => $value !== null && $value !== '');
@endphp

<div class="space-y-6">

  {{-- HEADER --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Rombel</h1>
      <p class="mt-1 text-sm text-gray-500">
        Kelola rombongan belajar berdasarkan tahun ajaran dan tingkat kelas.
      </p>
    </div>

    <a href="{{ route('admin.rombel.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 5c.414 0 .75.336.75.75V11h5.25a.75.75 0 010 1.5H12.75v5.25a.75.75 0 01-1.5 0V12.5H6a.75.75 0 010-1.5h5.25V5.75c0-.414.336-.75.75-.75z"/>
            </svg>
      Tambah Rombel
    </a>
  </div>

  {{-- FILTER --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
    <form method="GET" action="{{ route('admin.rombel.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Tahun Ajaran</label>
        <select name="tahun_ajaran_id"
                class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
          <option value="">Semua Tahun Ajaran</option>
          @foreach($tahunAjaranList as $ta)
            <option value="{{ $ta->id }}" @selected((string) $selectedTahun === (string) $ta->id)>
              {{ $ta->nama_tahun ?? $ta->label ?? '-' }}
              @if(($ta->status ?? null) === 'aktif')
                — Aktif
              @endif
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Tingkat</label>
        <select name="tingkat"
                class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
          <option value="">Semua Tingkat</option>
          <option value="X" @selected($selectedTingkat === 'X')>X</option>
          <option value="XI" @selected($selectedTingkat === 'XI')>XI</option>
          <option value="XII" @selected($selectedTingkat === 'XII')>XII</option>
        </select>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Cari</label>
        <input type="text"
               name="q"
               value="{{ $q }}"
               placeholder="Cari rombel atau wali kelas..."
               class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
      </div>

      <div class="flex items-end gap-2">
        <button type="submit"
                class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
          Terapkan
        </button>

        <a href="{{ route('admin.rombel.index') }}"
           class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
          Reset
        </a>
      </div>
    </form>
  </div>

  {{-- TABLE --}}
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
    <div class="border-b px-5 py-4">
      <h2 class="text-base font-semibold text-gray-800">Daftar Rombel</h2>
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
            Tidak ada data rombel sesuai filter yang dipilih.
          </p>

          <a href="{{ route('admin.rombel.create') }}"
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
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">No</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nama Rombel</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tingkat</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Wali Kelas</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Mapel</th>
              <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tahun Ajaran</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Jumlah Siswa</th>
              <th class="px-5 py-3 text-center text-xs font-semibold uppercase text-gray-500">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @foreach($items as $i => $row)
              <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3 text-gray-600">
                  {{ $items->firstItem() + $i }}
                </td>

                <td class="px-5 py-3">
<div class="font-semibold text-gray-800">
    {{ $row->nama_rombel }}
</div>                </td>

                <td class="px-5 py-3">
                  <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700">
                    {{ $row->tingkat ?? '-' }}
                  </span>
                </td>

                <td class="px-5 py-3">
                  <div class="font-medium text-gray-800">
                    {{ $row->waliKelas->nama ?? '-' }}
                  </div>
                  <div class="text-xs text-gray-500">Wali Kelas</div>
                </td>

                <td class="px-5 py-3">
                  <div class="flex max-w-md flex-wrap gap-1.5">
                    @forelse($row->mapel ?? [] as $mapel)
                      <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                        {{ $mapel->nama_mapel }}
                      </span>
                    @empty
                      <span class="text-xs text-gray-400">Belum ada mapel</span>
                    @endforelse
                  </div>
                </td>

                <td class="px-5 py-3">
                  <span class="inline-flex rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                    {{ $row->tahunAjaran->nama_tahun ?? $row->tahunAjaran->label ?? '-' }}
                  </span>
                </td>

                <td class="px-5 py-3 text-center">
                  <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
{{ $row->jumlah_siswa_histori ?? 0 }} siswa                  </span>
                </td>

                <td class="px-5 py-3">
                  <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                  <a href="{{ route('admin.rombel.anggota', array_merge(['rombel' => $row->id], $filterParams)) }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                    Anggota
                  </a>

                  <a href="{{ route('admin.rombel.edit', array_merge(['rombel' => $row->id], $filterParams)) }}"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                    Edit
                  </a>                  
              </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="border-t bg-gray-50 px-5 py-3">
        {{ $items->withQueryString()->links() }}
      </div>
    @endif
  </div>
</div>
@endsection