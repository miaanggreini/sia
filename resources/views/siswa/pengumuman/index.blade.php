@extends('layouts.siswa')

@section('content')
<div class="overflow-hidden rounded-2xl border bg-white shadow-sm">

  {{-- HEADER --}}
  <div class="border-b bg-gradient-to-r from-indigo-50 to-sky-50 px-5 py-5">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Pengumuman</h1>
        <p class="mt-1 text-sm text-gray-500">
          Menampilkan {{ $data->count() }} dari {{ $data->total() }} pengumuman.
        </p>
      </div>

      <form method="get"
            action="{{ route('siswa.pengumuman.index') }}"
            class="flex w-full gap-2 md:w-auto">
        <div class="relative flex-1 md:w-96">
          <input
            type="text"
            name="q"
            value="{{ $q }}"
            placeholder="Cari judul atau isi pengumuman..."
            class="w-full rounded-xl border-gray-300 bg-white px-4 py-2.5 pr-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          />

          @if($q)
            <a href="{{ route('siswa.pengumuman.index') }}"
               class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm font-semibold text-gray-400 hover:text-red-500">
              ✕
            </a>
          @endif
        </div>

        <button type="submit"
                class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
          Cari
        </button>
      </form>
    </div>
  </div>

  {{-- LIST --}}
  @if($data->isEmpty())
    <div class="px-5 py-14 text-center">
      <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
        📢
      </div>
      <h2 class="text-sm font-semibold text-gray-700">Tidak ada pengumuman</h2>
      <p class="mt-1 text-sm text-gray-500">
        Belum ada pengumuman yang sesuai dengan pencarian.
      </p>
    </div>
  @else
    <div class="divide-y">
      @foreach($data as $p)
        @php
          $kategori = strtolower(trim($p->kategori ?? 'umum'));

          $kategoriMap = [
              'akademik' => [
                  'label' => 'Akademik',
                  'class' => 'border-indigo-200 bg-indigo-50 text-indigo-700',
              ],
              'kesiswaan' => [
                  'label' => 'Kesiswaan',
                  'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
              ],
              'ekstrakurikuler' => [
                  'label' => 'Ekstrakurikuler',
                  'class' => 'border-violet-200 bg-violet-50 text-violet-700',
              ],
              'kegiatan' => [
                  'label' => 'Kegiatan',
                  'class' => 'border-amber-200 bg-amber-50 text-amber-700',
              ],
          ];

          $kategoriStyle = $kategoriMap[$kategori] ?? [
              'label' => ucfirst($kategori),
              'class' => 'border-gray-200 bg-gray-50 text-gray-700',
          ];

$tanggalTampil = $p->published_at
    ?? $p->tanggal_mulai
    ?? $p->created_at;
            @endphp

        <article class="px-5 py-5 transition hover:bg-gray-50">
          <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">

            {{-- KONTEN --}}
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('siswa.pengumuman.show', $p) }}"
                   class="text-lg font-bold text-gray-900 hover:text-indigo-700">
                  {{ $p->judul }}
                </a>

                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $kategoriStyle['class'] }}">
                  {{ $kategoriStyle['label'] }}
                </span>
              </div>

              <p class="mt-2 text-sm leading-6 text-gray-700">
                {{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 260) }}
              </p>

              <div class="mt-3">
                <a href="{{ route('siswa.pengumuman.show', $p) }}"
                   class="inline-flex items-center text-sm font-semibold text-indigo-700 hover:text-indigo-900">
                  Baca selengkapnya
                  <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5l7 7-7 7"/>
                  </svg>
                </a>
              </div>
            </div>

            {{-- TANGGAL --}}
            <div class="shrink-0 md:text-right">
              <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                {{ $tanggalTampil ? \Carbon\Carbon::parse($tanggalTampil)->format('d M Y') : '-' }}
              </span>
              <p class="mt-1 text-[11px] text-gray-400">
                Dipublikasikan
              </p>
            </div>

          </div>
        </article>
      @endforeach
    </div>

    <div class="border-t bg-gray-50 px-5 py-4">
      {{ $data->withQueryString()->links() }}
    </div>
  @endif

</div>
@endsection