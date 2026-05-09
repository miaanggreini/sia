<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    /**
     * LIST + PENCARIAN
     */
    public function index(Request $request)
    {
        $q = $request->query('q');

        $items = Siswa::query()
            ->when($q, function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('nama', 'like', "%{$q}%")
                      ->orWhere('nis', 'like', "%{$q}%")
                      ->orWhere('nisn', 'like', "%{$q}%")
                      ->orWhere('no_hp', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('nama_ayah', 'like', "%{$q}%")
                      ->orWhere('nama_ibu', 'like', "%{$q}%")
                      ->orWhere('nik_ayah', 'like', "%{$q}%")
                      ->orWhere('nik_ibu', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('admin.siswa.index', compact('items', 'q'));
    }

    /**
     * FORM CREATE
     */
    public function create()
    {
        $siswa = new Siswa();
        return view('admin.siswa.create', compact('siswa'));
    }

    /**
     * SIMPAN DATA BARU
     */
public function store(Request $request)
{
    $data = $this->validateData($request);

    // Samakan perilaku dengan update(): kosong -> null
    $data['tanggal_lahir'] = $request->filled('tanggal_lahir')
        ? $request->input('tanggal_lahir')
        : null;

    // Upload foto (jika ada)
    if ($request->hasFile('foto')) {
        $data['foto'] = $request->file('foto')->store('foto_siswa', 'public');
    }

    Siswa::create($data);

    return redirect()
        ->route('admin.siswa.index')
        ->with('ok', 'Data siswa berhasil ditambahkan.');
}

    /**
     * FORM EDIT
     */
    public function edit(Siswa $siswa)
    {
        return view('admin.siswa.edit', compact('siswa'));
    }

    /**
     * UPDATE DATA
     */
public function update(Request $request, Siswa $siswa)
{
    $data = $this->validateData($request, $siswa->id);

    $data['tanggal_lahir'] = $request->filled('tanggal_lahir')
        ? $request->input('tanggal_lahir')
        : null;

    if ($request->hasFile('foto')) {
        // Hapus foto lama jika ada
        if ($siswa->foto && Storage::disk('public')->exists($siswa->foto)) {
            Storage::disk('public')->delete($siswa->foto);
        }

        // Simpan foto baru
        $data['foto'] = $request->file('foto')->store('foto_siswa', 'public');
    }

    $siswa->update($data);

return redirect()->route('admin.siswa.index')->with('ok','Data siswa berhasil diperbarui.');

}

    /**
     * HAPUS DATA
     */
    public function destroy(Siswa $siswa)
    {
        // Hapus foto jika ada
        if ($siswa->foto && Storage::disk('public')->exists($siswa->foto)) {
            Storage::disk('public')->delete($siswa->foto);
        }

        $siswa->delete();

        return redirect()
            ->route('admin.siswa.index')
            ->with('ok', 'Data siswa berhasil dihapus.');
    }

    /**
     * RULE VALIDASI TERPUSAT
     */
    private function validateData(Request $request, ?int $id = null): array
{
    return $request->validate([
        // ================== Siswa ==================
        'nisn' => [
            'required',
            'digits:10',
            Rule::unique('siswa', 'nisn')->ignore($id),
        ],
        'nis' => [
            'required',
            'regex:/^\d+$/',
            'max:20',
            Rule::unique('siswa', 'nis')->ignore($id),
        ],
        'nama' => ['required', 'string', 'max:120', 'regex:/^[A-Za-zÀ-ÿ\s\'.-]+$/'],
        'jenis_kelamin' => ['required', Rule::in(['L','P'])],
        'tempat_lahir' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿ\s\'.-]+$/'],
        'tanggal_lahir' => ['required', 'date'],
        'agama' => ['required', 'string', 'max:30'],
        'alamat' => ['required', 'string', 'max:255'],
        'no_hp' => ['required', 'regex:/^(\+62|62|0)?8[0-9]{7,11}$/', 'max:15'],
        'email' => ['required', 'email', 'max:120', Rule::unique('siswa', 'email')->ignore($id)],
        'jalur_penerimaan' => ['required', Rule::in(['Afirmasi','Mutasi','Prestasi','Domisili Khusus','Domisili Reguler'])],
        'kebutuhan_khusus' => ['required', Rule::in(['Ya','Tidak'])],
        'tahun_masuk' => ['required', 'integer', 'between:2000,' . date('Y')],
        'status' => ['required', Rule::in(['aktif', 'pindah', 'keluar'])],
        // Foto
        'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

        // ================== Ayah ==================
        'nama_ayah' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿ\s\'.-]+$/'],
        'nik_ayah' => ['required', 'digits:16'],
        'status_ayah' => ['required', Rule::in(['hidup','meninggal','tidak diketahui'])],
        'pekerjaan_ayah' => ['required', 'string', 'max:100'],
        'pendidikan_ayah' => ['required', Rule::in(['Tidak sekolah','SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3'])],
        'no_hp_ayah' => ['required', 'regex:/^(\+62|62|0)?8[0-9]{7,11}$/', 'max:15'],
        'alamat_ayah' => ['required', 'string', 'max:255'],

        // ================== Ibu ==================
        'nama_ibu' => ['required', 'string', 'max:100', 'regex:/^[A-Za-zÀ-ÿ\s\'.-]+$/'],
        'nik_ibu' => ['required', 'digits:16'],
        'status_ibu' => ['required', Rule::in(['hidup','meninggal','tidak diketahui'])],
        'pekerjaan_ibu' => ['required', 'string', 'max:100'],
        'pendidikan_ibu' => ['required', Rule::in(['Tidak sekolah','SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3'])],
        'no_hp_ibu' => ['required', 'regex:/^(\+62|62|0)?8[0-9]{7,11}$/', 'max:15'],
        'alamat_ibu' => ['required', 'string', 'max:255'],
    ], [
        'nisn.required' => 'NISN wajib diisi.',
        'nisn.digits' => 'NISN harus 10 digit angka.',
        'nisn.unique' => 'NISN sudah digunakan.',

        'nis.required' => 'NIS wajib diisi.',
        'nis.regex' => 'NIS harus berupa angka.',
        'nis.max' => 'NIS maksimal 20 digit.',
        'nis.unique' => 'NIS sudah digunakan.',

        'nama.required' => 'Nama siswa wajib diisi.',
        'nama.regex' => 'Nama siswa hanya boleh berisi huruf, spasi, titik, petik, dan tanda hubung.',

        'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',

        'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
        'tempat_lahir.regex' => 'Tempat lahir hanya boleh berisi huruf dan spasi.',

        'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
        'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',

        'agama.required' => 'Agama wajib dipilih.',

        'alamat.required' => 'Alamat wajib diisi.',

        'no_hp.required' => 'Nomor HP siswa wajib diisi.',
        'no_hp.regex' => 'Nomor HP siswa tidak valid. Contoh: 081234567890.',

        'email.required' => 'Email siswa wajib diisi.',
        'email.email' => 'Format email siswa tidak valid.',
        'email.unique' => 'Email siswa sudah digunakan.',

        'jalur_penerimaan.required' => 'Jalur penerimaan wajib dipilih.',
        'jalur_penerimaan.in' => 'Jalur penerimaan tidak valid.',

        'kebutuhan_khusus.required' => 'Kebutuhan khusus wajib dipilih.',
        'kebutuhan_khusus.in' => 'Pilihan kebutuhan khusus tidak valid.',

        'tahun_masuk.required' => 'Tahun masuk wajib dipilih.',
        'tahun_masuk.integer' => 'Tahun masuk harus berupa angka.',
        'tahun_masuk.between' => 'Tahun masuk tidak valid.',

        'status.required' => 'Status siswa wajib dipilih.',
        'status.in' => 'Status siswa tidak valid.',

        'foto.image' => 'File foto harus berupa gambar.',
        'foto.mimes' => 'Foto harus berformat JPG, JPEG, PNG, atau WEBP.',
        'foto.max' => 'Ukuran foto maksimal 2 MB.',

        'nama_ayah.required' => 'Nama ayah wajib diisi.',
        'nama_ayah.regex' => 'Nama ayah hanya boleh berisi huruf dan spasi.',

        'nik_ayah.required' => 'NIK ayah wajib diisi.',
        'nik_ayah.digits' => 'NIK ayah harus 16 digit angka.',

        'status_ayah.required' => 'Status ayah wajib dipilih.',
        'status_ayah.in' => 'Status ayah tidak valid.',

        'pekerjaan_ayah.required' => 'Pekerjaan ayah wajib diisi.',
        'pendidikan_ayah.required' => 'Pendidikan ayah wajib dipilih.',
        'pendidikan_ayah.in' => 'Pendidikan ayah tidak valid.',

        'no_hp_ayah.required' => 'No HP ayah wajib diisi.',
        'no_hp_ayah.regex' => 'No HP ayah tidak valid. Contoh: 081234567890.',

        'alamat_ayah.required' => 'Alamat ayah wajib diisi.',

        'nama_ibu.required' => 'Nama ibu wajib diisi.',
        'nama_ibu.regex' => 'Nama ibu hanya boleh berisi huruf dan spasi.',

        'nik_ibu.required' => 'NIK ibu wajib diisi.',
        'nik_ibu.digits' => 'NIK ibu harus 16 digit angka.',

        'status_ibu.required' => 'Status ibu wajib dipilih.',
        'status_ibu.in' => 'Status ibu tidak valid.',

        'pekerjaan_ibu.required' => 'Pekerjaan ibu wajib diisi.',
        'pendidikan_ibu.required' => 'Pendidikan ibu wajib dipilih.',
        'pendidikan_ibu.in' => 'Pendidikan ibu tidak valid.',

        'no_hp_ibu.required' => 'No HP ibu wajib diisi.',
        'no_hp_ibu.regex' => 'No HP ibu tidak valid. Contoh: 081234567890.',

        'alamat_ibu.required' => 'Alamat ibu wajib diisi.',
    ]);

    }

       public function show(Siswa $siswa)
    {
        return view('admin.siswa.show', compact('siswa'));
    }
}
