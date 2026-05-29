<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\TahunAjaran;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class PenilaianController extends Controller
{
public function index()
{
    $user = auth()->user();

    $guruId = optional($user->guru)->id
        ?? Guru::where('user_id', $user->id)->value('id');

    $taAktif = $this->getTahunAjaranAktif();
    $taLabel = null;
    $semester = null;

    if ($taAktif) {
        $taLabel = $taAktif->nama_tahun
            ?? $taAktif->tahun
            ?? (($taAktif->mulai ?? '') . (($taAktif->mulai && $taAktif->akhir) ? '/' : '') . ($taAktif->akhir ?? ''));

        $semester = $taAktif->semester ?? $taAktif->periode ?? null;
    }

    $dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    /*
    |--------------------------------------------------------------------------
    | Jadwal guru hanya dari tahun ajaran aktif
    |--------------------------------------------------------------------------
    | Jangan hanya filter guru_id, karena guru bisa punya jadwal di TA lama.
    | Jadwal dikunci melalui rombel.tahun_ajaran_id.
    */
    $teaching = Jadwal::with(['rombel', 'mapel', 'mataPelajaran'])
        ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
        ->when($taAktif, function ($q) use ($taAktif) {
            $q->whereHas('rombel', function ($r) use ($taAktif) {
                $r->where('tahun_ajaran_id', $taAktif->id)
                  ->where(function ($w) {
                      $w->where('aktif', 1)
                        ->orWhereNull('aktif');
                  });
            });
        })
        ->orderByRaw("FIELD(hari, '" . implode("','", $dayOrder) . "')")
        ->orderBy('jam_mulai')
        ->get();

    $riwayat = $teaching->map(function ($j) use ($taAktif) {
        $mapel = $j->mataPelajaran->nama_mapel
            ?? $j->mapel->nama_mapel
            ?? $j->mapel->nama
            ?? 'Mapel';

        $siswaIds = $this->siswaQueryUntukJadwal($j)
            ->pluck('s.id')
            ->map(fn ($v) => (int) $v);

        $total = $siswaIds->count();

        $rows = DB::table('nilai')
            ->where('jadwal_id', $j->id)
            ->whereIn('siswa_id', $siswaIds)
            ->when($taAktif, function ($q) use ($taAktif) {
                $q->where('tahun_ajaran_id', $taAktif->id)
                    ->where('semester', $taAktif->semester);
            })
            ->get();

        $doneLM1 = $rows->whereNotNull('lm1_nilai')->count();
        $doneLM2 = $rows->whereNotNull('lm2_nilai')->count();
        $doneLM3 = $rows->whereNotNull('lm3_nilai')->count();
        $doneLM4 = $rows->whereNotNull('lm4_nilai')->count();

        $statusFinal = $total > 0
            && $rows->count() === $total
            && $rows->where('status_penilaian', 'final')->count() === $total;

        $finalAt = $rows->where('status_penilaian', 'final')->max('finalized_at');

        return [
            'id'                => $j->id,
            'rombel_id'         => $j->rombel_id,
            'mata_pelajaran_id' => $j->mata_pelajaran_id,
            'mapel'             => $mapel,
            'rombel'            => $j->rombel->nama_rombel ?? '-',
            'hari'              => $j->hari,
            'jam'               => ($j->jam_mulai ? substr($j->jam_mulai, 0, 5) : '') . '–' . ($j->jam_selesai ? substr($j->jam_selesai, 0, 5) : ''),
            'total'             => $total,
            'done'              => [
                'LM1' => $doneLM1,
                'LM2' => $doneLM2,
                'LM3' => $doneLM3,
                'LM4' => $doneLM4,
            ],
            'status'            => $statusFinal ? 'final' : 'draft',
            'final_at'          => $finalAt,
        ];
    });

    return view('guru.penilaian.index', compact(
        'teaching',
        'riwayat',
        'taAktif',
        'taLabel',
        'semester'
    ));
}
    public function create(Request $request)
    {
        $data = $request->validate([
            'rombel_id'         => ['required', 'integer'],
            'mata_pelajaran_id' => ['required', 'integer'],
            'komponen'          => ['required', 'in:LM1,LM2,LM3,LM4'],
        ]);

        $guru = auth()->user()->guru
            ?? Guru::where('user_id', auth()->id())->first();

        $taAktif = $this->getTahunAjaranAktif();

        if (!$taAktif) {
            return back()->withErrors(['msg' => 'Tahun ajaran aktif belum diatur.']);
        }

$jadwal = Jadwal::with(['rombel', 'mapel', 'mataPelajaran'])
    ->where('guru_id', $guru->id ?? 0)
    ->where('rombel_id', $data['rombel_id'])
    ->where('mata_pelajaran_id', $data['mata_pelajaran_id'])
    ->whereHas('rombel', function ($r) use ($taAktif) {
        $r->where('tahun_ajaran_id', $taAktif->id)
          ->where(function ($w) {
              $w->where('aktif', 1)
                ->orWhereNull('aktif');
          });
    })
    ->firstOrFail();

        $siswa = $this->siswaQueryUntukJadwal($jadwal)
            ->orderBy('s.nama')
            ->get();

        $nilai = DB::table('nilai')
            ->where('jadwal_id', $jadwal->id)
            ->where('tahun_ajaran_id', $taAktif->id)
            ->where('semester', $taAktif->semester)
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $progress = $this->buildProgress($jadwal, $taAktif->id, $taAktif->semester);

        $statusInfo = $this->getStatusPenilaianSemester($jadwal->id, $taAktif->id, $taAktif->semester);
        $readOnly = $statusInfo['status'] === 'final';

        $kkm = $jadwal->mataPelajaran->kkm
            ?? $jadwal->mapel->kkm
            ?? null;

        return view('guru.penilaian.create', [
            'guru'          => $guru,
            'jadwal'        => $jadwal,
            'siswa'         => $siswa,
            'komponen'      => $data['komponen'],
            'nilai'         => $nilai,
            'progress'      => $progress,
            'readOnly'      => $readOnly,
            'taAktif'       => $taAktif,
            'semesterAktif' => $taAktif->semester,
            'statusInfo'    => $statusInfo,
            'kkm'           => $kkm,
        ]);
    }

   public function store(Request $request)
{
    $data = $request->validate([
        'jadwal_id'         => ['required', 'integer'],
        'rombel_id'         => ['required', 'integer'],
        'mata_pelajaran_id' => ['required', 'integer'],
        'komponen'          => ['required', 'in:LM1,LM2,LM3,LM4'],

        'nilai'             => ['nullable', 'array'],
        'nilai.*.tp1'       => ['nullable', 'numeric', 'min:0', 'max:100'],
        'nilai.*.tp2'       => ['nullable', 'numeric', 'min:0', 'max:100'],
        'nilai.*.tp3'       => ['nullable', 'numeric', 'min:0', 'max:100'],
        'nilai.*.tp4'       => ['nullable', 'numeric', 'min:0', 'max:100'],

        'jenis_tp1'         => ['required', 'in:praktik,teori'],
        'jenis_tp2'         => ['required', 'in:praktik,teori'],
        'jenis_tp3'         => ['required', 'in:praktik,teori'],
        'jenis_tp4'         => ['required', 'in:praktik,teori'],

        'bobot_praktik'     => ['required', 'numeric', 'min:0', 'max:100'],
        'bobot_teori'       => ['required', 'numeric', 'min:0', 'max:100'],
    ]);

    $taAktif = $this->getTahunAjaranAktif();

    if (!$taAktif) {
        return back()->withErrors(['msg' => 'Tahun ajaran aktif belum diatur.'])->withInput();
    }

    $statusInfo = $this->getStatusPenilaianSemester(
        (int) $data['jadwal_id'],
        (int) $taAktif->id,
        $taAktif->semester
    );

    if ($statusInfo['status'] === 'final') {
        return back()->withErrors(['msg' => 'Nilai semester ini sudah difinalisasi dan terkunci.'])->withInput();
    }

    $guru = auth()->user()->guru
        ?? Guru::where('user_id', auth()->id())->first();

    $jadwal = Jadwal::with(['rombel', 'mataPelajaran', 'mapel'])
        ->where('id', (int) $data['jadwal_id'])
        ->where('guru_id', $guru->id ?? 0)
        ->where('rombel_id', (int) $data['rombel_id'])
        ->where('mata_pelajaran_id', (int) $data['mata_pelajaran_id'])
        ->whereHas('rombel', function ($r) use ($taAktif) {
            $r->where('tahun_ajaran_id', $taAktif->id)
              ->where(function ($w) {
                  $w->where('aktif', 1)
                    ->orWhereNull('aktif');
              });
        })
        ->firstOrFail();

    $kkm = $jadwal->mataPelajaran->kkm
        ?? $jadwal->mapel->kkm
        ?? null;

    if ($kkm === null || $kkm === '') {
        return back()->withErrors([
            'msg' => 'KKM mata pelajaran belum diatur. Silakan lengkapi KKM pada data mata pelajaran terlebih dahulu.',
        ])->withInput();
    }

    $bobotPraktik = (float) $data['bobot_praktik'];
    $bobotTeori = (float) $data['bobot_teori'];
    $totalBobot = $bobotPraktik + $bobotTeori;

    if (abs($totalBobot - 100) > 0.01) {
        return back()->withErrors([
            'msg' => 'Total bobot praktik dan teori harus 100%.',
        ])->withInput();
    }

    $jenisTp = [
        'tp1' => $data['jenis_tp1'],
        'tp2' => $data['jenis_tp2'],
        'tp3' => $data['jenis_tp3'],
        'tp4' => $data['jenis_tp4'],
    ];

    if ($bobotPraktik > 0 && !in_array('praktik', $jenisTp, true)) {
        return back()->withErrors([
            'msg' => 'Bobot praktik lebih dari 0%, maka minimal satu TP harus dipilih sebagai praktik.',
        ])->withInput();
    }

    if ($bobotTeori > 0 && !in_array('teori', $jenisTp, true)) {
        return back()->withErrors([
            'msg' => 'Bobot teori lebih dari 0%, maka minimal satu TP harus dipilih sebagai teori.',
        ])->withInput();
    }

    $validSiswaIds = $this->siswaQueryUntukJadwal($jadwal)
        ->pluck('s.id')
        ->map(fn ($v) => (int) $v)
        ->all();

    $submittedIds = collect(array_keys($data['nilai'] ?? []))
        ->map(fn ($v) => (int) $v)
        ->all();

    foreach ($submittedIds as $siswaId) {
        abort_unless(
            in_array($siswaId, $validSiswaIds, true),
            403,
            'Ada siswa yang tidak valid untuk penilaian mapel ini.'
        );
    }

    $prefixMap = [
        'LM1' => 'lm1',
        'LM2' => 'lm2',
        'LM3' => 'lm3',
        'LM4' => 'lm4',
    ];

    $prefix = $prefixMap[$data['komponen']];

    DB::beginTransaction();

    try {
        foreach (($data['nilai'] ?? []) as $siswaId => $item) {
            $siswaId = (int) $siswaId;

            $tp1 = $this->normalizeNilai($item['tp1'] ?? null);
            $tp2 = $this->normalizeNilai($item['tp2'] ?? null);
            $tp3 = $this->normalizeNilai($item['tp3'] ?? null);
            $tp4 = $this->normalizeNilai($item['tp4'] ?? null);

            $allNull = $tp1 === null && $tp2 === null && $tp3 === null && $tp4 === null;

            $where = [
                'jadwal_id'       => (int) $data['jadwal_id'],
                'siswa_id'        => $siswaId,
                'tahun_ajaran_id' => (int) $taAktif->id,
                'semester'        => $taAktif->semester,
            ];

            $existingRow = DB::table('nilai')->where($where)->first();

            if ($allNull && !$existingRow) {
                continue;
            }

            $lmNilai = $allNull
                ? null
                : $this->hitungNilaiLmBerbobot(
                    [
                        'tp1' => $tp1,
                        'tp2' => $tp2,
                        'tp3' => $tp3,
                        'tp4' => $tp4,
                    ],
                    $jenisTp,
                    $bobotPraktik,
                    $bobotTeori
                );

            DB::table('nilai')->updateOrInsert(
                $where,
                [
                    "{$prefix}_tp1"    => $tp1,
                    "{$prefix}_tp2"    => $tp2,
                    "{$prefix}_tp3"    => $tp3,
                    "{$prefix}_tp4"    => $tp4,
                    "{$prefix}_nilai"  => $lmNilai,
                    'status_penilaian' => 'draft',
                    'updated_at'       => now(),
                    'created_at'       => $existingRow->created_at ?? now(),
                ]
            );

            $row = DB::table('nilai')->where($where)->first();

            $nilaiAkhir = $this->averageNullable([
                $row->lm1_nilai ?? null,
                $row->lm2_nilai ?? null,
                $row->lm3_nilai ?? null,
                $row->lm4_nilai ?? null,
            ]);

            $statusKetuntasan = 'tidak_tuntas';

            if ($nilaiAkhir !== null) {
                $statusKetuntasan = $nilaiAkhir >= (float) $kkm ? 'tuntas' : 'tidak_tuntas';
            }

            DB::table('nilai')
                ->where($where)
                ->update([
                    'nilai_akhir'      => $nilaiAkhir,
                    'status'           => $statusKetuntasan,
                    'status_penilaian' => 'draft',
                    'updated_at'       => now(),
                ]);
        }

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();

        return back()->withErrors([
            'msg' => 'Gagal menyimpan nilai: ' . $e->getMessage(),
        ])->withInput();
    }

    return redirect()->route('guru.penilaian.create', [
        'rombel_id'         => $data['rombel_id'],
        'mata_pelajaran_id' => $data['mata_pelajaran_id'],
        'komponen'          => $data['komponen'],
    ])->with('success', 'Nilai berhasil disimpan. TP bernilai 0 tidak ikut dihitung.');
}
public function downloadTemplateExcel(Request $request)
{
    $data = $request->validate([
        'jadwal_id' => ['required', 'integer'],
    ]);

    $taAktif = $this->getTahunAjaranAktif();

    if (!$taAktif) {
        return back()->withErrors(['msg' => 'Tahun ajaran aktif belum diatur.']);
    }

    $guru = auth()->user()->guru
        ?? Guru::where('user_id', auth()->id())->first();

    $jadwal = Jadwal::with(['rombel', 'mataPelajaran', 'mapel'])
        ->where('id', (int) $data['jadwal_id'])
        ->where('guru_id', $guru->id ?? 0)
        ->whereHas('rombel', function ($r) use ($taAktif) {
            $r->where('tahun_ajaran_id', $taAktif->id)
              ->where(function ($w) {
                  $w->where('aktif', 1)
                    ->orWhereNull('aktif');
              });
        })
        ->firstOrFail();

    $siswa = $this->siswaQueryUntukJadwal($jadwal)
        ->orderBy('s.nama')
        ->get();

    $nilai = DB::table('nilai')
        ->where('jadwal_id', $jadwal->id)
        ->where('tahun_ajaran_id', $taAktif->id)
        ->where('semester', $taAktif->semester)
        ->whereIn('siswa_id', $siswa->pluck('id'))
        ->get()
        ->keyBy('siswa_id');

    $namaMapel = $jadwal->mataPelajaran->nama_mapel
        ?? $jadwal->mapel->nama_mapel
        ?? $jadwal->mataPelajaran->nama
        ?? $jadwal->mapel->nama
        ?? 'Mapel';

    $namaRombel = $jadwal->rombel->nama_rombel
        ?? $jadwal->rombel->nama_kelas
        ?? $jadwal->rombel->nama
        ?? '-';

    $kkm = $jadwal->mataPelajaran->kkm
        ?? $jadwal->mapel->kkm
        ?? null;

    $spreadsheet = new Spreadsheet();
    $lmList = ['LM1', 'LM2', 'LM3', 'LM4'];

    foreach ($lmList as $index => $lm) {
        $sheet = $index === 0
            ? $spreadsheet->getActiveSheet()
            : $spreadsheet->createSheet($index);

        $this->buatSheetLmExcel(
            $sheet,
            $lm,
            $siswa,
            $nilai,
            $namaRombel,
            $namaMapel,
            $taAktif,
            $kkm
        );
    }

    $spreadsheet->setActiveSheetIndex(0);

    $filename = 'template-nilai-' . Str::slug($namaRombel . '-' . $namaMapel . '-' . ($taAktif->nama_tahun ?? '')) . '.xlsx';

    return response()->streamDownload(function () use ($spreadsheet) {
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

private function buatSheetLmExcel($sheet, string $lm, $siswa, $nilai, string $namaRombel, string $namaMapel, $taAktif, $kkm): void
{
    $prefixMap = [
        'LM1' => 'lm1',
        'LM2' => 'lm2',
        'LM3' => 'lm3',
        'LM4' => 'lm4',
    ];

    $prefix = $prefixMap[$lm];

    $sheet->setTitle($lm);

    $sheet->mergeCells('A1:H1');
    $sheet->setCellValue('A1', 'TEMPLATE INPUT NILAI ' . $lm);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A3', 'Kelas');
    $sheet->setCellValue('B3', $namaRombel);

    $sheet->setCellValue('A4', 'Mata Pelajaran');
    $sheet->setCellValue('B4', $namaMapel);

    $sheet->setCellValue('A5', 'Lingkup Materi');
    $sheet->setCellValue('B5', $lm);

    $sheet->setCellValue('A6', 'Tahun Ajaran');
    $sheet->setCellValue('B6', $taAktif->nama_tahun ?? '-');

    $sheet->setCellValue('A7', 'Semester');
    $sheet->setCellValue('B7', $taAktif->semester ?? '-');

    $sheet->setCellValue('A8', 'KKM');
    $sheet->setCellValue('B8', $kkm ?? '-');

    $sheet->setCellValue('A10', 'Petunjuk');
    $sheet->setCellValue('B10', 'Isi kategori TP, bobot, dan nilai siswa. Nilai 0 dianggap kosong/tidak digunakan.');

    $sheet->setCellValue('A13', 'Bobot Praktik (%)');
    $sheet->setCellValue('B13', 50);

    $sheet->setCellValue('A14', 'Bobot Teori (%)');
    $sheet->setCellValue('B14', '=100-B13');

    $sheet->setCellValue('A15', 'Total Bobot (%)');
    $sheet->setCellValue('B15', '=SUM(B13:B14)');

    $sheet->setCellValue('A18', 'Kategori TP');
    $sheet->setCellValue('D18', 'Praktik');
    $sheet->setCellValue('E18', 'Praktik');
    $sheet->setCellValue('F18', 'Teori');
    $sheet->setCellValue('G18', 'Teori');

    foreach (['D18', 'E18', 'F18', 'G18'] as $cell) {
        $validation = $sheet->getCell($cell)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"Praktik,Teori"');
    }

    $headers = [
        'A19' => 'No',
        'B19' => 'NIS/NISN',
        'C19' => 'Nama Siswa',
        'D19' => 'TP1',
        'E19' => 'TP2',
        'F19' => 'TP3',
        'G19' => 'TP4',
        'H19' => 'Nilai LM',
        'I19' => 'SISWA_ID',
    ];

    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

    $startRow = 20;

    foreach ($siswa as $index => $s) {
        $rowNumber = $startRow + $index;
        $rowNilai = $nilai[$s->id] ?? null;

        $sheet->setCellValue("A{$rowNumber}", $index + 1);
        $sheet->setCellValue("B{$rowNumber}", $s->nis ?? $s->nisn ?? '');
        $sheet->setCellValue("C{$rowNumber}", $s->nama ?? '-');

        $sheet->setCellValue("D{$rowNumber}", $rowNilai?->{$prefix . '_tp1'} ?? 0);
        $sheet->setCellValue("E{$rowNumber}", $rowNilai?->{$prefix . '_tp2'} ?? 0);
        $sheet->setCellValue("F{$rowNumber}", $rowNilai?->{$prefix . '_tp3'} ?? 0);
        $sheet->setCellValue("G{$rowNumber}", $rowNilai?->{$prefix . '_tp4'} ?? 0);

        $sheet->setCellValue("H{$rowNumber}", $this->formulaNilaiLmExcel($rowNumber));

        // Disembunyikan. Dipakai sistem saat import, tidak perlu diedit guru.
        $sheet->setCellValue("I{$rowNumber}", $s->id);
    }

    $lastRow = max($startRow, $startRow + $siswa->count() - 1);

    $sheet->getStyle('A19:I19')->getFont()->setBold(true);
    $sheet->getStyle('A19:I19')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB('E0E7FF');

    $sheet->getStyle('A1:I' . $lastRow)
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER);

    $sheet->getStyle('A19:I' . $lastRow)
        ->getBorders()
        ->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);

    $sheet->getColumnDimension('A')->setWidth(6);
    $sheet->getColumnDimension('B')->setWidth(18);
    $sheet->getColumnDimension('C')->setWidth(34);
    $sheet->getColumnDimension('D')->setWidth(12);
    $sheet->getColumnDimension('E')->setWidth(12);
    $sheet->getColumnDimension('F')->setWidth(12);
    $sheet->getColumnDimension('G')->setWidth(12);
    $sheet->getColumnDimension('H')->setWidth(14);

    // Siswa ID tetap ada tapi disembunyikan.
    $sheet->getColumnDimension('I')->setVisible(false);

    $sheet->getStyle('D20:H' . $lastRow)
        ->getNumberFormat()
        ->setFormatCode('0.00');

    $sheet->getStyle('B13:B15')
        ->getNumberFormat()
        ->setFormatCode('0.00');

    foreach (range($startRow, $lastRow) as $rowNumber) {
        foreach (['D', 'E', 'F', 'G'] as $col) {
            $validation = $sheet->getCell("{$col}{$rowNumber}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_DECIMAL);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setOperator(DataValidation::OPERATOR_BETWEEN);
            $validation->setFormula1('0');
            $validation->setFormula2('100');
        }
    }

    $sheet->freezePane('A20');
    $sheet->setAutoFilter('A19:H' . $lastRow);
}

private function formulaNilaiLmExcel(int $rowNumber): string
{
    return '=IF(SUMPRODUCT(--(D'.$rowNumber.':G'.$rowNumber.'>0))=0,"",ROUND(IF(AND(SUMPRODUCT(--($D$18:$G$18="Praktik"),--(D'.$rowNumber.':G'.$rowNumber.'>0))>0,SUMPRODUCT(--($D$18:$G$18="Teori"),--(D'.$rowNumber.':G'.$rowNumber.'>0))>0),(SUMPRODUCT(--($D$18:$G$18="Praktik"),D'.$rowNumber.':G'.$rowNumber.',--(D'.$rowNumber.':G'.$rowNumber.'>0))/SUMPRODUCT(--($D$18:$G$18="Praktik"),--(D'.$rowNumber.':G'.$rowNumber.'>0))*$B$13/100)+(SUMPRODUCT(--($D$18:$G$18="Teori"),D'.$rowNumber.':G'.$rowNumber.',--(D'.$rowNumber.':G'.$rowNumber.'>0))/SUMPRODUCT(--($D$18:$G$18="Teori"),--(D'.$rowNumber.':G'.$rowNumber.'>0))*$B$14/100),IF(SUMPRODUCT(--($D$18:$G$18="Praktik"),--(D'.$rowNumber.':G'.$rowNumber.'>0))>0,SUMPRODUCT(--($D$18:$G$18="Praktik"),D'.$rowNumber.':G'.$rowNumber.',--(D'.$rowNumber.':G'.$rowNumber.'>0))/SUMPRODUCT(--($D$18:$G$18="Praktik"),--(D'.$rowNumber.':G'.$rowNumber.'>0)),SUMPRODUCT(--($D$18:$G$18="Teori"),D'.$rowNumber.':G'.$rowNumber.',--(D'.$rowNumber.':G'.$rowNumber.'>0))/SUMPRODUCT(--($D$18:$G$18="Teori"),--(D'.$rowNumber.':G'.$rowNumber.'>0)))),2))';
}

public function importExcel(Request $request)
{
    $data = $request->validate([
        'jadwal_id'  => ['required', 'integer'],
        'file_excel' => ['required', 'file', 'mimes:xlsx,xls'],
    ]);

    $taAktif = $this->getTahunAjaranAktif();

    if (!$taAktif) {
        return back()->withErrors(['msg' => 'Tahun ajaran aktif belum diatur.']);
    }

    $statusInfo = $this->getStatusPenilaianSemester(
        (int) $data['jadwal_id'],
        (int) $taAktif->id,
        $taAktif->semester
    );

    if ($statusInfo['status'] === 'final') {
        return back()->withErrors(['msg' => 'Nilai semester ini sudah difinalisasi dan terkunci.']);
    }

    $guru = auth()->user()->guru
        ?? Guru::where('user_id', auth()->id())->first();

    $jadwal = Jadwal::with(['rombel', 'mataPelajaran', 'mapel'])
        ->where('id', (int) $data['jadwal_id'])
        ->where('guru_id', $guru->id ?? 0)
        ->whereHas('rombel', function ($r) use ($taAktif) {
            $r->where('tahun_ajaran_id', $taAktif->id)
              ->where(function ($w) {
                  $w->where('aktif', 1)
                    ->orWhereNull('aktif');
              });
        })
        ->firstOrFail();

    $kkm = $jadwal->mataPelajaran->kkm
        ?? $jadwal->mapel->kkm
        ?? null;

    if ($kkm === null || $kkm === '') {
        return back()->withErrors([
            'msg' => 'KKM mata pelajaran belum diatur. Silakan lengkapi KKM pada data mata pelajaran terlebih dahulu.',
        ]);
    }

    $validSiswaRows = $this->siswaQueryUntukJadwal($jadwal)->get();

    $validSiswaIds = $validSiswaRows
        ->pluck('id')
        ->map(fn ($v) => (int) $v)
        ->all();

    $validSiswaByNis = [];

    foreach ($validSiswaRows as $s) {
        if (!empty($s->nis)) {
            $validSiswaByNis[(string) $s->nis] = (int) $s->id;
        }

        if (!empty($s->nisn)) {
            $validSiswaByNis[(string) $s->nisn] = (int) $s->id;
        }
    }

    $spreadsheet = IOFactory::load($request->file('file_excel')->getRealPath());

    $lmList = [
        'LM1' => 'lm1',
        'LM2' => 'lm2',
        'LM3' => 'lm3',
        'LM4' => 'lm4',
    ];

    $jumlahImport = 0;

    DB::beginTransaction();

    try {
        foreach ($lmList as $lm => $prefix) {
            $sheet = $spreadsheet->getSheetByName($lm);

            if (!$sheet) {
                throw new \Exception("Sheet {$lm} tidak ditemukan. Gunakan template Excel dari sistem.");
            }

            $jenisTp = [
                'tp1' => strtolower(trim((string) $sheet->getCell('D18')->getCalculatedValue())),
                'tp2' => strtolower(trim((string) $sheet->getCell('E18')->getCalculatedValue())),
                'tp3' => strtolower(trim((string) $sheet->getCell('F18')->getCalculatedValue())),
                'tp4' => strtolower(trim((string) $sheet->getCell('G18')->getCalculatedValue())),
            ];

            foreach ($jenisTp as $key => $jenis) {
                if (!in_array($jenis, ['praktik', 'teori'], true)) {
                    throw new \Exception("Sheet {$lm}: " . strtoupper($key) . ' harus berisi Praktik atau Teori.');
                }
            }

            $bobotPraktik = (float) str_replace(',', '.', (string) $sheet->getCell('B13')->getCalculatedValue());
            $bobotTeori = (float) str_replace(',', '.', (string) $sheet->getCell('B14')->getCalculatedValue());

            if (abs(($bobotPraktik + $bobotTeori) - 100) > 0.01) {
                throw new \Exception("Sheet {$lm}: total bobot praktik dan teori harus 100%.");
            }

            $highestRow = $sheet->getHighestRow();

            for ($row = 20; $row <= $highestRow; $row++) {
                $nis = trim((string) $sheet->getCell("B{$row}")->getCalculatedValue());
                $namaSiswa = trim((string) $sheet->getCell("C{$row}")->getCalculatedValue());
                $siswaId = (int) $sheet->getCell("I{$row}")->getCalculatedValue();

                if (!$siswaId && $nis !== '' && isset($validSiswaByNis[$nis])) {
                    $siswaId = $validSiswaByNis[$nis];
                }

                if (!$siswaId && $namaSiswa === '') {
                    continue;
                }

                if (!in_array($siswaId, $validSiswaIds, true)) {
                    throw new \Exception("Sheet {$lm}, baris {$row}: siswa tidak valid untuk kelas/mapel ini.");
                }

                $tp1 = $this->normalizeNilai($sheet->getCell("D{$row}")->getCalculatedValue());
                $tp2 = $this->normalizeNilai($sheet->getCell("E{$row}")->getCalculatedValue());
                $tp3 = $this->normalizeNilai($sheet->getCell("F{$row}")->getCalculatedValue());
                $tp4 = $this->normalizeNilai($sheet->getCell("G{$row}")->getCalculatedValue());

                $allNull = $tp1 === null && $tp2 === null && $tp3 === null && $tp4 === null;

                if ($allNull) {
                    continue;
                }

                $lmNilai = $this->hitungNilaiLmBerbobot(
                    [
                        'tp1' => $tp1,
                        'tp2' => $tp2,
                        'tp3' => $tp3,
                        'tp4' => $tp4,
                    ],
                    $jenisTp,
                    $bobotPraktik,
                    $bobotTeori
                );

                $where = [
                    'jadwal_id'       => (int) $data['jadwal_id'],
                    'siswa_id'        => (int) $siswaId,
                    'tahun_ajaran_id' => (int) $taAktif->id,
                    'semester'        => $taAktif->semester,
                ];

                $exists = DB::table('nilai')->where($where)->exists();

                $payload = [
                    "{$prefix}_tp1"    => $tp1,
                    "{$prefix}_tp2"    => $tp2,
                    "{$prefix}_tp3"    => $tp3,
                    "{$prefix}_tp4"    => $tp4,
                    "{$prefix}_nilai"  => $lmNilai,
                    'status_penilaian' => 'draft',
                    'updated_at'       => now(),
                ];

                if (!$exists) {
                    $payload['created_at'] = now();
                }

                DB::table('nilai')->updateOrInsert($where, $payload);

                $rowNilai = DB::table('nilai')->where($where)->first();

                $nilaiAkhir = $this->averageNullable([
                    $rowNilai->lm1_nilai ?? null,
                    $rowNilai->lm2_nilai ?? null,
                    $rowNilai->lm3_nilai ?? null,
                    $rowNilai->lm4_nilai ?? null,
                ]);

                $statusKetuntasan = 'tidak_tuntas';

                if ($nilaiAkhir !== null) {
                    $statusKetuntasan = $nilaiAkhir >= (float) $kkm ? 'tuntas' : 'tidak_tuntas';
                }

                DB::table('nilai')
                    ->where($where)
                    ->update([
                        'nilai_akhir'      => $nilaiAkhir,
                        'status'           => $statusKetuntasan,
                        'status_penilaian' => 'draft',
                        'updated_at'       => now(),
                    ]);

                $jumlahImport++;
            }
        }

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();

        return back()->withErrors([
            'msg' => 'Gagal import Excel: ' . $e->getMessage(),
        ]);
    }

    if ($jumlahImport === 0) {
        return back()->withErrors([
            'msg' => 'Tidak ada nilai yang diimport. Pastikan TP1–TP4 pada minimal satu LM sudah diisi lebih dari 0.',
        ]);
    }

    return redirect()
        ->route('guru.penilaian.index')
        ->with('success', "Import Excel berhasil. {$jumlahImport} data nilai diperbarui dari LM1 sampai LM4.");
}

    public function finalize(Request $request)
    {
        $data = $request->validate([
            'jadwal_id' => ['required', 'integer'],
        ]);

$taAktif = $this->getTahunAjaranAktif();

if (!$taAktif) {
    return back()->withErrors(['msg' => 'Tahun ajaran aktif belum diatur.']);
}

$jadwal = Jadwal::with(['rombel', 'mataPelajaran', 'mapel'])
    ->where('id', $data['jadwal_id'])
    ->whereHas('rombel', function ($r) use ($taAktif) {
        $r->where('tahun_ajaran_id', $taAktif->id)
          ->where(function ($w) {
              $w->where('aktif', 1)
                ->orWhereNull('aktif');
          });
    })
    ->firstOrFail();

        $guru = auth()->user()->guru ?? Guru::where('user_id', auth()->id())->first();

        if (($jadwal->guru_id ?? null) !== ($guru->id ?? null)) {
            abort(403);
        }


        if (!$taAktif) {
            return back()->withErrors(['msg' => 'Tahun ajaran aktif belum diatur.']);
        }

        $p = $this->buildProgress($jadwal, $taAktif->id, $taAktif->semester);

        if ($p['missing_any'] > 0) {
            return back()->withErrors([
                'msg' => 'Finalisasi gagal: masih ada nilai yang kosong pada LM1/LM2/LM3/LM4.',
            ]);
        }

        $siswaIds = $this->siswaQueryUntukJadwal($jadwal)
            ->pluck('s.id');

        DB::table('nilai')
            ->where('jadwal_id', $jadwal->id)
            ->where('tahun_ajaran_id', $taAktif->id)
            ->where('semester', $taAktif->semester)
            ->whereIn('siswa_id', $siswaIds)
            ->update([
                'status_penilaian' => 'final',
                'finalized_at'     => now(),
                'finalized_by'     => auth()->id(),
                'updated_at'       => now(),
            ]);

        return back()->with('success', 'Finalisasi berhasil untuk semester ' . ucfirst($taAktif->semester) . '.');
    }

    private function buildProgress(Jadwal $jadwal, int $tahunAjaranId, string $semester): array
    {
        $siswa = $this->siswaQueryUntukJadwal($jadwal)
            ->orderBy('s.nama')
            ->get();

        $nilai = DB::table('nilai')
            ->where('jadwal_id', $jadwal->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $semester)
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $total = $siswa->count();

        $miss = [
            'LM1' => 0,
            'LM2' => 0,
            'LM3' => 0,
            'LM4' => 0,
        ];

        $missingAny = 0;

        foreach ($siswa as $s) {
            $row = $nilai[$s->id] ?? null;
            $hasMiss = false;

            if ($row?->lm1_nilai === null) {
                $miss['LM1']++;
                $hasMiss = true;
            }

            if ($row?->lm2_nilai === null) {
                $miss['LM2']++;
                $hasMiss = true;
            }

            if ($row?->lm3_nilai === null) {
                $miss['LM3']++;
                $hasMiss = true;
            }

            if ($row?->lm4_nilai === null) {
                $miss['LM4']++;
                $hasMiss = true;
            }

            if ($hasMiss) {
                $missingAny++;
            }
        }

        return [
            'total'       => $total,
            'missing'     => $miss,
            'missing_any' => $missingAny,
        ];
    }

    private function getStatusPenilaianSemester(int $jadwalId, int $tahunAjaranId, string $semester): array
    {
        $jadwal = Jadwal::with(['mataPelajaran', 'mapel'])->find($jadwalId);

        if (!$jadwal) {
            return [
                'status'       => 'draft',
                'finalized_at' => null,
                'finalized_by' => null,
            ];
        }

        $siswaIds = $this->siswaQueryUntukJadwal($jadwal)
            ->pluck('s.id');

        $rows = DB::table('nilai')
            ->where('jadwal_id', $jadwalId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $semester)
            ->whereIn('siswa_id', $siswaIds)
            ->get();

        if ($rows->isEmpty()) {
            return [
                'status'       => 'draft',
                'finalized_at' => null,
                'finalized_by' => null,
            ];
        }

        $totalSiswa = $siswaIds->count();

        $allFinal = $totalSiswa > 0
            && $rows->count() === $totalSiswa
            && $rows->where('status_penilaian', 'final')->count() === $totalSiswa;

        return [
            'status'       => $allFinal ? 'final' : 'draft',
            'finalized_at' => $rows->where('status_penilaian', 'final')->max('finalized_at'),
            'finalized_by' => $rows->where('status_penilaian', 'final')->max('finalized_by'),
        ];
    }

    private function getTahunAjaranAktif()
    {
        $taQuery = TahunAjaran::query();
        $hasAny = false;

        $taQuery->where(function ($w) use (&$hasAny) {
            if (Schema::hasColumn('tahun_ajaran', 'is_aktif')) {
                $w->orWhere('is_aktif', 1);
                $hasAny = true;
            }

            if (Schema::hasColumn('tahun_ajaran', 'aktif')) {
                $w->orWhere('aktif', 1);
                $hasAny = true;
            }

            if (Schema::hasColumn('tahun_ajaran', 'is_active')) {
                $w->orWhere('is_active', 1);
                $hasAny = true;
            }

            if (Schema::hasColumn('tahun_ajaran', 'status')) {
                $w->orWhere('status', 'aktif');
                $hasAny = true;
            }
        });

        return ($hasAny ? $taQuery : TahunAjaran::query())
            ->orderByDesc('id')
            ->first();
    }

private function siswaQueryUntukJadwal(Jadwal $jadwal)
{
    $mapel = $jadwal->mataPelajaran->nama_mapel
        ?? $jadwal->mapel->nama_mapel
        ?? $jadwal->mapel->nama
        ?? null;

    $tahunAjaranId = $jadwal->rombel->tahun_ajaran_id
        ?? DB::table('rombel')
            ->where('id', $jadwal->rombel_id)
            ->value('tahun_ajaran_id');

    $query = DB::table('siswa as s')
        ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 's.id')
        ->where('sr.rombel_id', $jadwal->rombel_id)
        ->where('sr.aktif', 1)
        ->when($tahunAjaranId, function ($q) use ($tahunAjaranId) {
            $q->where('sr.tahun_ajaran_id', $tahunAjaranId);
        })
        ->select('s.*');

    if ($this->isMapelAgama($mapel)) {
        $agama = $this->agamaDariMapel($mapel);

        $query->whereRaw('LOWER(s.agama) = ?', [strtolower($agama)]);
    }

    return $query;
}

    private function isMapelAgama(?string $namaMapel): bool
    {
        return $namaMapel && Str::startsWith($namaMapel, 'Pendidikan Agama');
    }

    private function agamaDariMapel(?string $namaMapel): string
    {
        $namaMapel = trim((string) $namaMapel);

        return trim(Str::after($namaMapel, 'Pendidikan Agama'));
    }

private function normalizeNilai($value): ?float
{
    if ($value === null || $value === '') {
        return null;
    }

    if (is_string($value)) {
        $value = trim(str_replace(',', '.', $value));
    }

    if ($value === '') {
        return null;
    }

    $value = (float) $value;

    if ($value <= 0) {
        return null;
    }

    return max(0, min(100, $value));
}

    private function averageNullable(array $values): ?float
    {
        $filtered = collect($values)->filter(fn ($v) => $v !== null && $v !== '');

        return $filtered->isEmpty() ? null : round($filtered->avg(), 2);
    }

private function hitungNilaiLmBerbobot(array $tp, array $jenisTp, float $bobotPraktik, float $bobotTeori): ?float
{
    $nilaiPraktik = [];
    $nilaiTeori = [];

    foreach ([1, 2, 3, 4] as $i) {
        $key = "tp{$i}";
        $nilai = $tp[$key] ?? null;
        $jenis = $jenisTp[$key] ?? null;

        if ($nilai === null || $nilai === '') {
            continue;
        }

        if ($jenis === 'praktik') {
            $nilaiPraktik[] = (float) $nilai;
        }

        if ($jenis === 'teori') {
            $nilaiTeori[] = (float) $nilai;
        }
    }

    $adaPraktik = count($nilaiPraktik) > 0;
    $adaTeori = count($nilaiTeori) > 0;

    if (!$adaPraktik && !$adaTeori) {
        return null;
    }

    $rataPraktik = $adaPraktik
        ? array_sum($nilaiPraktik) / count($nilaiPraktik)
        : null;

    $rataTeori = $adaTeori
        ? array_sum($nilaiTeori) / count($nilaiTeori)
        : null;

    if ($adaPraktik && $adaTeori) {
        return round(
            ($rataPraktik * ($bobotPraktik / 100)) +
            ($rataTeori * ($bobotTeori / 100)),
            2
        );
    }

    if ($adaPraktik) {
        return round($rataPraktik, 2);
    }

    return round($rataTeori, 2);
}
}