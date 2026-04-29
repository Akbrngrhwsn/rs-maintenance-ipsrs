<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 11px; /* Sedikit lebih kecil agar lebih elegan */
            color: #333;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            table-layout: fixed; /* Menjaga lebar kolom tetap konsisten */
        }
        th, td { 
            border: 1px solid #000; 
            padding: 8px; 
            text-align: left; 
            word-wrap: break-word;
        }
        th { 
            background-color: #f2f2f2; 
            text-transform: uppercase;
            font-size: 10px;
        }
        .text-center { text-align: center; }
        
        /* Styling Area Tanda Tangan (QR) */
        .signature-wrapper {
            margin-top: 30px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 200px;
            text-align: center;
        }
        .qr-code {
            display: block;
            margin: 10px auto;
            /* Ukuran proporsional untuk dokumen A4 */
            width: 90px; 
            height: 90px;
        }
        .clear { clear: both; }
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
            <h2 style="margin-bottom: 5px;">{{ config('app.name') }}</h2>
            <p style="margin-top: 0;">Sistem Maintenance Rumah Sakit</p>
            <hr style="border: 1px solid black;">
        @endif
    </div>

    <h2 class="text-center" style="margin-bottom: 5px;">Laporan Request User Baru</h2>
    <p class="text-center" style="margin-top: 0;">Bulan: {{ $bulanNama }}</p>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 80px;">Tgl Request</th>
                <th style="width: 90px;">NIP</th>
                <th>Nama Lengkap</th>
                <th>Unit / Ruang</th>
                <th style="width: 80px;">Status Karyawan</th>
                <th style="width: 80px;">Diajukan</th>
                <th style="width: 70px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requests as $index => $r)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $r->created_at->format('d/m/Y') }}</td>
                <td>{{ $r->nip }}</td>
                <td>{{ $r->nama }}</td>
                <td>{{ $r->unit }}</td>
                <td>{{ $r->status_karyawan }}</td>
                <td>{{ $r->user->name ?? '-' }}</td>
                <td class="text-center"><strong>{{ strtoupper($r->status) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- BAGIAN TANDA TANGAN / QR YANG DIPERBAIKI --}}
    <div class="signature-wrapper">
        <div class="signature-box">
            <p style="margin-bottom: 0; font-size: 12px;">Mengetahui/Validasi,</p>
            
            {{-- QR Code dengan margin 1 agar area putih tidak terlalu luas --}}
            <img src="data:image/png;base64, {!! base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(100)
                ->margin(1)
                ->generate(
                    'Laporan Bulanan Request User | Bulan: ' . $bulanNama . ' | Divisi: Admin IT | Dicetak pada: ' . date('d-m-Y')
                )) !!}" class="qr-code" alt="QR Validasi">
            
            <div style="line-height: 1.4;">
                <span style="font-size: 12px; font-weight: bold; text-decoration: underline; text-transform: uppercase;">
                    ADMIN IT
                </span>
                <br>
                <span style="font-size: 10px; color: #666; font-style: italic;">
                    Digital Signature Verified
                </span>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>