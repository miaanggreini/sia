@extends('layouts.admin')
@section('title', 'Setting Rombel Final')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Setting Rombel Final</h1>
      <p class="mt-1 text-sm text-gray-500">
        Atur nama rombel, wali kelas, dan ruang kelas untuk rombel hasil penempatan.
      </p>

      <div class="mt-2 flex flex-wrap gap-2 text-sm">
        <span class="rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-indigo-700">
          TA: {{ $ta->nama_tahun ?? $ta->label ?? '-' }}
        </span>

        <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-emerald-700">
          Periode: {{ $periode->nama_periode ?? '-' }}
        </span>
      </div>
    </div>

    <a href="{{ route('admin.pemilihan.rekap', ['periode_id' => $periode->id]) }}"
       class="rounded-xl border bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
      Kembali
    </a>
  </div>

  @if(session('ok'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
      {{ session('ok') }}
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.pemilihan.rombel-final.simpan') }}">
    @csrf
    <input type="hidden" name="periode_id" value="{{ $periode->id }}">

    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
      <div class="border-b px-5 py-4">
        <h2 class="text-base font-semibold text-gray-800">Daftar Rombel Final</h2>
        <p class="mt-1 text-sm text-gray-500">
          Nama awal rombel dari sistem dapat diubah menjadi format sekolah, misalnya XI MIPA A, XI IPS A, atau XI Bahasa A.
        </p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
            <tr>
              <th class="px-5 py-3 text-left">Menu</th>
              <th class="px-5 py-3 text-left">Nama Rombel</th>
              <th class="px-5 py-3 text-left">Wali Kelas</th>
              <th class="px-5 py-3 text-left">Ruang Kelas</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @forelse($rombels as $i => $rombel)
              <tr>
                <td class="px-5 py-4 align-top">
                  <div class="font-semibold text-gray-800">
                    {{ $rombel->menuRombel->nama ?? '-' }}
                  </div>
                  <div class="mt-1 text-xs text-gray-500">
                    Rombel hasil commit
                  </div>

                  <input type="hidden" name="rombels[{{ $i }}][id]" value="{{ $rombel->id }}">
                </td>

                <td class="px-5 py-4 align-top">
                  <input type="text"
                         name="rombels[{{ $i }}][nama_rombel]"
                         value="{{ old("rombels.$i.nama_rombel", $rombel->nama_rombel) }}"
                         class="w-full min-w-[220px] rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-100"
                         required>

                  <p class="mt-1 text-xs text-gray-500">
                    Contoh: XI MIPA A
                  </p>
                </td>

                <td class="px-5 py-4 align-top">
                  <select name="rombels[{{ $i }}][guru_id]"
                          class="w-full min-w-[240px] rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-100">
                    <option value="">— Pilih Wali —</option>

                    @foreach($waliKelas as $guru)
                      <option value="{{ $guru->id }}"
                        @selected(old("rombels.$i.guru_id", $rombel->guru_id) == $guru->id)>
                        {{ $guru->nama }}
                      </option>
                    @endforeach
                  </select>

                  <p class="mt-1 text-xs text-gray-500">
                    Wali kelas dapat diatur sekarang atau nanti.
                  </p>
                </td>

                <td class="px-5 py-4 align-top">
                  <select name="rombels[{{ $i }}][ruang_kelas_id]"
                          class="w-full min-w-[240px] rounded-xl border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-100">
                    <option value="">— Pilih Ruang —</option>

                    @foreach($ruangKelas as $ruang)
                      @php
                        $namaRuang = $ruang->nama ?? $ruang->nama_ruang ?? '-';
                      @endphp

                      <option value="{{ $ruang->id }}"
                              @selected(old("rombels.$i.ruang_kelas_id", $rombel->ruang_kelas_id) == $ruang->id)>
                        {{ $namaRuang }}
                      </option>
                    @endforeach
                  </select>

                  <p class="mt-1 text-xs text-gray-500">
                    Pilih ruang kelas yang digunakan oleh rombel final.
                  </p>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">
                  Belum ada rombel final. Jalankan commit ke rombel terlebih dahulu.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-5 flex justify-end gap-3">
      <a href="{{ route('admin.pemilihan.rekap', ['periode_id' => $periode->id]) }}"
         class="rounded-xl border bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
        Batal
      </a>

      <button type="submit"
              class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
        Simpan Pengaturan
      </button>
    </div>
  </form>
</div>
@endsection