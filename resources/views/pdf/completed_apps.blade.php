<!DOCTYPE html>
<html>
<head>
    <title>Laporan Aplikasi Selesai</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; font-size: 12px; }
        th { background-color: #d1e7dd; } /* Warna hijau muda */
        h2 { text-align: center; }
    </style>
</head>
<body>
    <div style="text-align:center; margin-bottom:8px;">
        @php $hasImage = extension_loaded('gd') || extension_loaded('imagick'); @endphp
        @if($hasImage)
            @php
                $kopFile = public_path('images/KOPSurat.jfif');
                $hasKopImage = file_exists($kopFile);
            @endphp
            @if($hasKopImage)
                <img src="{{ $kopFile }}" alt="Kop Surat" style="width:100%; max-height:120px; object-fit:contain;" />
            @else
                <div style="text-align:center; margin-bottom:6px;">
                    <div style="font-weight:700; font-size:16px;">{{ config('app.name', 'KOP SURAT') }}</div>
                    <div style="font-size:12px;">Alamat dan kontak instansi</div>
                </div>
                <hr />
            @endif
        @endif
    </div>
    <h2>Laporan Aplikasi Selesai</h2>
    <p>Dicetak pada: {{ now()->translatedFormat('d F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Judul Aplikasi</th>
                <th>Pemohon (Manager)</th>
                <th>Tanggal Selesai</th>
                <th>Catatan Admin</th>
            </tr>
        </thead>
        <tbody>
            @foreach($apps as $app)
            <tr>
                <td>{{ $app->title ?? 'Judul Tidak Ada' }}</td>
                <td>{{ $app->user->name ?? 'User Tidak Dikenal' }}</td>
                {{-- Pastikan kolom updated_at atau kolom khusus tanggal selesai ada --}}
                <td>{{ $app->updated_at->translatedFormat('d F Y') }}</td>
                <td>{{ $app->admin_note ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>