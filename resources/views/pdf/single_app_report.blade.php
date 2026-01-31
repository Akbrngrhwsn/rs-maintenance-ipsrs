<!DOCTYPE html>
<html>
<head>
    <title>Laporan Aplikasi Selesai</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; padding: 20px; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .content { margin-bottom: 20px; }
        .label { font-weight: bold; width: 140px; display: inline-block; vertical-align: top; }
        .value { display: inline-block; width: 500px; vertical-align: top; }
        
        /* Box Status */
        .status-box { 
            background-color: #d1e7dd; color: #0f5132; 
            padding: 5px 10px; border-radius: 4px; display: inline-block; font-weight: bold;
            border: 1px solid #badbcc;
        }

        /* List Styling */
        ul { margin-top: 5px; padding-left: 20px; }
        li { margin-bottom: 5px; }

        /* Footer Signature Area */
        .footer-signature {
            margin-top: 50px;
            float: right;
            width: 250px;
            text-align: center;
        }
    </style>
</head>
<body>
    {{-- KOP SURAT --}}
    <div style="text-align:center; margin-bottom:15px;">
        @php
            // Pastikan path image benar
            $kopPath = public_path('images/KOPSurat.jfif');
        @endphp
        
        @if(file_exists($kopPath))
            <img src="{{ $kopPath }}" alt="Kop Surat" style="width:100%; max-height:120px; object-fit:contain;" />
        @else
            <h2>{{ config('app.name') }}</h2>
            <p>Sistem Maintenance Rumah Sakit</p>
            <hr style="border: 1px solid black;">
        @endif
    </div>

    <div class="header">
        <h2 style="margin:0;">PEMBERITAHUAN PENYELESAIAN APLIKASI</h2>
        <p style="margin:5px 0;">Nomor Tiket: {{ $app->ticket_number ?? '-' }}</p>
    </div>

    <div class="content">
        <div style="margin-bottom: 10px;">
            <span class="label">Judul Aplikasi:</span> 
            <span class="value">{{ $app->nama_aplikasi }}</span>
        </div>
        <div style="margin-bottom: 10px;">
            <span class="label">Pemohon:</span> 
            <span class="value">{{ $app->user->name ?? 'User Tidak Dikenal' }}</span>
        </div>
        <div style="margin-bottom: 10px;">
            <span class="label">Tanggal Selesai:</span> 
            <span class="value">{{ $app->updated_at->translatedFormat('d F Y') }}</span>
        </div>
        <div style="margin-bottom: 10px;">
            <span class="label">Status Akhir:</span> 
            <span class="status-box">SELESAI (COMPLETED)</span>
        </div>
        
        <br>
        <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px; background: #f9f9f9;">
            <strong style="display:block; margin-bottom:5px;">Deskripsi Aplikasi:</strong>
            <p style="margin:0; text-align: justify;">{{ $app->deskripsi }}</p>
        </div>

        @if($app->catatan_admin)
            <br>
            <div style="border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                <strong style="display:block; margin-bottom:5px;">Catatan Admin IT:</strong>
                <p style="margin:0;">{{ $app->catatan_admin }}</p>
            </div>
        @endif
        
        @if($app->features && $app->features->count() > 0)
        <h3>Fitur yang Dikembangkan:</h3>
        <ul>
            @foreach($app->features as $feature)
                <li>
                    {{ $feature->nama_fitur }} 
                    @if($feature->is_done)
                        <span style="font-family: DejaVu Sans, sans-serif; color: green; font-weight:bold;">&#10003;</span>
                    @endif
                </li>
            @endforeach
        </ul>
        @endif
    </div>

<p style="margin-bottom: 6px">Demikian laporan ini disusun untuk memberikan gambaran mengenai Pembuatan Aplikasi.
        Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>

    {{-- TANDA TANGAN DENGAN QR --}}
    <div class="footer-signature">
        <p style="margin-bottom: 5px;">Disahkan/Divalidasi Oleh,</p>
        
        {{-- Tampilkan QR Code --}}
        @if(isset($qrCode))
            <img src="data:{{ $qrMime ?? 'image/png' }};base64, {!! $qrCode !!}" alt="QR Validasi" style="width: 90px; height: 90px; margin: 10px 0;">
        @else
            <br><br><br><br>
        @endif
        
        <br>
        
        {{-- Nama Validator --}}
        <span style="font-size: 12px; font-weight: bold; text-decoration: underline;">
            {{ $validator ?? Auth::user()->name }}
        </span>
        <br>
        <span style="font-size: 10px; color: #555;">
            Digital Signature<br>
            {{ $waktuValidasi ?? date('d-m-Y') }}
        </span>
    </div>

</body>
</html>