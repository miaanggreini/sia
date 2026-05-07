{{-- resources/views/admin/ekskul/anggota.blade.php --}}
@extends('layouts.admin')
@section('title','Anggota Ekskul')

@section('content')
  <div class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold">Anggota Ekskul</h1>
      <p class="text-sm text-gray-500">
        Daftar anggota ekskul <span class="font-semibold text-gray-900">{{ $ekskul->nama }}</span>
        @if($tahunDipilih)
          – Tahun ajaran {{ $tahunDipilih->nama }}
        @endif
      </p>
    </div>

    <a href="{{ route('admin.ekskul.index') }}"
       class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd"
              d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
              clip-rule="evenodd" />
      </svg>
      <span>Kembali</span>
    </a>
  </div>

  <div class="mb-4 rounded-xl border bg-white p-4 shadow-sm">
    <div class="grid gap-4 md:grid-cols-3 text-sm">
      <div>
        <div class="text-xs text-gray-500">Nama Ekskul</div>
        <div class="font-semibold text-gray-900">{{ $ekskul->nama }}</div>
      </div>
      <div>
        <div class="text-xs text-gray-500">Pembina</div>
        <div class="font-semibold text-gray-900">
          {{ $ekskul->pembina->nama ?? '-' }}
        </div>
      </div>
      <div>
        <div class="text-xs text-gray-500">Jadwal</div>
        <div class="text-gray-900">
          @if($ekskul->hari)
            {{ $ekskul->hari }},
          @endif
          @if($ekskul->jam_mulai || $ekskul->jam_selesai)
            {{ $ekskul->jam_mulai ? \Illuminate\Support\Str::substr($ekskul->jam_mulai, 0, 5) : '–' }}
            -
            {{ $ekskul->jam_selesai ? \Illuminate\Support\Str::substr($ekskul->jam_selesai, 0, 5) : '–' }}
            &bull;
          @endif
          {{ $ekskul->lokasi ?? '-' }}
        </div>
      </div>
    </div>
  </div>

  {{-- FILTER TAHUN AJARAN --}}
  <div class="mb-4 flex items-center justify-between gap-2">
    <form method="GET" class="flex items-center gap-2">
      <label class="text-sm text-gray-600">Tahun ajaran</label>
      <select name="tahun_ajaran_id"
              class="rounded-lg border border-gray-300 px-2 py-1 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              onchange="this.form.submit()">
        <option value="">Semua</option>
        @foreach($tahunList as $t)
          <option value="{{ $t->id }}"
            {{ $tahunDipilih && $tahunDipilih->id == $t->id ? 'selected' : '' }}>
            {{ $t->nama }} {{ $t->status === 'aktif' ? '(aktif)' : '' }}
          </option>
        @endforeach
      </select>
    </form>

    <div class="text-xs text-gray-500">
      Total anggota:
      <span class="font-semibold text-gray-900">{{ $anggota->count() }}</span>
    </div>
  </div>

  <div class="rounded-2xl border bg-white p-4 shadow-sm">
    @if($anggota->count())
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
          <tr class="border-b bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
            <th class="px-3 py-2 text-left">No</th>
            <th class="px-3 py-2 text-left">NIS / NISN</th>
            <th class="px-3 py-2 text-left">Nama Siswa</th>
            <th class="px-3 py-2 text-left">Status</th>
            <th class="px-3 py-2 text-left">Tgl Gabung</th>
            <th class="px-3 py-2 text-left">Tgl Keluar</th>
          </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
          @foreach($anggota as $i => $row)
            @php $s = $row->siswa; @endphp
            <tr>
              <td class="px-3 py-2 align-top text-xs text-gray-500">
                {{ $i + 1 }}
              </td>
              <td class="px-3 py-2 align-top">
                {{ $s?->nisn ?? $s?->nis ?? '-' }}
              </td>
              <td class="px-3 py-2 align-top">
                {{ $s?->nama ?? '-' }}
              </td>
              <td class="px-3 py-2 align-top">
                <span class="inline-flex rounded-full px-3 py-1 text-xs
                    {{ $row->status === 'aktif'
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : 'bg-gray-50 text-gray-600 border border-gray-200' }}">
                  {{ ucfirst($row->status) }}
                </span>
              </td>
              <td class="px-3 py-2 align-top">
                {{ $row->tanggal_gabung ?? '-' }}
              </td>
              <td class="px-3 py-2 align-top">
                {{ $row->tanggal_keluar ?? '-' }}
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="py-8 text-center text-sm text-gray-500">
        Belum ada anggota yang terdaftar pada tahun ajaran ini.
      </div>
    @endif
  </div>
@endsection
