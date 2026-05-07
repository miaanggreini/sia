@extends(auth()->user()?->role === 'kepala_sekolah' ? 'layouts.kepsek' : 'layouts.admin')

@section('content')
<div class="space-y-6">

  {{-- HEADER --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Mata Pelajaran</h1>
      <p class="mt-1 text-sm text-gray-500">
        Kelola data mata pelajaran, kelompok, dan KKM.
      </p>
    </div>

    <a href="{{ route('admin.mapel.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 5c.414 0 .75.336.75.75V11h5.25a.75.75 0 010 1.5H12.75v5.25a.75.75 0 01-1.5 0V12.5H6a.75.75 0 010-1.5h5.25V5.75c0-.414.336-.75.75-.75z"/>
            </svg>
                  Tambah Mapel
    </a>
  </div>

  {{-- SEARCH AUTO --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
    <form id="searchMapelForm" method="GET" action="{{ route('admin.mapel.index') }}">
      <label class="mb-1 block text-sm font-medium text-gray-700">Cari Mata Pelajaran</label>

      <div class="relative">

        <input type="text"
               id="searchMapelInput"
               name="q"
               value="{{ request('q') }}"
               placeholder="Cari nama mapel atau kelompok..."
               class="w-full rounded-xl border-gray-300 pl-10 pr-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

        @if(request('q'))
          <a href="{{ route('admin.mapel.index') }}"
             class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500">
            ✕
          </a>
        @endif
      </div>

      <p class="mt-2 text-xs text-gray-400">Pencarian otomatis.</p>
    </form>
  </div>

  {{-- TABLE --}}
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
    @if($items->isEmpty())
      <div class="px-5 py-12 text-center">
        <div class="flex flex-col items-center">
          <div class="mb-3 text-3xl">📚</div>
          <h3 class="text-sm font-semibold text-gray-700">Belum ada mata pelajaran</h3>
          <p class="text-sm text-gray-500 mt-1">
            Silakan tambahkan data terlebih dahulu.
          </p>

          <a href="{{ route('admin.mapel.create') }}"
             class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
            Tambah Mapel
          </a>
        </div>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama Mapel</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kelompok</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">KKM</th>
              <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
              <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @foreach ($items as $i => $row)
              <tr class="hover:bg-gray-50 transition">

                {{-- NO --}}
                <td class="px-5 py-3 text-gray-600">
                  {{ $items->firstItem() + $i }}
                </td>

                {{-- NAMA --}}
                <td class="px-5 py-3 font-semibold text-gray-800">
                  {{ $row->nama_mapel }}
                </td>

                {{-- KELOMPOK --}}
                <td class="px-5 py-3">
                  <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    {{ $row->kelompok ?? '—' }}
                  </span>
                </td>

                {{-- KKM --}}
                <td class="px-5 py-3">
                  <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                    {{ $row->kkm ?? '-' }}
                  </span>
                </td>

                {{-- STATUS --}}
                <td class="px-5 py-3">
                  @php $st = strtolower($row->status ?? 'aktif'); @endphp
                  <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold
                    {{ $st === 'aktif'
                        ? 'border-green-200 bg-green-50 text-green-700'
                        : 'border-gray-200 bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($st) }}
                  </span>
                </td>

                {{-- AKSI --}}
                <td class="px-5 py-3">
                  <div class="flex justify-center gap-2 whitespace-nowrap">

                    <a href="{{ route('admin.mapel.edit', $row) }}"
                       class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                      Ubah
                    </a>

                  </div>
                </td>

              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- PAGINATION --}}
      <div class="px-5 py-3 border-t bg-gray-50">
        {{ $items->withQueryString()->links() }}
      </div>
    @endif
  </div>

</div>

{{-- AUTO SEARCH --}}
<script>
  const input = document.getElementById('searchMapelInput');
  const form = document.getElementById('searchMapelForm');

  if (input && form) {
    let timer;

    input.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(() => form.submit(), 500);
    });
  }
</script>
@endsection