{{-- resources/views/admin/siswa/_form.blade.php --}}
@if(($mode ?? 'form') !== 'show')
  @csrf
@endif

@php
  $mode   = $mode ?? (request()->routeIs('admin.siswa.show') ? 'show' : 'form');
  $isShow = $mode === 'show';
  $tabbed = $isShow;

  $fotoPath = $siswa->foto
      ? asset('storage/' . ltrim($siswa->foto, '/'))
      : null;
@endphp

<div
  @if($tabbed)
    x-data="{
      tab: localStorage.getItem('tabDetailSiswa') || 'pribadi',
      setTab(t){ this.tab=t; localStorage.setItem('tabDetailSiswa', t) }
    }"
  @endif
  class="bg-white rounded-2xl shadow border overflow-hidden"
>
  @if($tabbed)
    <div class="flex flex-wrap bg-gradient-to-r from-blue-600 to-indigo-500 text-white text-sm font-medium">
      @php
        $tabs = [
          'pribadi'  => 'Data Pribadi',
          'kontak'   => 'Kontak & Alamat',
          'akademik' => 'Akademik',
          'ayah'     => 'Data Ayah',
          'ibu'      => 'Data Ibu'
        ];
      @endphp
      @foreach($tabs as $k => $label)
        <button type="button"
          @click="setTab('{{ $k }}')"
          :class="tab==='{{ $k }}' ? 'bg-white text-blue-700 shadow-inner' : 'text-white hover:bg-blue-500/30'"
          class="px-5 py-3 transition-all duration-200 border-r border-blue-500 last:border-0">
          {{ $label }}
        </button>
      @endforeach
    </div>
  @endif

  <div class="p-6 bg-gray-50">

    {{-- ===================== Data Pribadi ===================== --}}
    <section @if($tabbed) x-show="tab==='pribadi'" x-transition x-cloak @endif class="space-y-5">
      <h2 class="text-lg font-bold text-blue-800">Data Pribadi</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
        {{-- Foto --}}
        <div class="space-y-3">
          <label class="block text-sm font-medium text-gray-700">Foto Siswa</label>

          <div class="p-4 border border-blue-200 rounded-xl bg-white shadow-sm inline-block">
            <div id="fotoPreviewWrapper"
                 class="h-48 w-48 rounded-lg border border-blue-100 shadow-sm mb-3 overflow-hidden bg-gray-100 flex items-center justify-center text-gray-400 text-sm">
              @if($fotoPath)
                <img id="fotoPreview"
                     src="{{ $fotoPath }}"
                     class="h-full w-full object-cover"
                     alt="">
                <span id="fotoPlaceholder" class="hidden">Belum ada foto</span>
              @else
                <img id="fotoPreview"
                     src=""
                     class="h-full w-full object-cover hidden"
                     alt="">
                <span id="fotoPlaceholder">Belum ada foto</span>
              @endif
            </div>

            @unless($isShow)
              <input
                id="foto"
                name="foto"
                type="file"
                accept=".jpg,.jpeg,.png,.webp"
                class="block w-full max-w-[300px] text-sm rounded-md p-2 border @error('foto') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
              >
              <p class="mt-2 text-xs text-gray-500">
                Format: JPG/PNG/WEBP. Maksimal ukuran 2 MB.
              </p>
              <p id="foto_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
              @error('foto')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            @endunless
          </div>
        </div>

        {{-- Kolom kanan --}}
        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
            <input id="nama" name="nama" type="text"
              value="{{ old('nama', $siswa->nama) }}"
              {{ $isShow ? 'readonly disabled' : 'required' }}
              autocomplete="name"
              minlength="3"
              maxlength="120"
              pattern="^[A-Za-zÀ-ÿ\s'.-]+$"
              title="Nama hanya boleh huruf, spasi, titik, petik, dan tanda hubung."
              class="block mt-1 w-full rounded-md @error('nama') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
            <p id="nama_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
            @error('nama')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="tempat_lahir" class="block text-sm font-medium text-gray-700">Tempat Lahir <span class="text-red-500">*</span></label>
            <input id="tempat_lahir" name="tempat_lahir" type="text"
              value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}"
              {{ $isShow ? 'readonly disabled' : 'required' }}
              minlength="2"
              maxlength="100"
              pattern="^[A-Za-zÀ-ÿ\s'.-]+$"
              title="Tempat lahir hanya boleh huruf dan spasi."
              class="block mt-1 w-full rounded-md @error('tempat_lahir') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
            <p id="tempat_lahir_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
            @error('tempat_lahir')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700">Jenis Kelamin <span class="text-red-500">*</span></label>
            @php $v = old('jenis_kelamin', $siswa->jenis_kelamin) @endphp
            <select id="jenis_kelamin" name="jenis_kelamin"
              class="mt-1 block w-full rounded-md @error('jenis_kelamin') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
              {{ $isShow ? 'disabled' : 'required' }}>
              <option value="">-- Pilih --</option>
              <option value="L" @selected($v === 'L')>Laki-laki</option>
              <option value="P" @selected($v === 'P')>Perempuan</option>
            </select>
            <p id="jenis_kelamin_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
            @error('jenis_kelamin')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700">Tanggal Lahir <span class="text-red-500">*</span></label>
            <input id="tanggal_lahir" name="tanggal_lahir" type="date"
              value="{{ old('tanggal_lahir', optional($siswa->tanggal_lahir)->format('Y-m-d')) }}"
              {{ $isShow ? 'readonly disabled' : 'required' }}
              class="block mt-1 w-full rounded-md @error('tanggal_lahir') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
            <p id="tanggal_lahir_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
            @error('tanggal_lahir')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="nisn" class="block text-sm font-medium text-gray-700">NISN <span class="text-red-500">*</span></label>
            <input id="nisn" name="nisn" type="text"
              value="{{ old('nisn', $siswa->nisn) }}"
              {{ $isShow ? 'readonly disabled' : 'required' }}
              inputmode="numeric"
              autocomplete="off"
              maxlength="10"
              minlength="10"
              pattern="^\d{10}$"
              title="NISN harus 10 digit angka."
              class="block mt-1 w-full rounded-md @error('nisn') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
            <p id="nisn_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
            @error('nisn')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="nis" class="block text-sm font-medium text-gray-700">NIS <span class="text-red-500">*</span></label>
            <input id="nis" name="nis" type="text"
              value="{{ old('nis', $siswa->nis) }}"
              {{ $isShow ? 'readonly disabled' : 'required' }}
              inputmode="numeric"
              autocomplete="off"
              maxlength="20"
              pattern="^\d+$"
              title="NIS harus angka."
              class="block mt-1 w-full rounded-md @error('nis') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
            <p id="nis_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
            @error('nis')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>
        </div>
      </div>
    </section>

    {{-- ===================== Kontak & Alamat ===================== --}}
    <section @if($tabbed) x-show="tab==='kontak'" x-transition x-cloak @endif class="space-y-5 mt-6">
      <h2 class="text-lg font-bold text-blue-800">Kontak & Alamat</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label for="agama" class="block text-sm font-medium text-gray-700">Agama <span class="text-red-500">*</span></label>
          @php $ag = old('agama', $siswa->agama) @endphp
          <select id="agama" name="agama"
            class="mt-1 block w-full rounded-md @error('agama') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
            {{ $isShow ? 'disabled' : 'required' }}>
            <option value="">-- Pilih --</option>
            @foreach (['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'] as $opt)
              <option value="{{ $opt }}" @selected($ag === $opt)>{{ $opt }}</option>
            @endforeach
          </select>
          <p id="agama_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('agama')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="no_hp" class="block text-sm font-medium text-gray-700">Nomor HP <span class="text-red-500">*</span></label>
          <input id="no_hp" name="no_hp" type="text"
            value="{{ old('no_hp', $siswa->no_hp) }}"
            {{ $isShow ? 'readonly disabled' : 'required' }}
            inputmode="numeric"
            autocomplete="tel"
            maxlength="15"
            pattern="^(\+62|62|0)?8[0-9]{7,11}$"
            title="Nomor HP tidak valid. Contoh: 081234567890"
            class="mt-1 block w-full rounded-md @error('no_hp') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
          <p id="no_hp_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('no_hp')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
          <input id="email" name="email" type="email"
            value="{{ old('email', $siswa->email) }}"
            {{ $isShow ? 'readonly disabled' : 'required' }}
            autocomplete="email"
            class="mt-1 block w-full rounded-md @error('email') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
          <p id="email_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div class="md:col-span-3">
          <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat Lengkap <span class="text-red-500">*</span></label>
          <textarea id="alamat" name="alamat" rows="3"
            class="mt-1 block w-full rounded-md @error('alamat') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
            {{ $isShow ? 'readonly disabled' : 'required' }}>{{ old('alamat', $siswa->alamat) }}</textarea>
          <p id="alamat_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('alamat')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>
      </div>
    </section>

    {{-- ===================== Akademik ===================== --}}
    <section @if($tabbed) x-show="tab==='akademik'" x-transition x-cloak @endif class="space-y-5 mt-6">
      <h2 class="text-lg font-bold text-blue-800">Akademik</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label for="jalur_penerimaan" class="block text-sm font-medium text-gray-700">Jalur Penerimaan <span class="text-red-500">*</span></label>
          @php $jp = old('jalur_penerimaan', $siswa->jalur_penerimaan) @endphp
          <select id="jalur_penerimaan" name="jalur_penerimaan"
            class="mt-1 block w-full rounded-md @error('jalur_penerimaan') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
            {{ $isShow ? 'disabled' : 'required' }}>
            <option value="">-- Pilih --</option>
            @foreach (['Afirmasi','Mutasi','Prestasi','Domisili Khusus','Domisili Reguler'] as $opt)
              <option value="{{ $opt }}" @selected($jp === $opt)>{{ $opt }}</option>
            @endforeach
          </select>
          <p id="jalur_penerimaan_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('jalur_penerimaan')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="kebutuhan_khusus" class="block text-sm font-medium text-gray-700">Kebutuhan Khusus <span class="text-red-500">*</span></label>
          @php $kk = old('kebutuhan_khusus', $siswa->kebutuhan_khusus) @endphp
          <select id="kebutuhan_khusus" name="kebutuhan_khusus"
            class="mt-1 block w-full rounded-md @error('kebutuhan_khusus') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
            {{ $isShow ? 'disabled' : 'required' }}>
            <option value="">-- Pilih --</option>
            <option value="Ya" @selected($kk === 'Ya')>Ya</option>
            <option value="Tidak" @selected($kk === 'Tidak')>Tidak</option>
          </select>
          <p id="kebutuhan_khusus_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('kebutuhan_khusus')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="tahun_masuk" class="block text-sm font-medium text-gray-700">Tahun Masuk <span class="text-red-500">*</span></label>
          <select id="tahun_masuk" name="tahun_masuk"
            class="mt-1 block w-full rounded-md @error('tahun_masuk') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
            {{ $isShow ? 'disabled' : 'required' }}>
            <option value="">-- Pilih Tahun --</option>
            @for ($y = date('Y'); $y >= 2000; $y--)
              <option value="{{ $y }}" @selected(old('tahun_masuk', $siswa->tahun_masuk) == $y)>{{ $y }}</option>
            @endfor
          </select>
          <p id="tahun_masuk_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('tahun_masuk')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Status Siswa <span class="text-red-500">*</span>
    </label>

    <select name="status"
        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        
        <option value="">-- Pilih Status --</option>

        <option value="aktif" {{ old('status', $siswa->status) == 'aktif' ? 'selected' : '' }}>
            Aktif
        </option>

        <option value="pindah" {{ old('status', $siswa->status) == 'pindah' ? 'selected' : '' }}>
            Pindah
        </option>

        <option value="keluar" {{ old('status', $siswa->status) == 'keluar' ? 'selected' : '' }}>
            Keluar
        </option>
    </select>

    @error('status')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror

    <p class="text-xs text-gray-500 mt-2">
        Status <b>Lulus</b> hanya ditentukan melalui proses kelulusan pada menu rombel.
    </p>
</div>
      </div>
    </section>

    {{-- ===================== Data Ayah ===================== --}}
    <section @if($tabbed) x-show="tab==='ayah'" x-cloak @endif class="space-y-4 mt-6">
      <h2 class="text-lg font-bold text-blue-800">Data Ayah</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label for="nama_ayah" class="block text-sm font-medium text-gray-700">Nama Ayah <span class="text-red-500">*</span></label>
          <input id="nama_ayah" name="nama_ayah" type="text"
            value="{{ old('nama_ayah', $siswa->nama_ayah) }}"
            {{ $isShow ? 'readonly disabled' : 'required' }}
            pattern="^[A-Za-zÀ-ÿ\s'.-]+$"
            title="Nama Ayah hanya boleh huruf dan spasi."
            class="block mt-1 w-full rounded-md @error('nama_ayah') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
          <p id="nama_ayah_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('nama_ayah')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="nik_ayah" class="block text-sm font-medium text-gray-700">NIK Ayah <span class="text-red-500">*</span></label>
          <input id="nik_ayah" name="nik_ayah" type="text"
            value="{{ old('nik_ayah', $siswa->nik_ayah) }}"
            {{ $isShow ? 'readonly disabled' : 'required' }}
            inputmode="numeric"
            minlength="16"
            maxlength="16"
            pattern="^\d{16}$"
            title="NIK Ayah harus 16 digit angka."
            class="block mt-1 w-full rounded-md @error('nik_ayah') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
          <p id="nik_ayah_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('nik_ayah')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="status_ayah" class="block text-sm font-medium text-gray-700">Status Ayah <span class="text-red-500">*</span></label>
          @php $stAyah = old('status_ayah', $siswa->status_ayah) @endphp
          <select id="status_ayah" name="status_ayah"
            class="w-full rounded-md @error('status_ayah') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
            {{ $isShow ? 'disabled' : 'required' }}>
            <option value="">-- Pilih --</option>
            <option value="hidup" @selected($stAyah === 'hidup')>Hidup</option>
            <option value="meninggal" @selected($stAyah === 'meninggal')>Meninggal</option>
            <option value="tidak diketahui" @selected($stAyah === 'tidak diketahui')>Tidak diketahui</option>
          </select>
          <p id="status_ayah_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('status_ayah')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="pekerjaan_ayah" class="block text-sm font-medium text-gray-700">Pekerjaan Ayah <span class="text-red-500">*</span></label>
          <input id="pekerjaan_ayah" name="pekerjaan_ayah" type="text"
            value="{{ old('pekerjaan_ayah', $siswa->pekerjaan_ayah) }}"
            {{ $isShow ? 'readonly disabled' : 'required' }}
            class="w-full mt-1 rounded-md @error('pekerjaan_ayah') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
          <p id="pekerjaan_ayah_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('pekerjaan_ayah')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="pendidikan_ayah" class="block text-sm font-medium text-gray-700">Pendidikan Ayah <span class="text-red-500">*</span></label>
          @php $pendAyah = old('pendidikan_ayah', $siswa->pendidikan_ayah) @endphp
          <select id="pendidikan_ayah" name="pendidikan_ayah"
            class="w-full rounded-md @error('pendidikan_ayah') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
            {{ $isShow ? 'disabled' : 'required' }}>
            <option value="">-- Pilih --</option>
            @foreach (['Tidak sekolah','SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3'] as $opt)
              <option value="{{ $opt }}" @selected($pendAyah === $opt)>{{ $opt }}</option>
            @endforeach
          </select>
          <p id="pendidikan_ayah_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('pendidikan_ayah')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="no_hp_ayah" class="block text-sm font-medium text-gray-700">No HP Ayah <span class="text-red-500">*</span></label>
          <input id="no_hp_ayah" name="no_hp_ayah" type="text"
            value="{{ old('no_hp_ayah', $siswa->no_hp_ayah) }}"
            {{ $isShow ? 'readonly disabled' : 'required' }}
            inputmode="numeric"
            maxlength="15"
            pattern="^(\+62|62|0)?8[0-9]{7,11}$"
            title="No HP Ayah tidak valid. Contoh: 081234567890"
            class="w-full mt-1 rounded-md @error('no_hp_ayah') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
          <p id="no_hp_ayah_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('no_hp_ayah')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div class="md:col-span-3">
          <label for="alamat_ayah" class="block text-sm font-medium text-gray-700">Alamat Ayah <span class="text-red-500">*</span></label>
          <textarea id="alamat_ayah" name="alamat_ayah" rows="2"
            class="w-full rounded-md @error('alamat_ayah') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
            {{ $isShow ? 'readonly disabled' : 'required' }}>{{ old('alamat_ayah', $siswa->alamat_ayah) }}</textarea>
          <p id="alamat_ayah_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('alamat_ayah')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>
      </div>
    </section>

    {{-- ===================== Data Ibu ===================== --}}
    <section @if($tabbed) x-show="tab==='ibu'" x-cloak @endif class="space-y-4 mt-6">
      <h2 class="text-lg font-bold text-blue-800">Data Ibu</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label for="nama_ibu" class="block text-sm font-medium text-gray-700">Nama Ibu <span class="text-red-500">*</span></label>
          <input id="nama_ibu" name="nama_ibu" type="text"
            value="{{ old('nama_ibu', $siswa->nama_ibu) }}"
            {{ $isShow ? 'readonly disabled' : 'required' }}
            pattern="^[A-Za-zÀ-ÿ\s'.-]+$"
            title="Nama Ibu hanya boleh huruf dan spasi."
            class="block mt-1 w-full rounded-md @error('nama_ibu') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
          <p id="nama_ibu_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('nama_ibu')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="nik_ibu" class="block text-sm font-medium text-gray-700">NIK Ibu <span class="text-red-500">*</span></label>
          <input id="nik_ibu" name="nik_ibu" type="text"
            value="{{ old('nik_ibu', $siswa->nik_ibu) }}"
            {{ $isShow ? 'readonly disabled' : 'required' }}
            inputmode="numeric"
            minlength="16"
            maxlength="16"
            pattern="^\d{16}$"
            title="NIK Ibu harus 16 digit angka."
            class="block mt-1 w-full rounded-md @error('nik_ibu') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
          <p id="nik_ibu_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('nik_ibu')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="status_ibu" class="block text-sm font-medium text-gray-700">Status Ibu <span class="text-red-500">*</span></label>
          @php $stIbu = old('status_ibu', $siswa->status_ibu) @endphp
          <select id="status_ibu" name="status_ibu"
            class="w-full rounded-md @error('status_ibu') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
            {{ $isShow ? 'disabled' : 'required' }}>
            <option value="">-- Pilih --</option>
            <option value="hidup" @selected($stIbu === 'hidup')>Hidup</option>
            <option value="meninggal" @selected($stIbu === 'meninggal')>Meninggal</option>
            <option value="tidak diketahui" @selected($stIbu === 'tidak diketahui')>Tidak diketahui</option>
          </select>
          <p id="status_ibu_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('status_ibu')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="pekerjaan_ibu" class="block text-sm font-medium text-gray-700">Pekerjaan Ibu <span class="text-red-500">*</span></label>
          <input id="pekerjaan_ibu" name="pekerjaan_ibu" type="text"
            value="{{ old('pekerjaan_ibu', $siswa->pekerjaan_ibu) }}"
            {{ $isShow ? 'readonly disabled' : 'required' }}
            class="w-full mt-1 rounded-md @error('pekerjaan_ibu') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
          <p id="pekerjaan_ibu_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('pekerjaan_ibu')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="pendidikan_ibu" class="block text-sm font-medium text-gray-700">Pendidikan Ibu <span class="text-red-500">*</span></label>
          @php $pendIbu = old('pendidikan_ibu', $siswa->pendidikan_ibu) @endphp
          <select id="pendidikan_ibu" name="pendidikan_ibu"
            class="w-full rounded-md @error('pendidikan_ibu') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
            {{ $isShow ? 'disabled' : 'required' }}>
            <option value="">-- Pilih --</option>
            @foreach (['Tidak sekolah','SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3'] as $opt)
              <option value="{{ $opt }}" @selected($pendIbu === $opt)>{{ $opt }}</option>
            @endforeach
          </select>
          <p id="pendidikan_ibu_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('pendidikan_ibu')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="no_hp_ibu" class="block text-sm font-medium text-gray-700">No HP Ibu <span class="text-red-500">*</span></label>
          <input id="no_hp_ibu" name="no_hp_ibu" type="text"
            value="{{ old('no_hp_ibu', $siswa->no_hp_ibu) }}"
            {{ $isShow ? 'readonly disabled' : 'required' }}
            inputmode="numeric"
            maxlength="15"
            pattern="^(\+62|62|0)?8[0-9]{7,11}$"
            title="No HP Ibu tidak valid. Contoh: 081234567890"
            class="w-full mt-1 rounded-md @error('no_hp_ibu') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror">
          <p id="no_hp_ibu_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('no_hp_ibu')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div class="md:col-span-3">
          <label for="alamat_ibu" class="block text-sm font-medium text-gray-700">Alamat Ibu <span class="text-red-500">*</span></label>
          <textarea id="alamat_ibu" name="alamat_ibu" rows="2"
            class="w-full rounded-md @error('alamat_ibu') border-red-500 focus:border-red-500 focus:ring-red-300 @else border-gray-300 focus:border-blue-400 focus:ring-blue-300 @enderror"
            {{ $isShow ? 'readonly disabled' : 'required' }}>{{ old('alamat_ibu', $siswa->alamat_ibu) }}</textarea>
          <p id="alamat_ibu_inline_error" class="mt-1 text-sm text-red-600 hidden"></p>
          @error('alamat_ibu')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>
      </div>
    </section>

{{-- ===== Tombol Aksi ===== --}}
<div class="flex items-center gap-3 pt-6">
  @if(!$isShow)
    <button id="btnSubmit" type="submit"
      class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 transition">
      Simpan
    </button>

    <a href="{{ $backUrl ?? route('admin.siswa.index') }}"
      class="px-6 py-2 border border-gray-300 bg-white text-gray-700 rounded-md shadow-sm hover:bg-gray-100 transition">
      Batal
    </a>
  @else
    <a href="{{ $backUrl ?? route('admin.siswa.index') }}"
      class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
      Kembali
    </a>
  @endif
</div>


  </div>
</div>

@push('scripts')
@if(!$isShow)
<script>
  // =============================
  // PREVIEW + VALIDASI FOTO
  // =============================
  document.getElementById('foto')?.addEventListener('change', e => {
    const [file] = e.target.files || [];
    const preview = document.getElementById('fotoPreview');
    const placeholder = document.getElementById('fotoPlaceholder');
    const errorEl = document.getElementById('foto_inline_error');

    if (!file) {
      if (preview) {
        preview.src = '';
        preview.classList.add('hidden');
      }
      if (placeholder) placeholder.classList.remove('hidden');
      if (errorEl) {
        errorEl.textContent = '';
        errorEl.classList.add('hidden');
      }
      return;
    }

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    const maxSize = 2 * 1024 * 1024;

    if (!allowedTypes.includes(file.type)) {
      e.target.value = '';
      if (preview) {
        preview.src = '';
        preview.classList.add('hidden');
      }
      if (placeholder) placeholder.classList.remove('hidden');
      if (errorEl) {
        errorEl.textContent = 'Foto harus berformat JPG, JPEG, PNG, atau WEBP.';
        errorEl.classList.remove('hidden');
      }
      return;
    }

    if (file.size > maxSize) {
      e.target.value = '';
      if (preview) {
        preview.src = '';
        preview.classList.add('hidden');
      }
      if (placeholder) placeholder.classList.remove('hidden');
      if (errorEl) {
        errorEl.textContent = 'Ukuran foto maksimal 2 MB.';
        errorEl.classList.remove('hidden');
      }
      return;
    }

    if (preview) {
      preview.src = URL.createObjectURL(file);
      preview.classList.remove('hidden');
    }
    if (placeholder) placeholder.classList.add('hidden');
    if (errorEl) {
      errorEl.textContent = '';
      errorEl.classList.add('hidden');
    }
  });

  // =============================
  // HELPER ERROR INLINE
  // =============================
  function setInlineError(input, message) {
    if (!input) return;
    input.classList.remove('border-gray-300', 'focus:border-blue-400', 'focus:ring-blue-300');
    input.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-300');

    const errorEl = document.getElementById(input.id + '_inline_error');
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.classList.remove('hidden');
    }
  }

  function clearInlineError(input) {
    if (!input) return;
    input.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-300');
    input.classList.add('border-gray-300', 'focus:border-blue-400', 'focus:ring-blue-300');

    const errorEl = document.getElementById(input.id + '_inline_error');
    if (errorEl) {
      errorEl.textContent = '';
      errorEl.classList.add('hidden');
    }
  }

  function onlyDigits(el) {
    if (!el) return;
    el.addEventListener('input', () => {
      const before = el.value;
      el.value = (el.value || '').replace(/\D+/g, '');
      if (before !== el.value) {
        setInlineError(el, 'Gunakan angka saja.');
      } else {
        validateField(el);
      }
    });
  }

  // =============================
  // VALIDASI PER FIELD
  // =============================
  function validateField(el) {
    if (!el) return true;

    const id = el.id;
    const value = (el.value || '').trim();

    if (el.hasAttribute('required') && value === '') {
      setInlineError(el, 'Kolom ini wajib diisi.');
      return false;
    }

    switch (id) {
      case 'nama':
      case 'nama_ayah':
      case 'nama_ibu':
        if (value && !/^[A-Za-zÀ-ÿ\s'.-]+$/.test(value)) {
          setInlineError(el, 'Kolom ini hanya boleh berisi huruf, spasi, titik, petik, dan tanda hubung.');
          return false;
        }
        if (value.length < 3) {
          setInlineError(el, 'Minimal 3 karakter.');
          return false;
        }
        break;

      case 'tempat_lahir':
        if (value && !/^[A-Za-zÀ-ÿ\s'.-]+$/.test(value)) {
          setInlineError(el, 'Tempat lahir hanya boleh huruf dan spasi.');
          return false;
        }
        break;

      case 'jenis_kelamin':
      case 'agama':
      case 'jalur_penerimaan':
      case 'kebutuhan_khusus':
      case 'tahun_masuk':
      case 'status':
      case 'status_ayah':
      case 'status_ibu':
      case 'pendidikan_ayah':
      case 'pendidikan_ibu':
        if (value === '') {
          setInlineError(el, 'Silakan pilih salah satu.');
          return false;
        }
        break;

      case 'tanggal_lahir':
        if (value === '') {
          setInlineError(el, 'Tanggal lahir wajib diisi.');
          return false;
        }
        break;

      case 'nisn':
        if (!/^\d{10}$/.test(value)) {
          setInlineError(el, 'NISN harus 10 digit angka.');
          return false;
        }
        break;

      case 'nis':
        if (!/^\d+$/.test(value)) {
          setInlineError(el, 'NIS harus berupa angka.');
          return false;
        }
        break;

      case 'nik_ayah':
      case 'nik_ibu':
        if (!/^\d{16}$/.test(value)) {
          setInlineError(el, 'NIK harus 16 digit angka.');
          return false;
        }
        break;

      case 'no_hp':
      case 'no_hp_ayah':
      case 'no_hp_ibu':
        if (!/^(\+62|62|0)?8[0-9]{7,11}$/.test(value)) {
          setInlineError(el, 'Nomor HP tidak valid. Contoh: 081234567890.');
          return false;
        }
        break;

      case 'email':
        if (value === '') {
          setInlineError(el, 'Email wajib diisi.');
          return false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
          setInlineError(el, 'Format email tidak valid.');
          return false;
        }
        break;

      case 'alamat':
      case 'alamat_ayah':
      case 'alamat_ibu':
        if (value.length < 5) {
          setInlineError(el, 'Minimal 5 karakter.');
          return false;
        }
        break;

      case 'pekerjaan_ayah':
      case 'pekerjaan_ibu':
        if (value.length < 3) {
          setInlineError(el, 'Minimal 3 karakter.');
          return false;
        }
        break;
    }

    clearInlineError(el);
    return true;
  }

  const fields = [
    'nama',
    'tempat_lahir',
    'jenis_kelamin',
    'tanggal_lahir',
    'nisn',
    'nis',
    'agama',
    'no_hp',
    'email',
    'alamat',
    'jalur_penerimaan',
    'kebutuhan_khusus',
    'tahun_masuk',
    'status',
    'nama_ayah',
    'nik_ayah',
    'status_ayah',
    'pekerjaan_ayah',
    'pendidikan_ayah',
    'no_hp_ayah',
    'alamat_ayah',
    'nama_ibu',
    'nik_ibu',
    'status_ibu',
    'pekerjaan_ibu',
    'pendidikan_ibu',
    'no_hp_ibu',
    'alamat_ibu'
  ].map(id => document.getElementById(id)).filter(Boolean);

  fields.forEach(field => {
    field.addEventListener('input', () => validateField(field));
    field.addEventListener('change', () => validateField(field));
    field.addEventListener('blur', () => validateField(field));
  });

  onlyDigits(document.getElementById('nisn'));
  onlyDigits(document.getElementById('nis'));
  onlyDigits(document.getElementById('nik_ayah'));
  onlyDigits(document.getElementById('nik_ibu'));
  onlyDigits(document.getElementById('no_hp'));
  onlyDigits(document.getElementById('no_hp_ayah'));
  onlyDigits(document.getElementById('no_hp_ibu'));

  // =============================
  // CEK SEMUA SAAT SUBMIT
  // =============================
  const btn = document.getElementById('btnSubmit');
  const form = btn?.closest('form');

  if (form && btn) {
    form.addEventListener('submit', (e) => {
      let valid = true;

      fields.forEach(field => {
        if (!validateField(field)) valid = false;
      });

      if (!valid) {
        e.preventDefault();
        const firstError = form.querySelector('.border-red-500');
        if (firstError) firstError.focus();
        return;
      }

      btn.disabled = true;
      btn.classList.add('opacity-70', 'cursor-not-allowed');
      btn.innerText = 'Menyimpan...';
    });
  }
</script>
@endif
@endpush