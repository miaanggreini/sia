@extends('layouts.kepsek')

@section('content')
<div class="w-full">
  <h1 class="text-2xl font-semibold mb-6">Ubah Jadwal</h1>

  @if ($errors->any())
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <ul class="list-disc list-inside">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('kepala_sekolah.data.jadwal.update', $jadwal) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-2xl shadow border">
      <div class="px-5 py-4 border-b">
        <h2 class="font-medium text-gray-800">Identitas Jadwal</h2>
      </div>

      <div class="px-5 pb-5 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Rombel</label>
          <select name="rombel_id" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
            <option value="">— Pilih Rombel —</option>
            @foreach($daftarRombel as $r)
              <option value="{{ $r->id }}" @selected(old('rombel_id', $jadwal->rombel_id) == $r->id)>
                {{ $r->nama_rombel }}{{ $r->tingkat ? " ($r->tingkat)" : '' }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
          <select name="mata_pelajaran_id" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
            <option value="">— Pilih Mapel —</option>
            @foreach(($mapelRombel ?? collect()) as $m)
              <option value="{{ $m->id }}" @selected(old('mata_pelajaran_id', $jadwal->mata_pelajaran_id) == $m->id)>
                {{ $m->nama_mapel }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Guru</label>
          <select name="guru_id" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
            <option value="">— Pilih Guru —</option>
            @foreach($daftarGuru as $g)
              <option value="{{ $g->id }}" @selected(old('guru_id', $jadwal->guru_id) == $g->id)>
                {{ $g->nama }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow border">
      <div class="px-5 py-4 border-b">
        <h2 class="font-medium text-gray-800">Waktu & Slot</h2>
      </div>

      <div class="px-5 pb-5 grid grid-cols-1 lg:grid-cols-12 gap-4">
        <div class="lg:col-span-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">Hari</label>
          <select name="hari" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
            @foreach($hariOptions as $h)
              <option value="{{ $h }}" @selected(old('hari', $jadwal->hari) == $h)>{{ $h }}</option>
            @endforeach
          </select>
        </div>

        <div class="lg:col-span-3">
          <label class="block text-sm font-medium text-gray-700 mb-1">Slot Mulai (JP)</label>
          <select name="slot_kode" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
            <option value="">— Pilih Slot —</option>
            @foreach($slotOptions as $kode => $arr)
              <option value="{{ $kode }}" @selected(old('slot_kode', $jadwal->slot_kode) == $kode)>
                {{ $arr[0] }} ({{ $arr[1] }}–{{ $arr[2] }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="lg:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (JP)</label>
          <select name="durasi_jp" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-indigo-500" required>
            @for($i = 1; $i <= 8; $i++)
              <option value="{{ $i }}" @selected(old('durasi_jp', $jadwal->durasi_jp ?? 1) == $i)>{{ $i }} JP</option>
            @endfor
          </select>
        </div>

        <div class="lg:col-span-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Jam Saat Ini</label>
          <div class="grid grid-cols-2 gap-2">
            <input type="text"
                   class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-700"
                   value="{{ $jadwal->jam_mulai ? substr($jadwal->jam_mulai,0,5) : '-' }}"
                   readonly>
            <input type="text"
                   class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-700"
                   value="{{ $jadwal->jam_selesai ? substr($jadwal->jam_selesai,0,5) : '-' }}"
                   readonly>
          </div>
        </div>
      </div>
    </div>

    <div class="flex items-center justify-end gap-3">
      <a href="{{ route('kepala_sekolah.data.jadwal') }}"
         class="px-4 py-2 rounded-xl border text-gray-700 hover:bg-gray-50">
        Batal
      </a>
      <button type="submit"
              class="px-5 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
        Simpan
      </button>
    </div>
  </form>
</div>
@endsection