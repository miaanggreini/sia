{{-- resources/views/siswa/ekskul/index.blade.php --}}
@extends('layouts.siswa')

@section('title','Ekstrakurikuler')

@section('content')
  <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">Ekstrakurikuler</h1>
      <p class="text-sm text-gray-500">
        Pilih ekskul yang ingin kamu ikuti. Pilihan baru resmi tersimpan setelah menekan tombol
        <span class="font-semibold">Simpan Pilihan</span>.
      </p>
    </div>

    <div class="text-xs text-gray-500 md:text-right">
      Tahun ajaran:
      <span class="font-semibold text-gray-800">
        {{ optional($tahunAktif)->nama_tahun ?? '-' }}
      </span>
    </div>
  </div>

  @if (!$tahunAktif)
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
      Tahun ajaran aktif belum diatur, sehingga pendaftaran ekskul sementara belum dapat dilakukan.
    </div>
  @endif

  @if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
      {{ session('success') }}
    </div>
  @endif

  @if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  @if (session('info'))
    <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm text-blue-700">
      {{ session('info') }}
    </div>
  @endif

  <div class="mb-6 rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
    <div class="font-semibold mb-1">Ketentuan pendaftaran ekskul</div>
    <ul class="list-disc pl-5 space-y-1 text-xs sm:text-sm">
      <li>Siswa dapat mengikuti maksimal 3 ekskul aktif pada tahun ajaran berjalan.</li>
      <li>Pilihan ekskul belum resmi terdaftar sebelum menekan tombol Simpan Pilihan.</li>
      <li>Jika sudah tersimpan sebagai anggota, perubahan atau pengeluaran dilakukan oleh pembina ekskul.</li>
    </ul>
  </div>

  {{-- EKSKUL SAYA --}}
  <section class="rounded-2xl border bg-white shadow-sm mb-6">
    <div class="border-b px-5 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
      <div>
        <h2 class="text-base font-semibold text-gray-900">Ekskul Saya</h2>
        <p class="text-xs text-gray-500">
          Daftar ekskul aktif yang sudah resmi kamu ikuti pada tahun ajaran ini.
        </p>
      </div>

      <div class="text-[11px] text-gray-500">
        {{ count($ekskulSayaIds ?? []) }}/3 ekskul aktif
      </div>
    </div>

    <div class="divide-y divide-indigo-50">
      @forelse ($ekskulSaya as $anggota)
        @php
          $ek = $anggota->ekskul;
        @endphp

        @if($ek)
          <div class="px-5 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-indigo-50/60">
            <div class="space-y-0.5">
              <div class="text-sm font-semibold text-indigo-900">
                {{ $ek->nama }}
              </div>

              <div class="text-xs text-indigo-800">
                Pembina:
                <span class="font-medium">
                  {{ $ek->pembina->nama ?? '-' }}
                </span>
              </div>

              <div class="text-xs text-indigo-800/80">
                @if($ek->hari)
                  {{ $ek->hari }},
                @endif

                @if($ek->jam_mulai || $ek->jam_selesai)
                  {{ $ek->jam_mulai ? \Illuminate\Support\Str::substr($ek->jam_mulai,0,5) : '–' }}
                  –
                  {{ $ek->jam_selesai ? \Illuminate\Support\Str::substr($ek->jam_selesai,0,5) : '–' }}
                  &bull;
                @endif

                {{ $ek->lokasi ?? '-' }}
              </div>
            </div>

            <div class="flex items-center gap-3 justify-between md:justify-end w-full md:w-auto">
              <div class="text-[11px] text-indigo-700">
                Bergabung:
                <span class="font-semibold">
                  {{ optional($anggota->tanggal_gabung)->translatedFormat('d M Y') ?? '-' }}
                </span>
              </div>

              <a href="{{ route('siswa.ekskul.show', $ek->id) }}"
                 class="inline-flex items-center gap-1 rounded-full border border-indigo-500 bg-white px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                Lihat presensi & nilai
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd"
                        d="M5.293 4.293a1 1 0 011.414 0L13 10l-6.293 5.707a1 1 0 01-1.414-1.414L10.172 10 5.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
              </a>
            </div>
          </div>
        @endif
      @empty
        <div class="px-5 py-6 text-sm text-gray-500">
          Kamu belum mengikuti ekskul apa pun pada tahun ajaran ini.
        </div>
      @endforelse
    </div>
  </section>

{{-- LANJUTKAN EKSKUL TAHUN LALU --}}
@if(($lanjutEkskul ?? collect())->count() > 0)
  <section class="rounded-2xl border border-emerald-100 bg-white shadow-sm mb-6">
    <div class="border-b border-emerald-100 px-5 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-2 bg-emerald-50/60">
      <div>
        <h2 class="text-base font-semibold text-emerald-900">
          Lanjutkan Ekskul Tahun Lalu
        </h2>
        <p class="text-xs text-emerald-700">
          Kamu pernah mengikuti ekskul berikut pada tahun ajaran
          <span class="font-semibold">
            {{ optional($tahunSebelumnya)->nama_tahun ?? '-' }}
          </span>.
          Pilih lanjutkan jika masih ingin mengikuti pada tahun ajaran ini.
        </p>
      </div>
    </div>

    <div class="divide-y divide-emerald-50">
      @foreach($lanjutEkskul as $anggotaLama)
        @php
          $ek = $anggotaLama->ekskul;
        @endphp

        @if($ek)
          <div class="px-5 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="space-y-0.5">
              <div class="text-sm font-semibold text-gray-900">
                {{ $ek->nama }}
              </div>

              <div class="text-xs text-gray-600">
                Pembina saat ini:
                <span class="font-medium">
                  {{ $ek->pembina->nama ?? '-' }}
                </span>
              </div>

              <div class="text-xs text-gray-500">
                @if($ek->hari)
                  {{ $ek->hari }},
                @endif

                @if($ek->jam_mulai || $ek->jam_selesai)
                  {{ $ek->jam_mulai ? \Illuminate\Support\Str::substr($ek->jam_mulai,0,5) : '–' }}
                  –
                  {{ $ek->jam_selesai ? \Illuminate\Support\Str::substr($ek->jam_selesai,0,5) : '–' }}
                  &bull;
                @endif

                {{ $ek->lokasi ?? '-' }}
              </div>

              <div class="text-[11px] text-emerald-700">
                Riwayat tahun lalu:
                <span class="font-semibold">
                  {{ optional($anggotaLama->tanggal_gabung)->translatedFormat('d M Y') ?? '-' }}
                </span>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <form method="POST" action="{{ route('siswa.ekskul.lanjutkan', $ek->id) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center rounded-full bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                  Lanjutkan
                </button>
              </form>

              <form method="POST" action="{{ route('siswa.ekskul.tidak-lanjut', $ek->id) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center rounded-full border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                  Tidak Lanjut
                </button>
              </form>
            </div>
          </div>
        @endif
      @endforeach
    </div>
  </section>
@endif

{{-- PILIHAN SEMENTARA --}}
@if(($pilihanSementara ?? collect())->count() > 0)
  <section 
    x-data 
    x-transition
    class="rounded-2xl border bg-white shadow-sm mb-6"
  >
    <div class="border-b px-5 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h2 class="text-base font-semibold text-gray-900">
          Pilihan Sementara
        </h2>
        <p class="text-xs text-gray-500">
          Ekskul di bagian ini belum masuk sebagai anggota resmi.
        </p>
      </div>

      {{-- tombol simpan --}}
      <form method="POST" action="{{ route('siswa.ekskul.simpan-pilihan') }}">
        @csrf
        <button type="submit"
                class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 transition">
          Simpan Pilihan
        </button>
      </form>
    </div>

    <div class="divide-y divide-amber-50">
      @foreach ($pilihanSementara as $ek)
        <div class="px-5 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-amber-50/50">
          
          {{-- info ekskul --}}
          <div class="space-y-0.5">
            <div class="text-sm font-semibold text-amber-900">
              {{ $ek->nama }}
            </div>

            <div class="text-xs text-amber-800">
              Pembina:
              <span class="font-medium">
                {{ $ek->pembina->nama ?? '-' }}
              </span>
            </div>

            <div class="text-xs text-amber-800/80">
              @if($ek->hari)
                {{ $ek->hari }},
              @endif

              @if($ek->jam_mulai || $ek->jam_selesai)
                {{ $ek->jam_mulai ? \Illuminate\Support\Str::substr($ek->jam_mulai,0,5) : '–' }}
                –
                {{ $ek->jam_selesai ? \Illuminate\Support\Str::substr($ek->jam_selesai,0,5) : '–' }}
                &bull;
              @endif

              {{ $ek->lokasi ?? '-' }}
            </div>
          </div>

          {{-- tombol batalkan --}}
          <form method="POST" action="{{ route('siswa.ekskul.hapus-pilihan', $ek->id) }}">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center rounded-full border border-red-300 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 transition">
              Batalkan
            </button>
          </form>
        </div>
      @endforeach
    </div>
  </section>
@endif

  {{-- DAFTAR EKSKUL --}}
  <section x-data="{ open: true }" class="mt-6 rounded-2xl border bg-white shadow-sm">
    <button type="button"
            @click="open = !open"
            class="w-full px-4 py-3 flex items-center justify-between gap-3">
      <div class="text-left">
        <div class="text-sm font-semibold text-gray-800">
          Daftar Ekskul
        </div>
        <div class="text-xs text-gray-500">
          Pilih ekskul yang ingin kamu ikuti. Sistem akan mengecek batas maksimal dan bentrok jadwal.
        </div>
      </div>

      <div class="flex items-center gap-2 text-xs text-gray-500">
        <span x-text="open ? 'Sembunyikan daftar' : 'Tampilkan daftar'"></span>
        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-gray-200">
          <svg xmlns="http://www.w3.org/2000/svg"
               class="h-3.5 w-3.5 text-gray-500 transition-transform"
               :class="open ? 'rotate-180' : ''"
               viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.27a.75.75 0 01-.02-1.06z"
                  clip-rule="evenodd" />
          </svg>
        </span>
      </div>
    </button>

    <div x-show="open" x-collapse x-cloak class="border-t border-gray-100">
      @forelse ($daftarEkskul as $ekskul)
        <div class="px-4 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-4 hover:bg-gray-50">
          <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-900 truncate">
              {{ $ekskul->nama }}
            </div>

            <div class="text-xs text-gray-600">
              Pembina:
              <span class="font-medium">
                {{ $ekskul->pembina->nama ?? '-' }}
              </span>
            </div>

            <div class="mt-1 text-xs text-gray-500">
              @if ($ekskul->hari)
                {{ $ekskul->hari }},
              @endif

              @if ($ekskul->jam_mulai || $ekskul->jam_selesai)
                {{ $ekskul->jam_mulai ? \Illuminate\Support\Str::substr($ekskul->jam_mulai, 0, 5) : '–' }}
                –
                {{ $ekskul->jam_selesai ? \Illuminate\Support\Str::substr($ekskul->jam_selesai, 0, 5) : '–' }}
                &bull;
              @endif

              {{ $ekskul->lokasi ?? '-' }}
            </div>
          </div>

          <div class="shrink-0">
            @if (in_array((int) $ekskul->id, $ekskulSayaIds ?? []))
              <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                Sudah diikuti
              </span>
            @elseif (in_array((int) $ekskul->id, $pilihanSementaraIds ?? []))
              <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                Dipilih sementara
              </span>
            @elseif (!$tahunAktif)
              <button type="button"
                      disabled
                      class="inline-flex items-center rounded-full bg-gray-300 px-3 py-1 text-xs font-medium text-white cursor-not-allowed">
                Belum bisa daftar
              </button>
            @else
              <form method="POST" action="{{ route('siswa.ekskul.daftar', $ekskul->id) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center rounded-full bg-indigo-600 px-3 py-1 text-xs font-medium text-white hover:bg-indigo-700">
                  Pilih
                </button>
              </form>
            @endif
          </div>
        </div>
      @empty
        <div class="px-4 py-6 text-center text-sm text-gray-500">
          Belum ada data ekskul yang tersedia.
        </div>
      @endforelse
    </div>
  </section>
@endsection