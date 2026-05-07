{{-- resources/views/admin/pemilihan/rekap.blade.php --}}
@extends('layouts.admin')
@section('title','Rekap & Penempatan Menu Rombel')

@section('content')
@php
  $periodeId = optional($periode)->id;

  $totalDiterima = 0;
  $totalKapasitas = 0;

  foreach ($menu as $m) {
      $r = $rekap[$m->id] ?? [
          'p1' => 0,
          'p2' => 0,
          'total' => 0,
          'diterima' => 0,
          'sisa' => $m->kapasitas_total,
      ];

      $totalDiterima += (int) ($r['diterima'] ?? 0);
      $totalKapasitas += (int) ($m->kapasitas_total ?? 0);
  }

  $totalPendaftar = $totalPendaftar ?? 0;
  $belumCount = isset($belum) ? $belum->count() : 0;
@endphp

<div class="space-y-6">

  {{-- Header --}}
  <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">
        Rekap & Penempatan Menu Rombel (XI)
      </h1>

      <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
        <span class="rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-indigo-700">
          TA: {{ $taLabel ?? '—' }}
        </span>

        @if($periode)
          <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-emerald-700">
            Periode: {{ $periode->nama ?? $periode->nama_periode ?? 'Periode Pemilihan' }}
          </span>

          <span class="rounded-full border border-gray-200 bg-white px-3 py-1 text-gray-700">
            Status: {{ strtoupper($periode->status ?? '-') }}
          </span>
        @else
          <span class="rounded-full border border-amber-100 bg-amber-50 px-3 py-1 text-amber-700">
            Periode belum tersedia
          </span>
        @endif
      </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col gap-2 sm:flex-row">
      <form action="{{ route('admin.pemilihan.jalankan') }}" method="POST">
        @csrf
        <input type="hidden" name="periode_id" value="{{ $periodeId }}">
        <button type="submit"
                class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
          Jalankan Otomatis
        </button>
      </form>

      <form id="formCommit" method="POST" action="{{ route('admin.pemilihan.commit') }}">
        @csrf
        <input type="hidden" name="periode_id" value="{{ $periodeId }}">
        <button type="button"
                data-open-modal="modalCommit"
                class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
          Commit ke Rombel
        </button>
      </form>

      <form id="formReset" action="{{ route('admin.pemilihan.reset') }}" method="POST">
        @csrf
        <input type="hidden" name="periode_id" value="{{ $periodeId }}">
        <button type="button"
                data-open-modal="modalReset"
                class="w-full rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">
          Reset
        </button>
      </form>
    </div>
  </div>

  {{-- Petunjuk --}}
  <div class="rounded-2xl border border-blue-100 bg-blue-50 px-5 py-4 text-sm text-blue-800">
    Sistem menempatkan siswa berdasarkan pilihan utama terlebih dahulu. Jika kapasitas penuh,
    siswa akan dialihkan ke pilihan cadangan sesuai ketersediaan kuota.
  </div>

  {{-- Alert --}}
  @if(session('commit_success'))
    @php $commit = session('commit_success'); @endphp

    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <div class="font-semibold text-emerald-800">
            {{ $commit['message'] ?? 'Commit berhasil.' }}
          </div>
          <div class="mt-1 text-sm text-emerald-700">
            Langkah berikutnya, lengkapi nama rombel, wali kelas, ruang kelas, dan kapasitas rombel.
            Data ini juga dapat dilengkapi nanti melalui menu Master Data Rombel.
          </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
          <a href="{{ $commit['next_url'] ?? route('admin.pemilihan.rombel-final', ['periode_id' => $periodeId]) }}"
             class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
            Lanjut Setting Rombel
          </a>

          <button type="button"
                  onclick="this.closest('.rounded-2xl').remove()"
                  class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
            Atur Nanti
          </button>
        </div>
      </div>
    </div>
  @endif

  @if(session('ok'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
      {{ session('ok') }}
    </div>
  @endif

  @if(session('err'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      {{ session('err') }}
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <ul class="list-disc space-y-1 pl-5">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Ringkasan --}}
  <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
    <div class="rounded-xl border bg-white p-4">
      <div class="text-xs text-gray-500">Total Menu</div>
      <div class="mt-1 text-xl font-bold text-gray-800">{{ $menu->count() }}</div>
    </div>

    <div class="rounded-xl border bg-white p-4">
      <div class="text-xs text-gray-500">Total Kapasitas</div>
      <div class="mt-1 text-xl font-bold text-gray-800">{{ $totalKapasitas }}</div>
    </div>

    <div class="rounded-xl border bg-white p-4">
      <div class="text-xs text-gray-500">Total Pendaftar</div>
      <div class="mt-1 text-xl font-bold text-blue-600">{{ $totalPendaftar }}</div>
    </div>

    <div class="rounded-xl border bg-white p-4">
      <div class="text-xs text-gray-500">Sudah Diterima</div>
      <div class="mt-1 text-xl font-bold text-emerald-600">{{ $totalDiterima }}</div>
    </div>
  </div>

  {{-- Tabel Rekap --}}
  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
    <div class="border-b px-5 py-4">
      <h2 class="text-base font-semibold text-gray-800">Rekap Menu Rombel</h2>
      <p class="mt-1 text-sm text-gray-500">
        P1 adalah pilihan utama, sedangkan P2 adalah pilihan cadangan.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
          <tr>
            <th class="px-5 py-3 text-left">Menu</th>
            <th class="px-5 py-3 text-center">Kapasitas</th>
            <th class="px-5 py-3 text-center">P1</th>
            <th class="px-5 py-3 text-center">P2</th>
            <th class="px-5 py-3 text-center">Total</th>
            <th class="px-5 py-3 text-center">Diterima</th>
            <th class="px-5 py-3 text-center">Sisa</th>
            <th class="px-5 py-3 text-left">Mapel Pilihan</th>
            <th class="px-5 py-3 text-left">Rombel Final</th>
            <th class="px-5 py-3 text-right">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          @forelse($menu as $m)
            @php
              $r = $rekap[$m->id] ?? [
                'p1' => 0,
                'p2' => 0,
                'total' => 0,
                'diterima' => 0,
                'sisa' => $m->kapasitas_total,
              ];

              $linked = optional($rombels)->get($m->id) ?: collect();
              $isOver = (int)($r['total'] ?? 0) > (int)($m->kapasitas_total ?? 0);
            @endphp

            <tr class="hover:bg-gray-50">
              <td class="px-5 py-4 align-top">
                <div class="font-semibold text-gray-800">{{ $m->nama }}</div>
                <div class="mt-1 text-xs text-gray-500">
                  Tingkat: {{ $m->tingkat ?? 'XI' }}
                </div>

                @if($isOver)
                  <div class="mt-1 text-xs font-medium text-amber-600">
                    Peminat melebihi kapasitas
                  </div>
                @endif
              </td>

              <td class="px-5 py-4 text-center align-top">
                <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                  {{ number_format($m->kapasitas_total) }}
                </span>
              </td>

              <td class="px-5 py-4 text-center align-top">{{ $r['p1'] ?? 0 }}</td>
              <td class="px-5 py-4 text-center align-top">{{ $r['p2'] ?? 0 }}</td>
              <td class="px-5 py-4 text-center align-top font-semibold">{{ $r['total'] ?? 0 }}</td>
              <td class="px-5 py-4 text-center align-top font-semibold text-blue-600">{{ $r['diterima'] ?? 0 }}</td>

              <td class="px-5 py-4 text-center align-top">
                <span class="rounded-full px-3 py-1 text-xs font-semibold
                  {{ ($r['sisa'] ?? 0) > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
                  {{ $r['sisa'] ?? 0 }}
                </span>
              </td>

              <td class="px-5 py-4 align-top">
                <div class="flex max-w-xl flex-wrap gap-1.5">
                  @forelse($m->mapel as $mp)
                    <span class="rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs text-gray-700">
                      {{ $mp->nama_mapel }}
                    </span>
                  @empty
                    <span class="text-xs text-gray-400">Belum ada mapel</span>
                  @endforelse
                </div>
              </td>

              <td class="px-5 py-4 align-top">
                @if($linked->count())
                  <div class="flex flex-wrap gap-1.5">
                    @foreach($linked as $rb)
                      <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                        {{ $rb->nama_rombel }}
                      </span>
                    @endforeach
                  </div>
                @else
                  <span class="text-sm text-amber-600">Belum ada</span>
                @endif
              </td>

              <td class="px-5 py-4 text-right align-top">
                <a href="{{ route('admin.pemilihan.rombel-final', ['periode_id' => $periodeId]) }}"
                   class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                  Lihat Rombel
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="px-5 py-10 text-center text-sm text-gray-500">
                Belum ada menu rombel.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Daftar Siswa Belum Ditempatkan --}}
  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
    <div class="flex flex-col gap-2 border-b px-5 py-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h2 class="text-base font-semibold text-gray-800">Daftar Siswa Belum Ditempatkan</h2>
        <p class="mt-1 text-sm text-gray-500">
          Siswa berikut sudah memilih menu rombel, tetapi belum memiliki hasil penempatan.
          Setelah reset, data siswa akan kembali muncul di bagian ini.
        </p>
      </div>

      <span class="inline-flex w-fit rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
        {{ $belumCount }} siswa
      </span>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
          <tr>
            <th class="px-5 py-3 text-left">No</th>
            <th class="px-5 py-3 text-left">Nama Siswa</th>
            <th class="px-5 py-3 text-left">NIS</th>
            <th class="px-5 py-3 text-left">Pilihan Utama</th>
            <th class="px-5 py-3 text-left">Pilihan Cadangan</th>
            <th class="px-5 py-3 text-center">Status</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          @forelse($belum as $i => $row)
            <tr class="hover:bg-gray-50">
              <td class="px-5 py-4 text-gray-500">
                {{ $i + 1 }}
              </td>

              <td class="px-5 py-4">
                <div class="font-semibold text-gray-800">
                  {{ $row->siswa->nama ?? '-' }}
                </div>
              </td>

              <td class="px-5 py-4 text-gray-600">
                {{ $row->siswa->nis ?? '-' }}
              </td>

              <td class="px-5 py-4 text-gray-700">
                {{ $row->pilihan1->nama ?? '-' }}
              </td>

              <td class="px-5 py-4 text-gray-700">
                {{ $row->pilihan2->nama ?? '-' }}
              </td>

              <td class="px-5 py-4 text-center">
                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                  Belum ditempatkan
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">
                Tidak ada siswa yang belum ditempatkan.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

{{-- Modal Commit --}}
<div id="modalCommit"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
  <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
    <div class="p-6">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
      </div>

      <h3 class="mt-4 text-center text-lg font-bold text-gray-900">
        Commit ke Rombel?
      </h3>

      <p class="mt-2 text-center text-sm text-gray-500">
        Pastikan hasil penempatan sudah benar. Setelah commit, sistem akan membuat/memperbarui rombel final dan anggota siswa.
      </p>

      <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
        <button type="button"
                data-close-modal
                class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
          Batal
        </button>

        <button type="button"
                data-submit-form="formCommit"
                class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
          Ya, Commit
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Modal Reset --}}
<div id="modalReset"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
  <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
    <div class="p-6">
      <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-700">
        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
      </div>

      <h3 class="mt-4 text-center text-lg font-bold text-gray-900">
        Reset Hasil Penempatan?
      </h3>

      <p class="mt-2 text-center text-sm text-gray-500">
        Hasil penempatan akan dikosongkan. Pilihan utama dan cadangan siswa tetap tersimpan,
        sehingga siswa akan kembali tampil sebagai belum ditempatkan.
      </p>

      <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
        <button type="button"
                data-close-modal
                class="rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
          Batal
        </button>

        <button type="button"
                data-submit-form="formReset"
                class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
          Ya, Reset
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const openButtons = document.querySelectorAll('[data-open-modal]');
  const closeButtons = document.querySelectorAll('[data-close-modal]');
  const submitButtons = document.querySelectorAll('[data-submit-form]');

  function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
  }

  function closeModal(modal) {
    if (!modal) return;

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  openButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      openModal(btn.dataset.openModal);
    });
  });

  closeButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      closeModal(btn.closest('.fixed'));
    });
  });

  document.querySelectorAll('.fixed[id^="modal"]').forEach(function (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal) {
        closeModal(modal);
      }
    });
  });

  submitButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const form = document.getElementById(btn.dataset.submitForm);
      if (form) form.submit();
    });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.fixed[id^="modal"]').forEach(function (modal) {
        closeModal(modal);
      });
    }
  });
});
</script>
@endpush
@endsection