<x-guest-layout>
  <div class="w-full max-w-6xl">
    <div class="grid grid-cols-1 md:grid-cols-2 overflow-hidden rounded-[32px] bg-white shadow-xl ring-1 ring-slate-200">

      {{-- ================= PANEL INFORMASI (KIRI) ================= --}}
      <div class="hidden md:!flex flex-col justify-between p-10 bg-white relative border-r border-slate-200">
        {{-- aksen biru halus --}}

        <div class="relative">
          {{-- header --}}
          <div class="flex items-center gap-3">
<div class="flex h-14 w-14 items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm">
  <img src="{{ asset('images/logo-sman2.png') }}"
       alt="Logo SMAN 2 Temanggung"
       class="h-12 w-12 rounded-full object-contain">
</div>
     <div>
              <div class="text-lg font-bold text-slate-900">Sistem Informasi Akademik</div>
              <div class="text-sm text-slate-500">SMA Negeri 2 Temanggung</div>
            </div>
          </div>

          {{-- konten --}}
          <div class="mt-10">
            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-100">
              Selamat datang
            </span>

            <h2 class="mt-4 text-4xl font-extrabold text-slate-900 leading-tight">
              Sistem Informasi<br>
              <span class="text-indigo-700">Akademik</span>
            </h2>

            <p class="mt-3 text-sm text-slate-600 leading-relaxed max-w-sm">
              Silakan masuk menggunakan akun yang diberikan sekolah untuk melihat dan mengelola informasi akademik.
            </p>

            <div class="mt-7 space-y-3">
              <div class="rounded-2xl border border-slate-200 p-4 bg-white/70">
                <div class="text-sm font-semibold text-slate-900">Informasi</div>
                <div class="text-xs text-slate-600 mt-1">
                  Pastikan data akun sudah benar sebelum masuk ke sistem.
                </div>
              </div>

              <br>
            </div>
          </div>
        </div>

        <div class="relative text-xs text-slate-500">
          © {{ now()->format('Y') }} SMA Negeri 2 Temanggung
        </div>
      </div>

      {{-- ================= FORM LOGIN (KANAN) ================= --}}
      {{-- bikin kolom kanan benar-benar center --}}
      <div class="p-8 sm:p-10 flex items-center justify-center">
        <div class="w-full max-w-md">
          {{-- header mobile (panel kiri hidden di mobile) --}}
          <div class="flex items-center gap-3 md:hidden mb-6">
            <div class="h-12 w-12 rounded-2xl bg-indigo-50 ring-1 ring-indigo-100 flex items-center justify-center">
              <img src="{{ asset('images/logo-sman2.png') }}" alt="Logo SMAN 2 Temanggung"
                   class="h-8 w-8 object-contain">
            </div>
            <div>
              <div class="font-semibold text-slate-900">Sistem Informasi Akademik</div>
              <div class="text-sm text-slate-500">SMA Negeri 2 Temanggung</div>
            </div>
          </div>

          {{-- judul & deskripsi center --}}
          <div class="text-center">
            <h1 class="text-3xl font-extrabold text-slate-900">Masuk</h1>

          </div>

          <form method="POST" action="{{ route('login') }}" class="space-y-5 mt-8">
            @csrf

            <div>
              <x-input-label for="login" value="Email / NIS / NUPTK" />
              <x-text-input id="login" name="login" type="text" required autofocus
                            class="block mt-1 w-full"
                            :value="old('login')" placeholder="Masukkan email, NIS, atau NUPTK" autocomplete="username" />
              <x-input-error :messages="$errors->get('login')" class="mt-2" />
            </div>

            <div>
              <x-input-label for="password" value="Password" />
              <x-text-input id="password" name="password" type="password" required
                            class="block mt-1 w-full" placeholder="Masukkan password" autocomplete="current-password" />
              <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
<label class="block text-sm font-medium text-slate-700 mb-2">
    {{ session('captcha_num1') }} + {{ session('captcha_num2') }} = ?
</label>

<x-text-input
    id="captcha_answer"
    name="captcha_answer"
    type="text"
    inputmode="numeric"
    pattern="[0-9]*"
    required
    class="block mt-1 w-full border-slate-300 bg-white text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
    placeholder="Masukkan hasil penjumlahan"
    autocomplete="off"
    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
/>

<x-input-error :messages="$errors->get('captcha_answer')" class="mt-2" />

            </div>

            <button class="w-full py-3 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition shadow-sm">
              MASUK
            </button>
          </form>
        </div>
      </div>

    </div>
  </div>
</x-guest-layout>
