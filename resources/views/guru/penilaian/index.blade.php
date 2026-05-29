@extends('layouts.guru')

@section('content')
  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-800">Penilaian</h1>
    <p class="text-sm text-gray-500 mt-1">
      Daftar mata pelajaran yang diampu dan progres penilaian
      @if($taLabel || $semester)
        <span class="mx-1">•</span>
        {{ $taLabel ?? '-' }} / {{ ucfirst($semester ?? '-') }}
      @endif
    </p>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
      <div class="font-semibold mb-1">Terjadi kesalahan:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="bg-white rounded-lg shadow border overflow-hidden">
    <div class="px-4 py-3 border-b">
      <h2 class="font-semibold text-gray-800">Daftar Penilaian</h2>
      <p class="text-xs text-gray-500 mt-1">
        Pilih LM1 sampai LM4 untuk input manual, atau gunakan Excel untuk mengisi seluruh LM sekaligus.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-700">
          <tr>
            <th class="px-4 py-3 text-left">No</th>
            <th class="px-4 py-3 text-left">Hari / Jam</th>
            <th class="px-4 py-3 text-left">Mata Pelajaran</th>
            <th class="px-4 py-3 text-left">Rombel</th>
            <th class="px-4 py-3 text-center">Total Siswa</th>
            <th class="px-4 py-3 text-center">LM1</th>
            <th class="px-4 py-3 text-center">LM2</th>
            <th class="px-4 py-3 text-center">LM3</th>
            <th class="px-4 py-3 text-center">LM4</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-center min-w-[260px]">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y">
          @forelse($riwayat as $i => $item)
            @php
              $jadwalId = $item['jadwal_id'] ?? $item['id'] ?? null;
              $rombelId = $item['rombel_id'] ?? null;
              $mapelId = $item['mata_pelajaran_id'] ?? null;
              $isFinal = ($item['status'] ?? 'draft') === 'final';
            @endphp

            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">{{ $i + 1 }}</td>

              <td class="px-4 py-3">
                <div class="font-medium text-gray-800">{{ $item['hari'] ?? '-' }}</div>
                <div class="text-xs text-gray-500">{{ $item['jam'] ?? '-' }}</div>
              </td>

              <td class="px-4 py-3">
                <div class="font-medium text-gray-900">{{ $item['mapel'] ?? '-' }}</div>
              </td>

              <td class="px-4 py-3">{{ $item['rombel'] ?? '-' }}</td>

              <td class="px-4 py-3 text-center">{{ $item['total'] ?? 0 }}</td>

              <td class="px-4 py-3 text-center">
                <span class="{{ (($item['done']['LM1'] ?? 0) < ($item['total'] ?? 0)) ? 'text-amber-600' : 'text-green-600' }}">
                  {{ $item['done']['LM1'] ?? 0 }}/{{ $item['total'] ?? 0 }}
                </span>
              </td>

              <td class="px-4 py-3 text-center">
                <span class="{{ (($item['done']['LM2'] ?? 0) < ($item['total'] ?? 0)) ? 'text-amber-600' : 'text-green-600' }}">
                  {{ $item['done']['LM2'] ?? 0 }}/{{ $item['total'] ?? 0 }}
                </span>
              </td>

              <td class="px-4 py-3 text-center">
                <span class="{{ (($item['done']['LM3'] ?? 0) < ($item['total'] ?? 0)) ? 'text-amber-600' : 'text-green-600' }}">
                  {{ $item['done']['LM3'] ?? 0 }}/{{ $item['total'] ?? 0 }}
                </span>
              </td>

              <td class="px-4 py-3 text-center">
                <span class="{{ (($item['done']['LM4'] ?? 0) < ($item['total'] ?? 0)) ? 'text-amber-600' : 'text-green-600' }}">
                  {{ $item['done']['LM4'] ?? 0 }}/{{ $item['total'] ?? 0 }}
                </span>
              </td>

              <td class="px-4 py-3 text-center">
                @if($isFinal)
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                    Final
                  </span>
                @else
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
                    Draft
                  </span>
                @endif
              </td>

              <td class="px-4 py-3">
                <div class="flex flex-col gap-2 items-center">
                  <div class="flex flex-wrap gap-2 justify-center">
                    @foreach (['LM1', 'LM2', 'LM3', 'LM4'] as $lm)
                      <a href="{{ route('guru.penilaian.create', [
                          'rombel_id' => $rombelId,
                          'mata_pelajaran_id' => $mapelId,
                          'komponen' => $lm,
                      ]) }}"
                      class="px-3 py-1.5 rounded-md bg-indigo-600 text-white text-xs hover:bg-indigo-700">
                        {{ $lm }}
                      </a>
                    @endforeach
                  </div>

                  <div class="flex flex-wrap gap-2 justify-center border-t pt-2 mt-1">
                    <a href="{{ route('guru.penilaian.template-excel', [
                        'jadwal_id' => $jadwalId,
                        'rombel_id' => $rombelId,
                        'mata_pelajaran_id' => $mapelId,
                    ]) }}"
                    class="px-3 py-1.5 rounded-md bg-emerald-600 text-white text-xs hover:bg-emerald-700">
                      Download Excel
                    </a>

                    @if($isFinal)
                      <button type="button"
                              disabled
                              class="px-3 py-1.5 rounded-md bg-gray-300 text-gray-500 text-xs cursor-not-allowed">
                        Import Terkunci
                      </button>
                    @else
                      <button type="button"
                              onclick="openModalImportExcel(this)"
                              data-jadwal-id="{{ $jadwalId }}"
                              data-rombel-id="{{ $rombelId }}"
                              data-mapel-id="{{ $mapelId }}"
                              data-mapel="{{ $item['mapel'] ?? '-' }}"
                              data-rombel="{{ $item['rombel'] ?? '-' }}"
                              class="px-3 py-1.5 rounded-md bg-amber-500 text-white text-xs hover:bg-amber-600">
                        Import Excel
                      </button>
                    @endif
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="px-4 py-6 text-center text-gray-500">
                Belum ada jadwal mengajar.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- MODAL IMPORT EXCEL --}}
  <div id="modalImportExcel"
       class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">

    <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden">
      <div class="px-6 py-5 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          Import Nilai dari Excel
        </h3>

        <p class="mt-1 text-sm text-gray-500">
          Upload template Excel yang sudah diisi. File ini berisi LM1 sampai LM4 sekaligus.
        </p>
      </div>

      <form method="POST"
            action="{{ route('guru.penilaian.import-excel') }}"
            enctype="multipart/form-data"
            class="px-6 py-5 space-y-4">
        @csrf

        <input type="hidden" name="jadwal_id" id="importJadwalId">
        <input type="hidden" name="rombel_id" id="importRombelId">
        <input type="hidden" name="mata_pelajaran_id" id="importMapelId">

        <div class="rounded-lg bg-gray-50 border px-4 py-3 text-sm">
          <div class="text-gray-500">Kelas</div>
          <div id="importRombelLabel" class="font-semibold text-gray-900">-</div>

          <div class="text-gray-500 mt-2">Mata Pelajaran</div>
          <div id="importMapelLabel" class="font-semibold text-gray-900">-</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            File Excel
          </label>

          <input type="file"
                 name="file_excel"
                 accept=".xlsx,.xls"
                 required
                 class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">

          <p class="text-xs text-gray-500 mt-2">
            Gunakan file dari tombol Download Excel agar format sheet LM1 sampai LM4 sesuai.
          </p>
        </div>

        <div class="rounded-lg bg-yellow-50 border border-yellow-100 px-4 py-3 text-sm text-yellow-800">
          Nilai 0 pada Excel dianggap kosong/tidak digunakan. Sistem akan menghitung ulang nilai LM dan nilai akhir di server.
        </div>

        <div class="flex justify-end gap-2 border-t pt-4">
          <button type="button"
                  onclick="closeModalImportExcel()"
                  class="px-4 py-2 rounded-lg border bg-white text-gray-700 hover:bg-gray-50">
            Batal
          </button>

          <button type="submit"
                  class="px-4 py-2 rounded-lg bg-amber-500 text-white hover:bg-amber-600">
            Upload & Import
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModalImportExcel(button) {
      const modal = document.getElementById('modalImportExcel');

      if (!modal || !button) {
        return;
      }

      document.getElementById('importJadwalId').value = button.dataset.jadwalId || '';
      document.getElementById('importRombelId').value = button.dataset.rombelId || '';
      document.getElementById('importMapelId').value = button.dataset.mapelId || '';

      document.getElementById('importRombelLabel').textContent = button.dataset.rombel || '-';
      document.getElementById('importMapelLabel').textContent = button.dataset.mapel || '-';

      modal.classList.remove('hidden');
      modal.classList.add('flex');
      document.body.classList.add('overflow-hidden');
    }

    function closeModalImportExcel() {
      const modal = document.getElementById('modalImportExcel');

      if (!modal) {
        return;
      }

      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeModalImportExcel();
      }
    });

    document.getElementById('modalImportExcel')?.addEventListener('click', function(e) {
      if (e.target === this) {
        closeModalImportExcel();
      }
    });
  </script>
@endsection