{{-- resources/views/admin/ekskul/index.blade.php --}}
@php
    $user = auth()->user();
    $isKepsek = $user?->role === 'kepala_sekolah';
@endphp

@extends($isKepsek ? 'layouts.kepsek' : 'layouts.admin')

@section('title', 'Ekskul')

@section('content')
<div class="space-y-6">

  {{-- HEADER --}}
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Data Ekskul</h1>
      <p class="mt-1 text-sm text-gray-500">
        @if($isKepsek)
          Lihat data ekstrakurikuler dan monitoring anggota ekskul.
        @else
          Kelola data ekstrakurikuler, pembina, jadwal, dan lokasi kegiatan.
        @endif
      </p>
    </div>

    @unless($isKepsek)
      <a href="{{ route('admin.ekskul.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 5c.414 0 .75.336.75.75V11h5.25a.75.75 0 010 1.5H12.75v5.25a.75.75 0 01-1.5 0V12.5H6a.75.75 0 010-1.5h5.25V5.75c0-.414.336-.75.75-.75z"/>
            </svg>
        Tambah Ekskul
      </a>
    @endunless
  </div>

  {{-- ALERT --}}
  @if(session('success') || session('ok'))
    <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
      {{ session('success') ?? session('ok') }}
    </div>
  @endif

  @if(session('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  {{-- SEARCH AUTO --}}
  <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
    <form id="searchEkskulForm" method="GET" action="{{ url()->current() }}">
      <label class="mb-1 block text-sm font-medium text-gray-700">Cari Ekskul</label>

      <div class="relative">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14z"/>
          </svg>
        </span>

        <input type="text"
               id="searchEkskulInput"
               name="q"
               value="{{ $q ?? request('q') }}"
               placeholder="Cari nama, pembina, hari, atau lokasi ekskul..."
               autocomplete="off"
               class="w-full rounded-xl border-gray-300 pl-10 pr-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

        @if(($q ?? request('q')))
          <a href="{{ url()->current() }}"
             class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm font-semibold text-gray-400 hover:text-red-500">
            ✕
          </a>
        @endif
      </div>

      <p class="mt-2 text-xs text-gray-400">
        Pencarian berjalan otomatis setelah mengetik.
      </p>
    </form>
  </div>

  {{-- TABLE --}}
  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
    @if($items->isEmpty())
      <div class="px-5 py-12 text-center">
        <div class="flex flex-col items-center justify-center">
          <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
            🏃
          </div>
          <h3 class="text-sm font-semibold text-gray-700">Belum ada data ekskul</h3>
          <p class="mt-1 text-sm text-gray-500">
            Data ekstrakurikuler belum tersedia atau tidak sesuai pencarian.
          </p>

          @unless($isKepsek)
            <a href="{{ route('admin.ekskul.create') }}"
               class="mt-4 inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
              Tambah Ekskul
            </a>
          @endunless
        </div>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="border-b bg-gray-50">
            <tr>
              <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
              <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Ekskul</th>
              <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Pembina</th>
              <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Hari & Jam</th>
              <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Lokasi</th>
              <th class="whitespace-nowrap px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                {{ $isKepsek ? 'Monitoring' : 'Aksi' }}
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @forelse ($items as $item)
              <tr class="transition hover:bg-gray-50">
                <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                  {{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}
                </td>

                <td class="px-5 py-4">
                  <div class="font-semibold text-gray-800">
                    {{ $item->nama }}
                  </div>
                </td>

                <td class="px-5 py-4">
                  <div class="font-medium text-gray-800">
                    {{ $item->pembina->nama ?? '-' }}
                  </div>
                  <div class="text-xs text-gray-500">Pembina Ekskul</div>
                </td>

                <td class="px-5 py-4">
                  <div class="space-y-1">
                    @if($item->hari)
                      <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                        {{ $item->hari }}
                      </span>
                    @else
                      <span class="text-xs text-gray-400">Hari belum diatur</span>
                    @endif

                    <div class="text-xs text-gray-500">
                      @if($item->jam_mulai || $item->jam_selesai)
                        {{ $item->jam_mulai ? \Illuminate\Support\Str::substr($item->jam_mulai, 0, 5) : '–' }}
                        –
                        {{ $item->jam_selesai ? \Illuminate\Support\Str::substr($item->jam_selesai, 0, 5) : '–' }}
                      @else
                        Jam belum diatur
                      @endif
                    </div>
                  </div>
                </td>

                <td class="px-5 py-4">
                  <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    {{ $item->lokasi ?? 'Lokasi belum diatur' }}
                  </span>
                </td>

                <td class="px-5 py-4">
                  <div class="flex items-center justify-center gap-2 whitespace-nowrap">
                    @if($isKepsek)
                      <a href="{{ route('kepala_sekolah.ekskul.anggota', $item) }}"
                         class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">
                        Lihat Anggota
                      </a>
                    @else
                      <a href="{{ route('admin.ekskul.edit', $item) }}"
                         class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-700">
                        Ubah
                      </a>

                      <a href="{{ route('admin.ekskul.anggota', $item) }}"
                         class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">
                        Lihat Anggota
                      </a>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">
                  Belum ada data ekskul.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($items->hasPages())
        <div class="border-t bg-gray-50 px-5 py-3">
          {{ $items->withQueryString()->links() }}
        </div>
      @endif
    @endif
  </div>

</div>

<script>
  const searchEkskulInput = document.getElementById('searchEkskulInput');
  const searchEkskulForm = document.getElementById('searchEkskulForm');

  if (searchEkskulInput && searchEkskulForm) {
    let typingTimer;

    searchEkskulInput.addEventListener('input', function () {
      clearTimeout(typingTimer);

      typingTimer = setTimeout(function () {
        searchEkskulForm.submit();
      }, 500);
    });
  }
</script>
@endsection