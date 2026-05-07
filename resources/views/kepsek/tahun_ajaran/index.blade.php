@extends('layouts.kepsek')
@section('title', 'Tahun Ajaran')

@section('content')
<div class="w-full">
  <div class="mb-6">
    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-gray-900">
      Tahun Ajaran
    </h1>
    <p class="mt-1 text-sm text-gray-500">
      Kelola tahun ajaran dan semester aktif yang digunakan sistem.
    </p>
  </div>

  <form method="GET" action="{{ route('kepala_sekolah.data.tahun-ajaran') }}" class="mb-4">
    <div class="bg-white rounded-2xl border px-3 py-2.5 flex flex-wrap items-center gap-2">
      <div class="relative flex-1 min-w-[220px] max-w-md">
        <input type="text" name="q"
               value="{{ request('q') }}"
               class="w-full pl-9 pr-3 py-2.5 rounded-xl border text-sm
                      focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500"
               placeholder="Cari nama tahun ajaran, semester, status...">
        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
             viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
                d="M12.9 14.32a8 8 0 1 1 1.414-1.414l3.387 3.387-1.414 1.414-3.387-3.387ZM14 8a6 6 0 1 0-12 0 6 6 0 0 0 12 0Z"
                clip-rule="evenodd" />
        </svg>
      </div>

      <button class="px-3 py-2 rounded-xl border bg-white text-sm hover:bg-gray-50">
        Cari
      </button>

      @if(request('q'))
        <a href="{{ route('kepala_sekolah.data.tahun-ajaran') }}"
           class="px-3 py-2 rounded-xl border bg-white text-sm hover:bg-gray-50">
          Reset
        </a>
      @endif
    </div>
  </form>

  <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
    @if($items->isEmpty())
      <div class="p-10 text-center text-gray-500 text-sm">
        Belum ada data tahun ajaran.
      </div>
    @else
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-gray-50 text-gray-700 border-b">
            <th class="px-4 py-3 text-left font-semibold">Tahun Ajaran</th>
            <th class="px-4 py-3 text-left font-semibold">Semester</th>
            <th class="px-4 py-3 text-left font-semibold">Mulai</th>
            <th class="px-4 py-3 text-left font-semibold">Selesai</th>
            <th class="px-4 py-3 text-left font-semibold">Status</th>
            <th class="px-4 py-3 text-center font-semibold w-64">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($items as $item)
            <tr class="hover:bg-indigo-50/30 transition-colors">
              <td class="px-4 py-3 font-medium text-gray-900">
                {{ $item->nama_tahun }}
              </td>

              <td class="px-4 py-3">
                {{ $item->semester }}
              </td>

              <td class="px-4 py-3">
                {{ $item->mulai_formatted }}
              </td>

              <td class="px-4 py-3">
                {{ $item->selesai_formatted }}
              </td>

              <td class="px-4 py-3">
                @if($item->status === 'aktif')
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs
                                 bg-green-100 text-green-700 border border-green-200">
                    Aktif
                  </span>
                @else
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs
                                 bg-gray-100 text-gray-600 border border-gray-200">
                    Nonaktif
                  </span>
                @endif
              </td>

              <td class="px-4 py-3 text-center">
                <div class="inline-flex items-center gap-2 flex-wrap justify-end">
                  <a href="{{ route('kepala_sekolah.data.tahun-ajaran.edit', $item) }}"
                     class="px-3 py-1.5 rounded-lg bg-yellow-500 text-white text-xs hover:bg-yellow-600">
                    Edit
                  </a>

                  @if($item->status !== 'aktif')
                    <form method="POST"
                          action="{{ route('kepala_sekolah.data.tahun-ajaran.setAktif', $item) }}"
                          class="inline-block">
                      @csrf
                      @method('PATCH')
                      <button type="submit"
                              class="px-3 py-1.5 rounded-lg bg-green-600 text-white text-xs hover:bg-green-700">
                        Aktifkan
                      </button>
                    </form>

                    <button type="button"
                            onclick="openDeleteModal('{{ route('kepala_sekolah.data.tahun-ajaran.destroy', $item) }}', '{{ addslashes($item->nama_tahun . ' - ' . $item->semester) }}')"
                            class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs hover:bg-red-700">
                      Hapus
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      @if($items instanceof \Illuminate\Pagination\Paginator
          || $items instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="px-4 py-3 border-t bg-gray-50">
          {{ $items->links() }}
        </div>
      @endif
    @endif
  </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/50" onclick="closeDeleteModal()"></div>

  <div class="relative flex items-center justify-center min-h-screen px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6">
      <div class="flex items-start gap-3">
        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-600" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v2m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z" />
          </svg>
        </div>

        <div class="flex-1">
          <h2 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus Data</h2>
          <p class="mt-2 text-sm text-gray-600 leading-6">
            Yakin ingin menghapus tahun ajaran
            <span id="deleteTaName" class="font-semibold text-gray-900"></span>?
          </p>
          <p class="mt-2 text-sm text-red-600">
            Tahun ajaran yang sedang aktif tidak dapat dihapus.
          </p>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button type="button"
                onclick="closeDeleteModal()"
                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">
          Batal
        </button>

        <form id="deleteForm" method="POST">
          @csrf
          @method('DELETE')
          <button type="submit"
                  class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
            Ya, Hapus
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  function openDeleteModal(actionUrl, taName) {
    document.getElementById('deleteForm').action = actionUrl;
    document.getElementById('deleteTaName').textContent = taName;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }

  function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteForm').action = '';
    document.getElementById('deleteTaName').textContent = '';
    document.body.classList.remove('overflow-hidden');
  }
</script>
@endsection