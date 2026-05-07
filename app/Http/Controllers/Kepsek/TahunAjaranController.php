<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->q;

        $items = TahunAjaran::query()
            ->when($q, function ($qr) use ($q) {
                $like = '%' . $q . '%';
                $qr->where(function ($s) use ($like) {
                    $s->where('nama_tahun', 'like', $like)
                        ->orWhere('semester', 'like', $like)
                        ->orWhere('status', 'like', $like);
                });
            })
            ->orderByRaw("status = 'aktif' DESC")
            ->orderByDesc('tanggal_mulai')
            ->paginate(10)
            ->withQueryString();

        return view('kepsek.tahun_ajaran.index', compact('items', 'q'));
    }

    public function show(TahunAjaran $tahun_ajaran)
    {
        return view('kepsek.tahun_ajaran.show', ['item' => $tahun_ajaran]);
    }

    public function edit(TahunAjaran $tahun_ajaran)
    {
        return view('kepsek.tahun_ajaran.edit', ['item' => $tahun_ajaran]);
    }

    public function update(Request $request, TahunAjaran $tahun_ajaran)
    {
        $data = $this->validated($request, $tahun_ajaran->id);

        if (($data['status'] ?? $tahun_ajaran->status) === 'aktif') {
            DB::transaction(function () use ($tahun_ajaran, $data) {
                TahunAjaran::where('status', 'aktif')
                    ->where('id', '<>', $tahun_ajaran->id)
                    ->update(['status' => 'nonaktif']);

                $tahun_ajaran->update($data);
            });

            return redirect()
                ->route('kepala_sekolah.data.tahun-ajaran')
                ->with('ok', 'Tahun ajaran berhasil diperbarui dan diaktifkan.');
        }

        $tahun_ajaran->update($data);

        return redirect()
            ->route('kepala_sekolah.data.tahun-ajaran')
            ->with('ok', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahun_ajaran)
    {
        if ($tahun_ajaran->status === 'aktif') {
            return back()->with('err', 'Tidak bisa menghapus tahun ajaran yang sedang aktif.');
        }

        $tahun_ajaran->delete();

        return back()->with('ok', 'Tahun ajaran berhasil dihapus.');
    }

    public function setAktif(TahunAjaran $tahun_ajaran)
    {
        DB::transaction(function () use ($tahun_ajaran) {
            TahunAjaran::where('status', 'aktif')->update(['status' => 'nonaktif']);
            $tahun_ajaran->update(['status' => 'aktif']);
        });

        return back()->with('ok', 'Tahun ajaran telah diaktifkan.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nama_tahun' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tahun_ajaran', 'nama_tahun')->ignore($ignoreId),
            ],
            'semester' => ['required', Rule::in(['Ganjil', 'Genap'])],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [], [
            'nama_tahun' => 'nama tahun ajaran',
            'tanggal_mulai' => 'tanggal mulai',
            'tanggal_selesai' => 'tanggal selesai',
        ]);
    }
}