@extends(auth()->user()?->role === 'kepala_sekolah' ? 'layouts.kepsek' : 'layouts.admin')

@section('title','Tahun Ajaran')

@section('content')
<div class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Tahun Ajaran</h1>
      <p class="mt-1 text-sm text-gray-500">
        Kelola periode tahun ajaran, semester aktif, dan status tahun ajaran yang digunakan sistem.
      </p>
    </div>

    <a href="{{ route('admin.tahun_ajaran.create') }}"
       class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 5c.414 0 .75.336.75.75V11h5.25a.75.75 0 010 1.5H12.75v5.25a.75.75 0 01-1.5 0V12.5H6a.75.75 0 010-1.5h5.25V5.75c0-.414.336-.75.75-.75z"/>
      </svg>
      Tambah Tahun Ajaran
    </a>
  </div>

  {{-- Alert --}}
  @if (session('success'))
    <div x-data="{open:true}" x-show="open" x-transition
         class="flex items-start justify-between gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
      <span>{{ session('success') }}</span>
      <button type="button" @click="open=false" class="text-green-700/70 hover:text-green-900">✕</button>
    </div>
  @endif

  @if (session('error'))
    <div x-data="{open:true}" x-show="open" x-transition
         class="flex items-start justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <span>{{ session('error') }}</span>
      <button type="button" @click="open=false" class="text-red-700/70 hover:text-red-900">✕</button>
    </div>
  @endif

  {{-- Filter --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-col gap-3 md:flex-row md:items-end">
      <div class="flex-1">
        <label class="mb-1 block text-sm font-medium text-gray-700">Cari Tahun Ajaran</label>
        <div class="relative">
          <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14z"/>
            </svg>
          </span>
          <input type="text"
                 name="q"
                 value="{{ $q }}"
                 placeholder="Cari tahun, semester, atau status..."
                 class="w-full rounded-xl border-gray-300 pl-10 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
      </div>

      <div class="flex gap-2">
        <button type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-gray-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-700">
          Cari
        </button>

        @if(request('q'))
          <a href="{{ route('admin.tahun_ajaran.index') }}"
             class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            Reset
          </a>
        @endif
      </div>
    </form>
  </div>

  {{-- Table --}}
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tahun</th>
            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Semester Aktif</th>
            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Mulai</th>
            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Selesai</th>
            <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
            <th class="sticky right-0 whitespace-nowrap bg-gray-50 px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100 bg-white">
          @forelse($items as $i => $row)
            <tr class="transition hover:bg-gray-50">
              <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                {{ $items->firstItem() + $i }}
              </td>

              <td class="whitespace-nowrap px-5 py-4">
                <div class="font-semibold text-gray-800">{{ $row->nama_tahun }}</div>
              </td>

              <td class="whitespace-nowrap px-5 py-4">
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold
                  {{ $row->semester === 'Ganjil'
                      ? 'border-amber-200 bg-amber-50 text-amber-700'
                      : 'border-indigo-200 bg-indigo-50 text-indigo-700' }}">
                  {{ $row->semester }}
                </span>
              </td>

              <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                {{ $row->tanggal_mulai ? \Carbon\Carbon::parse($row->tanggal_mulai)->format('d/m/Y') : '-' }}
              </td>

              <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                {{ $row->tanggal_selesai ? \Carbon\Carbon::parse($row->tanggal_selesai)->format('d/m/Y') : '-' }}
              </td>

              <td class="whitespace-nowrap px-5 py-4">
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold
                  {{ $row->status === 'aktif'
                      ? 'border-green-200 bg-green-50 text-green-700'
                      : 'border-gray-200 bg-gray-100 text-gray-600' }}">
                  {{ ucfirst($row->status) }}
                </span>
              </td>

              <td class="sticky right-0 bg-white px-5 py-4">
                <div class="flex flex-wrap items-center justify-center gap-2">
                  <a href="{{ route('admin.tahun_ajaran.edit', $row) }}"
                     class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Ubah
                  </a>

                  @if($row->status !== 'aktif')
                    <button type="button"
                            onclick="openAktifModal('{{ route('admin.tahun_ajaran.setAktif', $row) }}', '{{ $row->nama_tahun }}', '{{ $row->semester }}')"
                            class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-green-700">
                      Aktifkan
                    </button>
                  @else
                    <span class="inline-flex items-center gap-1 rounded-lg bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700">
                      Sedang Aktif
                    </span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-12 text-center">
                <div class="flex flex-col items-center justify-center">
                  <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M6 21h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                    </svg>
                  </div>
                  <h3 class="text-sm font-semibold text-gray-700">Belum ada data tahun ajaran</h3>
                  <p class="mt-1 text-sm text-gray-500">
                    Data tahun ajaran belum tersedia atau tidak sesuai pencarian.
                  </p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pagination --}}
  <div>
    {{ $items->withQueryString()->links() }}
  </div>

  {{-- Modal Konfirmasi Aktivasi --}}
  <div id="aktifModal"
       class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
      <div class="p-6">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Aktifkan Tahun Ajaran</h3>
            <p class="mt-2 text-sm leading-6 text-gray-600">
              Tahun ajaran
              <span id="modalTahun" class="font-semibold text-gray-900"></span>
              dengan semester
              <span id="modalSemester" class="font-semibold text-gray-900"></span>
              akan dijadikan aktif. Tahun ajaran aktif lain akan otomatis dinonaktifkan.
            </p>
          </div>

          <button type="button"
                  onclick="closeAktifModal()"
                  class="text-2xl leading-none text-gray-400 hover:text-gray-600">
            &times;
          </button>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
          <button type="button"
                  onclick="closeAktifModal()"
                  class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Batal
          </button>

          <form id="aktifForm" method="POST">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
              Ya, Aktifkan
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
  function openAktifModal(actionUrl, tahun, semester) {
    const modal = document.getElementById('aktifModal');
    const form = document.getElementById('aktifForm');
    const tahunEl = document.getElementById('modalTahun');
    const semesterEl = document.getElementById('modalSemester');

    form.action = actionUrl;
    tahunEl.textContent = tahun;
    semesterEl.textContent = semester;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }

  function closeAktifModal() {
    const modal = document.getElementById('aktifModal');

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeAktifModal();
    }
  });

  document.getElementById('aktifModal')?.addEventListener('click', function (e) {
    if (e.target.id === 'aktifModal') {
      closeAktifModal();
    }
  });
</script>
@endsection