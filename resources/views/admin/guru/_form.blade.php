{{-- resources/views/admin/guru/_form.blade.php --}}
@csrf

@php
  $fotoPath = isset($guru->foto) && $guru->foto
      ? asset('storage/' . ltrim($guru->foto, '/'))
      : null;

  $ttdPath = isset($guru->ttd_path) && $guru->ttd_path
      ? asset('storage/' . ltrim($guru->ttd_path, '/'))
      : null;
@endphp

<div class="bg-white rounded-2xl shadow border overflow-hidden">
  <div class="p-6 bg-gray-50">

    @if ($errors->any())
      <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
        <div class="font-semibold">Data belum valid.</div>
        <div class="text-sm">Periksa kembali kolom yang ditandai merah, lalu coba simpan lagi.</div>
      </div>
    @endif

    {{-- ===================== Data Pribadi ===================== --}}
    <section class="space-y-5">
      <h2 class="text-lg font-bold text-blue-800">Data Pribadi</h2>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Foto --}}
        <div class="space-y-3">
          <label class="block text-sm font-medium text-gray-700">
            Foto Guru <span class="text-rose-600">*</span>
          </label>

          <div class="p-4 border border-blue-200 rounded-xl bg-white shadow-sm inline-block">
            <div id="fotoPreviewWrapper"
                 class="h-48 w-48 rounded-lg border border-blue-100 shadow-sm mb-3 overflow-hidden bg-gray-100 flex items-center justify-center text-gray-400 text-sm">
              @if($fotoPath)
                <img id="fotoPreview"
                     src="{{ $fotoPath }}"
                     class="h-full w-full object-cover"
                     alt="Foto guru">
                <span id="fotoPlaceholder" class="hidden">Belum ada foto</span>
              @else
                <img id="fotoPreview"
                     src=""
                     class="h-full w-full object-cover hidden"
                     alt="Foto guru">
                <span id="fotoPlaceholder">Belum ada foto</span>
              @endif
            </div>

            <input id="foto" name="foto" type="file" accept=".jpg,.jpeg,.png,.webp"
                   @if(empty($guru?->foto)) required @endif
                   class="block w-full max-w-[300px] text-sm rounded-md p-2 border
                          {{ $errors->has('foto') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">

            <p class="mt-2 text-xs text-gray-500">
              Format: JPG/PNG/WEBP. Maksimal ukuran 2 MB.
            </p>

            <p id="foto_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('foto') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>

          {{-- Tanda Tangan Digital --}}
          <div class="p-4 border border-emerald-200 rounded-xl bg-white shadow-sm inline-block">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tanda Tangan Digital
            </label>

            <div id="ttdPreviewWrapper"
                 class="h-28 w-48 rounded-lg border border-emerald-100 shadow-sm mb-3 overflow-hidden bg-gray-50 flex items-center justify-center text-gray-400 text-sm">
              @if($ttdPath)
                <img id="ttdPreview"
                     src="{{ $ttdPath }}"
                     class="h-full w-full object-contain"
                     alt="Tanda tangan guru">
                <span id="ttdPlaceholder" class="hidden">Belum ada TTD</span>
              @else
                <img id="ttdPreview"
                     src=""
                     class="h-full w-full object-contain hidden"
                     alt="Tanda tangan guru">
                <span id="ttdPlaceholder">Belum ada TTD</span>
              @endif
            </div>

            <input id="ttd_file"
                   name="ttd_file"
                   type="file"
                   accept=".jpg,.jpeg,.png"
                   class="block w-full max-w-[300px] text-sm rounded-md p-2 border
                          {{ $errors->has('ttd_file') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">

            <p class="mt-2 text-xs text-gray-500">
              Format: JPG/PNG. Maksimal ukuran 2 MB. Kosongkan jika belum tersedia.
            </p>

            @if($ttdPath)
              <p class="mt-1 text-xs text-emerald-700">
                Tanda tangan sudah tersimpan. Upload file baru untuk mengganti.
              </p>
            @endif

            <p id="ttd_file_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('ttd_file') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>
        </div>

        {{-- Form kanan --}}
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5">
          {{-- Nama --}}
          <div>
            <label for="nama" class="block text-sm font-medium text-gray-700">
              Nama Lengkap <span class="text-rose-600">*</span>
            </label>
            <input id="nama" name="nama" type="text" required
                   value="{{ old('nama', $guru->nama ?? '') }}"
                   placeholder="Contoh: Budi Hartono"
                   pattern="^[A-Za-zÀ-ÿ\s\.\'\-,]+$"
                   title="Nama hanya boleh berisi huruf, spasi, titik, koma, strip, dan apostrof."
                   class="mt-1 block w-full rounded-md border
                          {{ $errors->has('nama') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">
            <p id="nama_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('nama') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>

          {{-- Jenis Kelamin --}}
          <div>
            <label for="jk" class="block text-sm font-medium text-gray-700">
              Jenis Kelamin <span class="text-rose-600">*</span>
            </label>
            @php $jkVal = old('jk', $guru->jk ?? '') @endphp
            <select id="jk" name="jk" required
                    class="mt-1 block w-full rounded-md border bg-white
                           {{ $errors->has('jk') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">
              <option value="">-- Pilih --</option>
              <option value="L" @selected($jkVal === 'L')>Laki-laki</option>
              <option value="P" @selected($jkVal === 'P')>Perempuan</option>
            </select>
            <p id="jk_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('jk') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>

          {{-- NIP --}}
          <div>
            <label for="nip" class="block text-sm font-medium text-gray-700">
              NIP
            </label>
            <input id="nip" name="nip" type="text"
                   value="{{ old('nip', $guru->nip ?? '') }}"
                   placeholder="Contoh: 198012312010011001"
                   inputmode="numeric" autocomplete="off"
                   pattern="^\d{8,25}$"
                   title="NIP harus berupa angka 8 sampai 25 digit."
                   class="mt-1 block w-full rounded-md border
                          {{ $errors->has('nip') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">
            <p id="nip_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('nip') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>

          {{-- NUPTK --}}
          <div>
            <label for="nuptk" class="block text-sm font-medium text-gray-700">
              NUPTK <span class="text-rose-600">*</span>
            </label>
            <input id="nuptk" name="nuptk" type="text" required
                   value="{{ old('nuptk', $guru->nuptk ?? '') }}"
                   placeholder="Contoh: 1234567890123456"
                   inputmode="numeric" autocomplete="off"
                   pattern="^\d{8,25}$"
                   title="NUPTK harus berupa angka 8 sampai 25 digit."
                   class="mt-1 block w-full rounded-md border
                          {{ $errors->has('nuptk') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">
            <p id="nuptk_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('nuptk') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>

          {{-- Tempat Lahir --}}
          <div>
            <label for="tempat_lahir" class="block text-sm font-medium text-gray-700">
              Tempat Lahir <span class="text-rose-600">*</span>
            </label>
            <input id="tempat_lahir" name="tempat_lahir" type="text" required
                   value="{{ old('tempat_lahir', $guru->tempat_lahir ?? '') }}"
                   placeholder="Contoh: Temanggung"
                   pattern="^[A-Za-zÀ-ÿ\s\.\'\-]+$"
                   title="Tempat lahir hanya boleh huruf dan spasi."
                   class="mt-1 block w-full rounded-md border
                          {{ $errors->has('tempat_lahir') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">
            <p id="tempat_lahir_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('tempat_lahir') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>

          {{-- Tanggal Lahir --}}
          <div>
            <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700">
              Tanggal Lahir <span class="text-rose-600">*</span>
            </label>
            <input id="tanggal_lahir" name="tanggal_lahir" type="date" required
                   value="{{ old('tanggal_lahir', optional($guru->tanggal_lahir)->format('Y-m-d')) }}"
                   class="mt-1 block w-full rounded-md border
                          {{ $errors->has('tanggal_lahir') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">
            <p id="tanggal_lahir_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('tanggal_lahir') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>
        </div>
      </div>
    </section>

    {{-- ===================== Kepegawaian & Kontak ===================== --}}
    <section class="space-y-5 mt-8">
      <h2 class="text-lg font-bold text-blue-800">Kepegawaian & Kontak</h2>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
        <div class="space-y-5">
          <div>
            <label for="status_kepegawaian" class="block text-sm font-medium text-gray-700">
              Status Kepegawaian <span class="text-rose-600">*</span>
            </label>
            @php $sk = old('status_kepegawaian', $guru->status_kepegawaian ?? '') @endphp
            <select id="status_kepegawaian" name="status_kepegawaian" required
                    class="mt-1 block w-full rounded-md border bg-white
                           {{ $errors->has('status_kepegawaian') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">
              <option value="">-- Pilih --</option>
              <option value="PNS" @selected($sk === 'PNS')>PNS</option>
              <option value="PPPK" @selected($sk === 'PPPK')>PPPK</option>
              <option value="Non-PNS" @selected($sk === 'Non-PNS')>Non-PNS</option>
            </select>
            <p id="status_kepegawaian_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('status_kepegawaian') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>

          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">
              Status <span class="text-rose-600">*</span>
            </label>
            @php $st = old('status', $guru->status ?? 'aktif') @endphp
            <select id="status" name="status" required
                    class="mt-1 block w-full rounded-md border bg-white
                           {{ $errors->has('status') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">
              <option value="aktif" @selected($st === 'aktif')>Aktif</option>
              <option value="nonaktif" @selected($st === 'nonaktif')>Non Aktif</option>
            </select>
            <p id="status_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('status') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>
        </div>

        <div class="space-y-5">
          <div>
            <label for="no_hp" class="block text-sm font-medium text-gray-700">
              No. HP <span class="text-rose-600">*</span>
            </label>
            <input id="no_hp" name="no_hp" type="text" required
                   value="{{ old('no_hp', $guru->no_hp ?? '') }}"
                   placeholder="Contoh: 081234567890"
                   inputmode="numeric" autocomplete="off"
                   pattern="^\d{10,15}$"
                   title="No. HP harus angka 10 sampai 15 digit."
                   class="mt-1 block w-full rounded-md border
                          {{ $errors->has('no_hp') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">
            <p id="no_hp_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('no_hp') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email <span class="text-rose-600">*</span>
            </label>
            <input id="email" name="email" type="email" required
                   value="{{ old('email', $guru->email ?? '') }}"
                   placeholder="Contoh: guru@sekolah.sch.id"
                   class="mt-1 block w-full rounded-md border
                          {{ $errors->has('email') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">
            <p id="email_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
            @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
          </div>
        </div>

        <div>
          <label for="alamat" class="block text-sm font-medium text-gray-700">
            Alamat Lengkap <span class="text-rose-600">*</span>
          </label>
          <textarea id="alamat" name="alamat" rows="6" required
                    placeholder="Contoh: Jl. Pahlawan No. 10, Temanggung"
                    class="mt-1 block w-full rounded-md border
                           {{ $errors->has('alamat') ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-200' : 'border-gray-300 focus:border-blue-400 focus:ring-blue-300' }}">{{ old('alamat', $guru->alamat ?? '') }}</textarea>
          <p id="alamat_inline_error" class="mt-1 text-sm text-rose-600 hidden"></p>
          @error('alamat') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>
      </div>
    </section>

    <div class="flex items-center gap-3 pt-8">
      <button id="btnSubmit" type="submit"
              class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-md hover:bg-blue-700 transition">
        Simpan
      </button>

      <a href="{{ route('admin.guru.index') }}"
         class="px-6 py-2 border border-gray-300 bg-white text-gray-700 rounded-md shadow-sm hover:bg-gray-100 transition">
        Batal
      </a>
    </div>

  </div>
</div>

@push('scripts')
<script>
  function setInlineError(input, message) {
    if (!input) return;

    input.classList.remove('border-gray-300', 'focus:border-blue-400', 'focus:ring-blue-300');
    input.classList.add('border-rose-400', 'focus:border-rose-500', 'focus:ring-rose-200');

    const errorEl = document.getElementById(input.id + '_inline_error');

    if (errorEl) {
      errorEl.textContent = message;
      errorEl.classList.remove('hidden');
    }
  }

  function clearInlineError(input) {
    if (!input) return;

    input.classList.remove('border-rose-400', 'focus:border-rose-500', 'focus:ring-rose-200');
    input.classList.add('border-gray-300', 'focus:border-blue-400', 'focus:ring-blue-300');

    const errorEl = document.getElementById(input.id + '_inline_error');

    if (errorEl) {
      errorEl.textContent = '';
      errorEl.classList.add('hidden');
    }
  }

  function handleImagePreview(config) {
    const input = document.getElementById(config.inputId);
    const preview = document.getElementById(config.previewId);
    const placeholder = document.getElementById(config.placeholderId);
    const errorEl = document.getElementById(config.errorId);

    if (!input || !preview || !placeholder || !errorEl) {
      return;
    }

    input.addEventListener('change', e => {
      const [file] = e.target.files || [];

      if (!file) {
        errorEl.classList.add('hidden');
        errorEl.textContent = '';

        if (!preview.getAttribute('src')) {
          preview.src = '';
          preview.classList.add('hidden');
          placeholder.classList.remove('hidden');
        }

        return;
      }

      const maxSize = 2 * 1024 * 1024;

      if (!config.allowedTypes.includes(file.type)) {
        errorEl.textContent = config.typeMessage;
        errorEl.classList.remove('hidden');

        e.target.value = '';
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        return;
      }

      if (file.size > maxSize) {
        errorEl.textContent = 'Ukuran file maksimal 2 MB.';
        errorEl.classList.remove('hidden');

        e.target.value = '';
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        return;
      }

      errorEl.textContent = '';
      errorEl.classList.add('hidden');

      preview.src = URL.createObjectURL(file);
      preview.classList.remove('hidden');
      placeholder.classList.add('hidden');
    });
  }

  handleImagePreview({
    inputId: 'foto',
    previewId: 'fotoPreview',
    placeholderId: 'fotoPlaceholder',
    errorId: 'foto_inline_error',
    allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
    typeMessage: 'Foto harus berformat JPG, JPEG, PNG, atau WEBP.'
  });

  handleImagePreview({
    inputId: 'ttd_file',
    previewId: 'ttdPreview',
    placeholderId: 'ttdPlaceholder',
    errorId: 'ttd_file_inline_error',
    allowedTypes: ['image/jpeg', 'image/jpg', 'image/png'],
    typeMessage: 'Tanda tangan digital harus berformat JPG, JPEG, atau PNG.'
  });

  function onlyDigits(el) {
    if (!el) return;

    el.addEventListener('input', () => {
      const oldVal = el.value;
      el.value = (el.value || '').replace(/\D+/g, '');

      if (oldVal !== el.value) {
        setInlineError(el, 'Gunakan angka saja.');
      } else {
        validateField(el);
      }
    });
  }

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
        if (value && !/^[A-Za-zÀ-ÿ\s.\-',]+$/.test(value)) {
          setInlineError(el, 'Nama hanya boleh huruf, spasi, titik, koma, strip, dan apostrof.');
          return false;
        }

        if (value.length < 3) {
          setInlineError(el, 'Nama minimal 3 karakter.');
          return false;
        }
        break;

      case 'jk':
        if (value === '') {
          setInlineError(el, 'Pilih jenis kelamin.');
          return false;
        }
        break;

      case 'nip':
        if (value && !/^\d{8,25}$/.test(value)) {
          setInlineError(el, 'NIP harus angka 8 sampai 25 digit.');
          return false;
        }
        break;

      case 'nuptk':
        if (!/^\d{8,25}$/.test(value)) {
          setInlineError(el, 'NUPTK harus angka 8 sampai 25 digit.');
          return false;
        }
        break;

      case 'tempat_lahir':
        if (value && !/^[A-Za-zÀ-ÿ\s.\'-]+$/.test(value)) {
          setInlineError(el, 'Tempat lahir hanya boleh huruf dan spasi.');
          return false;
        }
        break;

      case 'tanggal_lahir':
        if (value === '') {
          setInlineError(el, 'Tanggal lahir wajib diisi.');
          return false;
        }
        break;

      case 'status_kepegawaian':
        if (value === '') {
          setInlineError(el, 'Pilih status kepegawaian.');
          return false;
        }
        break;

      case 'no_hp':
        if (!/^\d{10,15}$/.test(value)) {
          setInlineError(el, 'No. HP harus angka 10 sampai 15 digit.');
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

      case 'status':
        if (value === '') {
          setInlineError(el, 'Pilih status.');
          return false;
        }
        break;

      case 'alamat':
        if (value.length < 5) {
          setInlineError(el, 'Alamat minimal 5 karakter.');
          return false;
        }
        break;
    }

    clearInlineError(el);
    return true;
  }

  const fields = [
    document.getElementById('nama'),
    document.getElementById('jk'),
    document.getElementById('nip'),
    document.getElementById('nuptk'),
    document.getElementById('tempat_lahir'),
    document.getElementById('tanggal_lahir'),
    document.getElementById('status_kepegawaian'),
    document.getElementById('no_hp'),
    document.getElementById('email'),
    document.getElementById('status'),
    document.getElementById('alamat'),
  ];

  fields.forEach(field => {
    if (!field) return;

    field.addEventListener('input', () => validateField(field));
    field.addEventListener('change', () => validateField(field));
    field.addEventListener('blur', () => validateField(field));
  });

  onlyDigits(document.getElementById('nip'));
  onlyDigits(document.getElementById('nuptk'));
  onlyDigits(document.getElementById('no_hp'));

  const btn = document.getElementById('btnSubmit');
  const form = btn?.closest('form');

  if (form && btn) {
    form.addEventListener('submit', (e) => {
      let valid = true;

      fields.forEach(field => {
        if (!validateField(field)) {
          valid = false;
        }
      });

      if (!valid) {
        e.preventDefault();

        const firstError = form.querySelector('.border-rose-400');

        if (firstError) {
          firstError.focus();
        }

        return;
      }

      btn.disabled = true;
      btn.classList.add('opacity-70', 'cursor-not-allowed');
      btn.innerText = 'Menyimpan...';
    });
  }
</script>
@endpush