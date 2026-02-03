<!DOCTYPE html>
<html>
<head>
    <title>Laporan Bulanan Pengembangan Aplikasi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; padding: 10px; font-size: 12px; color: #333; }
        .header-title { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        
        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; table-layout: fixed; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; font-size: 11px; }
        
        .status-badge { 
            padding: 2px 4px; border-radius: 3px; font-size: 9px; font-weight: bold; text-align: center; display: block;
        }

        /* List Styling inside table */
        ul { margin: 0; padding-left: 15px; }
        li { margin-bottom: 2px; }

        /* Footer Signature Area */
        .footer-signature {
            margin-top: 30px;
            float: right;
            width: 200px;
            text-align: center;
        }
    </style>
</head>
<body>
    {{-- KOP SURAT --}}
    <div style="text-align:center; margin-bottom:15px;">
        @php
            $kopPath = public_path('images/KOPSurat.jfif');
        @endphp
        
        @if(file_exists($kopPath))
            <img src="{{ $kopPath }}" alt="Kop Surat" style="width:100%; max-height:120px; object-fit:contain;" />
        @else
            <h2 style="margin:0;">{{ config('app.name') }}</h2>
            <p style="margin:0;">Sistem Maintenance IT Rumah Sakit</p>
            <hr style="border: 1px solid black; margin-top: 10px;">
        @endif
    </div>

    <div class="header-title">
        <h2 style="margin:0; text-transform: uppercase;">LAPORAN BULANAN PENGEMBANGAN APLIKASI</h2>
        <p style="margin:5px 0;">Periode: {{ \Carbon\Carbon::parse($startDate)->locale('id')->translatedFormat('F Y') }}</p>
    </div>

    <div class="content">
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">No. Tiket / Tgl</th>
                    <th style="width: 20%;">Judul & Pemohon</th>
                    <th style="width: 35%;">Fitur & Deskripsi</th>
                    <th style="width: 15%;">Status</th>
                    <th style="width: 15%;">Waktu Progress</th>
                </tr>
            </thead>
            <tbody>
                @forelse($apps as $app)
                <tr>
                    <td style="text-align: center;">
                        <strong>{{ $app->ticket_number ?? '-' }}</strong><br>
                        <span style="font-size: 9px; color: #666;">{{ $app->created_at->format('d/m/Y') }}</span>
                    </td>
                    <td>
                        <strong>{{ $app->nama_aplikasi }}</strong><br>
                        <span style="font-size: 10px;">Oleh: {{ $app->user->name ?? 'User' }}</span>
                    </td>
                    <td>
                        <div style="font-size: 10px; margin-bottom: 5px; text-align: justify;">
                            {{ Str::limit($app->deskripsi, 100) }}
                        </div>
                        @if($app->features && $app->features->count() > 0)
                            <div style="font-weight: bold; font-size: 9px; margin-top: 5px;">Fitur Utama:</div>
                            <ul>
                                @foreach($app->features as $feature)
                                    <li style="font-size: 9px;">
                                        {{ $feature->nama_fitur }}
                                        @if($feature->is_done)
                                            <span style="color: green;">✔</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td>
                        <div class="status-badge" style="
                            background-color: {{ $app->status == 'completed' ? '#d1e7dd' : ($app->status == 'in_progress' ? '#fff3cd' : '#f8d7da') }};
                            color: {{ $app->status == 'completed' ? '#0f5132' : ($app->status == 'in_progress' ? '#856404' : '#721c24') }};
                            border: 1px solid {{ $app->status == 'completed' ? '#badbcc' : ($app->status == 'in_progress' ? '#ffeeba' : '#f5c6cb') }};
                        ">
                            {{ strtoupper(str_replace('_', ' ', $app->status)) }}
                        </div>
                        <div style="font-size: 8px; text-align: center; margin-top: 3px;">
                            Progress: {{ $app->progress }}%
                        </div>
                    </td>
                    <td style="text-align: center;">
    {{ $app->status == 'completed' ? $app->updated_at->format('d/m/Y') : '-' }}
</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic;">Tidak ada data proyek aplikasi pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p style="margin-top: 20px; font-size: 11px;">
        Demikian laporan bulanan ini disusun untuk memberikan gambaran mengenai status pengembangan aplikasi di lingkungan Rumah Sakit.
        Atas perhatiannya, kami ucapkan terima kasih.
    </p>

    {{-- TANDA TANGAN --}}
    <div class="footer-signature">
        <p style="margin-bottom: 5px; font-size: 11px;">Disahkan/Divalidasi Oleh,</p>
        
        @if(isset($qrCode))
            <img src="data:image/png;base64, {!! $qrCode !!}" alt="QR Validasi" style="width: 80px; height: 80px; margin: 5px 0;">
        @else
            <br><br><br><br>
        @endif
        
        <br>
        <span style="font-size: 11px; font-weight: bold; text-decoration: underline;">
            {{ $validator }}
        </span>
        <br>
        <span style="font-size: 9px; color: #555;">
            Digital Signature<br>
            Tgl Cetak: {{ date('d/m/Y H:i') }}
        </span>
    </div>
</body>
</html>