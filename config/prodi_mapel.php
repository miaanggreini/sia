<?php
// config/prodi_mapel.php
// Sumber mapping: Lampiran 4 Panduan Pemilihan Mapel Pilihan (Kurikulum Merdeka)
// Pastikan nama mapel di sini identik dengan 'mata_pelajaran.nama_mapel'

return [

    // ===== RUMPUN HUMANIORA =====
    'seni' => [
        'nama'  => 'Seni',
        'mapel' => ['Seni Budaya'],
    ],
    'sejarah' => [
        'nama'  => 'Sejarah',
        'mapel' => ['Sejarah'],
    ],
    'linguistik' => [
        'nama'  => 'Linguistik',
        'mapel' => ['Bahasa Indonesia', 'Bahasa Inggris', 'Bahasa Inggris Tingkat Lanjut'],
    ],
    'sastra' => [
        'nama'  => 'Sastra',
        'mapel' => ['Bahasa Indonesia', 'Bahasa Inggris', 'Bahasa Inggris Tingkat Lanjut'],
    ],
    'filsafat' => [
        'nama'  => 'Filsafat',
        'mapel' => ['Sosiologi'],
    ],

    // ===== RUMPUN ILMU SOSIAL =====
    'ilmu-sosial' => [
        'nama'  => 'Ilmu Sosial',
        'mapel' => ['Sosiologi'],
    ],
    'ekonomi' => [
        'nama'  => 'Ekonomi',
        'mapel' => ['Ekonomi', 'Matematika', 'Matematika Tingkat Lanjut'],
    ],
    'pertahanan' => [
        'nama'  => 'Pertahanan',
        'mapel' => ['Pendidikan Pancasila'],
    ],
    'psikologi' => [
        'nama'  => 'Psikologi',
        'mapel' => ['Sosiologi', 'Matematika', 'Matematika Tingkat Lanjut'],
    ],

    // ===== RUMPUN ILMU ALAM =====
    'kimia' => [
        'nama'  => 'Kimia',
        'mapel' => ['Kimia'],
    ],
    'ilmu-kebumian' => [
        'nama'  => 'Ilmu/Sains Kebumian',
        'mapel' => ['Fisika', 'Matematika Tingkat Lanjut'],
    ],
    'ilmu-kelautan' => [
        'nama'  => 'Ilmu/Sains Kelautan',
        'mapel' => ['Biologi'],
    ],
    'biologi' => [
        'nama'  => 'Biologi',
        'mapel' => ['Biologi'],
    ],
    'biofisika' => [
        'nama'  => 'Biofisika',
        'mapel' => ['Fisika'],
    ],
    'fisika' => [
        'nama'  => 'Fisika',
        'mapel' => ['Fisika'],
    ],
    'astronomi' => [
        'nama'  => 'Astronomi',
        'mapel' => ['Fisika', 'Matematika Tingkat Lanjut'],
    ],

    // ===== RUMPUN ILMU FORMAL =====
    'ilmu-komputer' => [
        'nama'  => 'Ilmu Komputer / Informatika',
        'mapel' => ['Matematika Tingkat Lanjut'],
    ],
    'logika' => [
        'nama'  => 'Logika',
        'mapel' => ['Matematika Tingkat Lanjut'],
    ],
    'matematika' => [
        'nama'  => 'Matematika (Ilmu Formal)',
        'mapel' => ['Matematika Tingkat Lanjut'],
    ],

    // ===== RUMPUN ILMU TERAPAN =====
    'pertanian' => [
        'nama'  => 'Ilmu/Sains Pertanian',
        'mapel' => ['Biologi'],
    ],
    'peternakan' => [
        'nama'  => 'Peternakan',
        'mapel' => ['Biologi'],
    ],
    'perikanan' => [
        'nama'  => 'Ilmu/Sains Perikanan',
        'mapel' => ['Biologi'],
    ],
    'arsitektur' => [
        'nama'  => 'Arsitektur',
        'mapel' => ['Matematika', 'Fisika', 'Matematika Tingkat Lanjut'],
    ],
    'perencanaan-wilayah' => [
        'nama'  => 'Perencanaan Wilayah',
        'mapel' => ['Ekonomi', 'Matematika', 'Matematika Tingkat Lanjut'],
    ],
    'desain' => [
        'nama'  => 'Desain',
        'mapel' => ['Seni Budaya', 'Matematika', 'Matematika Tingkat Lanjut'],
    ],
    'akuntansi' => [
        'nama'  => 'Ilmu/Sains Akuntansi',
        'mapel' => ['Ekonomi'],
    ],
    'manajemen' => [
        'nama'  => 'Ilmu/Sains Manajemen',
        'mapel' => ['Ekonomi'],
    ],
    'logistik' => [
        'nama'  => 'Logistik',
        'mapel' => ['Ekonomi'],
    ],
    'administrasi-bisnis' => [
        'nama'  => 'Administrasi Bisnis',
        'mapel' => ['Ekonomi'],
    ],
    'bisnis' => [
        'nama'  => 'Bisnis',
        'mapel' => ['Ekonomi'],
    ],
    'komunikasi' => [
        'nama'  => 'Ilmu/Sains Komunikasi',
        'mapel' => ['Sosiologi'],
    ],
    'pendidikan' => [
        'nama'  => 'Pendidikan (Kependidikan)',
        // catatan lampiran: paling banyak 1 mapel relevan — kita biarkan kosong & isi via kebijakan sekolah bila perlu
        'mapel' => [],
    ],
    'teknik-rekayasa' => [
        'nama'  => 'Teknik / Rekayasa',
        'mapel' => ['Fisika', 'Kimia', 'Matematika Tingkat Lanjut'],
    ],
    'ilmu-lingkungan' => [
        'nama'  => 'Ilmu/Sains Lingkungan',
        'mapel' => ['Biologi'],
    ],
    'kehutanan' => [
        'nama'  => 'Kehutanan',
        'mapel' => ['Biologi'],
    ],
    'kedokteran' => [
        'nama'  => 'Ilmu/Sains Kedokteran',
        'mapel' => ['Biologi', 'Kimia'],
    ],
    'kedokteran-gigi' => [
        'nama'  => 'Ilmu/Sains Kedokteran Gigi',
        'mapel' => ['Biologi', 'Kimia'],
    ],
    'kedokteran-hewan' => [
        'nama'  => 'Ilmu/Sains Veteriner (Kedokteran Hewan)',
        'mapel' => ['Biologi', 'Kimia'],
    ],
    'farmasi' => [
        'nama'  => 'Ilmu Farmasi',
        'mapel' => ['Biologi', 'Kimia'],
    ],
    'gizi' => [
        'nama'  => 'Ilmu/Sains Gizi',
        'mapel' => ['Biologi', 'Kimia'],
    ],
    'kesehatan-masyarakat' => [
        'nama'  => 'Kesehatan Masyarakat',
        'mapel' => ['Biologi'],
    ],
    'kebidanan' => [
        'nama'  => 'Kebidanan',
        'mapel' => ['Biologi'],
    ],
    'keperawatan' => [
        'nama'  => 'Keperawatan',
        'mapel' => ['Biologi'],
    ],
    'kesehatan' => [
        'nama'  => 'Kesehatan',
        'mapel' => ['Biologi'],
    ],
    'ilmu-informasi' => [
        'nama'  => 'Ilmu/Sains Informasi',
        'mapel' => ['Matematika Tingkat Lanjut'],
    ],
    'hukum' => [
        'nama'  => 'Hukum',
        'mapel' => ['Sosiologi', 'Pendidikan Pancasila'],
    ],
    'militer' => [
        'nama'  => 'Ilmu/Sains Militer',
        'mapel' => ['Sosiologi'],
    ],
    'urusan-publik' => [
        'nama'  => 'Urusan Publik',
        'mapel' => ['Sosiologi'],
    ],
    'keolahragaan' => [
        'nama'  => 'Ilmu/Sains Keolahragaan',
        'mapel' => ['Pendidikan Jasmani, Olahraga, dan Kesehatan', 'Biologi'],
    ],
    'pariwisata' => [
        'nama'  => 'Pariwisata',
        'mapel' => ['Ekonomi'],
    ],
    'transportasi' => [
        'nama'  => 'Transportasi',
        'mapel' => ['Matematika Tingkat Lanjut'],
    ],
    'bioteknologi' => [
        'nama'  => 'Bioteknologi / Biokewirausahaan / Bioinformatika',
        'mapel' => ['Biologi', 'Matematika', 'Matematika Tingkat Lanjut'],
    ],
    'geografi-terapan' => [
        'nama'  => 'Geografi / Geografi Lingkungan / SIG',
        'mapel' => ['Geografi', 'Matematika', 'Matematika Tingkat Lanjut'],
    ],
    'informatika-medis' => [
        'nama'  => 'Informatika Medis / Informatika Kesehatan',
        'mapel' => ['Biologi', 'Matematika Tingkat Lanjut'],
    ],
    'konservasi' => [
        'nama'  => 'Konservasi (Biologi/Hewan/Hutan/SDA)',
        'mapel' => ['Biologi'],
    ],
    'teknologi-pangan' => [
        'nama'  => 'Teknologi Pangan / Hasil Pertanian/Peternakan/Perikanan',
        'mapel' => ['Kimia', 'Biologi'],
    ],
    'sains-data' => [
        'nama'  => 'Sains Data',
        'mapel' => ['Matematika Tingkat Lanjut'],
    ],
    'sains-perkopian' => [
        'nama'  => 'Sains Perkopian',
        'mapel' => ['Biologi'],
    ],
    'studi-humanitas' => [
        'nama'  => 'Studi Humanitas',
        'mapel' => ['Sosiologi'],
    ],
];
