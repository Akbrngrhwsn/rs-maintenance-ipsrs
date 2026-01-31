<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapat {{ $meeting->id }}</title>
    <style> 
        body { font-family: sans-serif; }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header img {
            width: 100%;
            max-height: 150px;
            object-fit: contain;
        }
        .meta-info {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .meta-info td {
            padding: 5px;
        }
        .content {
            margin-bottom: 50px;
        }
        .signature-section {
            width: 100%;
            text-align: right;
            margin-top: 50px;
        }
        .signature-box {
            display: inline-block;
            width: 250px;
            text-align: center;
        }
        .signature-box p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/KOPSurat.jfif') }}" alt="KOP Surat">
    </div>

    <h2 style="text-align: center;">NOTULENSI RAPAT</h2>
    
    <table class="meta-info">
        <tr>
            <td style="width: 150px;"><strong>Judul Rapat</strong></td>
            <td>: {{ $meeting->title }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal</strong></td>
            <td>: {{ \Carbon\Carbon::parse($meeting->meeting_date)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td><strong>Divisi</strong></td>
            <td>: {{ $meeting->division_role ?? 'Umum' }}</td>
        </tr>
    </table>

    <hr>
    
    <div class="content" style="white-space: pre-wrap; text-align: justify;">{!! nl2br(e($meeting->minutes)) !!}</div>
    
    <hr>
    <p style="font-size: 0.9em; color: gray;">
        Dicatat oleh: {{ optional($meeting->creator)->name ?? '-' }} pada {{ $meeting->created_at->format('Y-m-d H:i') }}
        @if($meeting->edited_by)
            | Diedit pada: {{ $meeting->updated_at->format('Y-m-d H:i') }}
        @endif
    </p>

    <div class="signature-section">
        <div class="signature-box">
            <p>Mengetahui,</p>
            <p><strong>{{ $meeting->division_role ?? 'Kepala Divisi' }}</strong></p>
            
            <br>
            
            <img src="data:image/png;base64, {!! base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(100)->generate(
                'Dokumen ini disetujui secara digital oleh Divisi: ' . ($meeting->division_role ?? 'Terkait') . 
                ' | ID Rapat: ' . $meeting->id . 
                ' | Tanggal: ' . $meeting->meeting_date
            )) !!} ">
            
            <br><br>
            <p>( Tanda Tangan Digital )</p>
        </div>
    </div>
</body>
</html>