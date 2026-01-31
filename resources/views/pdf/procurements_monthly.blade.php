<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengadaan Bulanan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #ddd; padding:6px; }
        th { background:#f3f4f6; }
        
        /* Tambahan Style untuk Footer Tanda Tangan */
        .footer-signature {
            float: right; 
            width: 220px; 
            text-align: center; 
            margin-top: 30px;
            page-break-inside: avoid; /* Mencegah tanda tangan terpotong ke halaman baru */
        }
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

    <h3>Laporan Pengadaan Bulanan</h3>
    <div>Periode: {{ $monthLabel ?? '' }}</div>
    <br>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Waktu Dibuat</th>
                <th>Tiket / Ruangan</th>
                <th>Status</th>
                <th>Item (Jml x Harga)</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($procurements as $p)
                @php $pTotal = 0; @endphp
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ optional($p->created_at)->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($p->report)
                            {{ $p->report->ticket_number ?? '' }} / {{ $p->report->ruangan ?? '' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $p->status_label ?? $p->status }}</td>
                    <td>
                        <table style="width:100%; border:none;">
                        @php $items = is_array($p->items) ? $p->items : []; @endphp
                        @foreach($items as $it)
                            @php
                                $qty = isset($it['quantity']) ? (int)$it['quantity'] : (isset($it['jumlah']) ? (int)$it['jumlah'] : 1);
                                $price = isset($it['unit_price']) ? (float)$it['unit_price'] : (isset($it['harga_satuan']) ? (float)$it['harga_satuan'] : (isset($it['harga']) ? (float)$it['harga'] : (isset($it['biaya']) ? (float)$it['biaya'] : 0)));
                                $name = $it['name'] ?? $it['nama'] ?? '-';
                                $subtotal = $qty * $price; $pTotal += $subtotal;
                            @endphp
                            <tr><td style="border:none;padding:2px;">{{ $name }} ({{ $qty }} x {{ number_format($price,0,',','.') }})</td></tr>
                        @endforeach
                        </table>
                    </td>
                    <td style="text-align:right">{{ number_format($pTotal,0,',','.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-bottom: 6px">Demikian laporan ini disusun untuk memberikan gambaran mengenai Pemeliharaan Sistem IT periode ini.
        Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>

    {{-- BAGIAN TANDA TANGAN DENGAN QR --}}
    <div class="footer-signature">
        

        <p style="margin-bottom: 5px;">Disetujui/Divalidasi Oleh,</p>
        
        @if(isset($qrCode))
            <img src="data:{{ $qrMime ?? 'image/png' }};base64, {!! $qrCode !!}" alt="QR Validasi" style="width: 80px; height: 80px; margin: 5px 0;">
        @else
            <br><br><br>
        @endif
        
        <br>
        
        <span style="font-size: 12px; font-weight: bold; text-decoration: underline;">
            {{ $validator ?? 'Administrator' }}
        </span>
        <br>
        <span style="font-size: 10px; color: #555;">
            {{-- Menggunakan variable waktuValidasi agar sesuai dengan data di dalam QR --}}
            {{ $waktuValidasi ?? date('d F Y') }}
        </span>
    </div>
</body>
</html>