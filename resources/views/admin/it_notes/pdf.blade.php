<!DOCTYPE html>
<html>
<head>
    <title>Laporan Bulanan Catatan Tim IT</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; font-size: 11px; }
        th { background-color: #f2f2f2; text-align: center; }
        h2 { text-align: center; margin-bottom: 5px; }
        p.date { text-align: center; margin-top: 0; font-size: 14px; color: #555; }
    </style>
</head>
<body>
    <div style="text-align:center; margin-bottom:20px;">
        @php
            $kopPath = public_path('images/KOPSurat.jfif');
        @endphp
        
        @if(file_exists($kopPath))
            <img src="{{ $kopPath }}" alt="Kop Surat" style="width:100%; max-height:120px; object-fit:contain;" />
        @else
            <h1 style="margin:0;">{{ config('app.name') }}</h1>
            <p style="margin:0;">Laporan Catatan Tim IT</p>
            <hr style="border: 1px solid black; margin-top: 10px;">
        @endif
    </div>

    <h2>Laporan Bulanan Catatan Tim IT</h2>
    <p class="date">Periode: {{ \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 20%;">Tanggal & Waktu</th>
                <th>Isi Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($notes as $index => $note)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($note->created_at)->translatedFormat('d F Y, H:i') }}</td>
                <td>{{ $note->note }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; font-style: italic; color: #777;">
                    Tidak ada catatan pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 20px; font-size: 12px;">Demikian laporan catatan ini disusun untuk mendokumentasikan aktivitas teknis operasional Tim IT. Atas perhatiannya, kami ucapkan terima kasih.</p>

    <div style="float: right; width: 200px; text-align: center; margin-top: 30px;">
        <p style="margin-bottom: 5px; font-size: 12px;">Mengetahui/Validasi,</p>
        
        <div style="margin: 10px 0;">
            <img src="data:image/png;base64, {{ $qrCode }}" alt="QR Validasi" style="width: 80px; height: 80px;">
        </div>
        
        <span style="font-size: 12px; font-weight: bold; text-decoration: underline;">
            {{ $validator }}
        </span>
        <br>
        <span style="font-size: 10px; color: #555;">Admin IT Digital Signature</span>
    </div>

    
</body>
</html>