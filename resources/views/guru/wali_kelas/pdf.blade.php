<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Daftar Siswa - {{ $rombel->nama_rombel }}</title>
  <style>
    @page { margin: 24px; }
    body  { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
    h1    { font-size: 16px; margin: 0 0 6px 0; }
    .meta { font-size: 11px; color:#555; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th,td { border:1px solid #ddd; padding: 6px 8px; }
    th    { background:#f5f6f8; text-align:left; }
  </style>
</head>
<body>
  <h1>Daftar Siswa — Kelas {{ $rombel->nama_rombel }}</h1>
  <div class="meta">Dicetak: {{ $tanggal }}</div>

  <table>
    <thead>
      <tr>
        <th style="width: 40px;">No</th>
        <th style="width: 90px;">NIS</th>
        <th>Nama</th>
        <th style="width: 120px;">Jenis Kelamin</th>
        <th style="width: 220px;">Tempat/Tanggal Lahir</th>
      </tr>
    </thead>
    <tbody>
      @foreach($siswa as $i => $s)
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $s->nis }}</td>
          <td>{{ $s->nama }}</td>
          <td>{{ $s->jk_label }}</td>
          <td>{{ ($s->tempat_lahir ?? '—') }}, {{ optional($s->tanggal_lahir)->format('d-m-Y') ?? '—' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
