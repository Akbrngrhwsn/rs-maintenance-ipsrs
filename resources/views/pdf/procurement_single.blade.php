<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengadaan #{{ $procurement->id }}</title>
    <style>
        body { font-family: sans-serif; font-size:12px; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #333; padding:6px; }
        th { background:#f0f0f0; text-align: left; }
        
        /* Helper Text */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-red { color: #d32f2f; }
        .text-green { color: #388e3c; }
        .text-gray { color: #757575; }
        .italic { font-style: italic; }

        /* Status Box jika belum valid */
        .status-box {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 25px; /* Manual vertikal align untuk DomPDF */
        }
    </style>
</head>
<body>
    {{-- KOP SURAT --}}
    <div style="text-align:center; margin-bottom:15px;">
        @php 
            $kopFile = public_path('images/KOPSurat.jfif');
        @endphp
        @if(file_exists($kopFile))
            <img src="{{ $kopFile }}" alt="Kop Surat" style="width:100%; max-height:120px; object-fit:contain;" />
        @else
            <div style="font-size:16px; font-weight:bold;">{{ config('app.name', 'RS Maintenance') }}</div>
            <div style="font-size:12px;">Laporan Pengadaan Barang & Jasa</div>
            <hr style="margin-top:5px; border:1px solid #000;">
        @endif
    </div>

    {{-- HEADER INFO --}}
    <table style="border:none; margin-bottom:15px;">
        <tr style="border:none;">
            <td style="border:none; width:60%;">
                <h3 style="margin:0;">Laporan Pengadaan #{{ $procurement->id }}</h3>
                <div style="margin-top:5px;">Tanggal Pengajuan: {{ $procurement->created_at->format('d/m/Y') }}</div>
            </td>
            <td style="border:none; width:40%; text-align:right;">
                <div style="font-weight:bold; font-size:14px; padding:5px; border:1px solid #333; display:inline-block;">
                    STATUS: {{ strtoupper($procurement->status) }}
                </div>
            </td>
        </tr>
    </table>

    {{-- INFO RELASI --}}
    <div style="margin-bottom: 10px; background: #f9f9f9; padding: 10px; border: 1px solid #ddd;">
        <strong>Informasi Referensi:</strong>
        <br>
        @if($procurement->report)
            Nomor Tiket Laporan: <strong>{{ $procurement->report->ticket_number ?? '-' }}</strong> | 
            Ruangan: <strong>{{ $procurement->report->ruangan ?? '-' }}</strong>
        @else
            <em>(Pengadaan langsung tanpa referensi tiket laporan)</em>
        @endif
    </div>

    {{-- TABEL ITEM --}}
    <h4 style="margin-bottom:5px;">Rincian Item Pengadaan</h4>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">Nama Barang</th>
                <th width="20%">Merk / Spesifikasi</th>
                <th width="10%" class="text-center">Jml</th>
                <th width="15%" class="text-right">Harga Satuan</th>
                <th width="20%" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total = 0;
                $items = is_array($procurement->items) ? $procurement->items : [];
            @endphp
            @forelse($items as $index => $it)
                @php
                    // Normalisasi data (handle perbedaan nama key)
                    $qty = isset($it['quantity']) ? (int)$it['quantity'] : (isset($it['jumlah']) ? (int)$it['jumlah'] : 1);
                    $price = isset($it['unit_price']) ? (float)$it['unit_price'] : (isset($it['harga_satuan']) ? (float)$it['harga_satuan'] : 0);
                    $name = $it['name'] ?? $it['nama'] ?? '-';
                    $brand = $it['brand'] ?? $it['merk'] ?? ($it['spek'] ?? '-');
                    
                    $subtotal = $price * $qty;
                    $total += $subtotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $name }}</td>
                    <td>{{ $brand }}</td>
                    <td class="text-center">{{ $qty }}</td>
                    <td class="text-right">Rp {{ number_format($price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center italic">Tidak ada item barang.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right text-bold" style="background: #f0f0f0;">Total Estimasi Biaya</td>
                <td class="text-right text-bold" style="background: #f0f0f0;">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- CATATAN --}}
    @if($procurement->director_note)
        <div style="margin-top: 15px;">
            <strong>Catatan Direktur:</strong>
            <div style="border:1px dashed #555; padding:8px; margin-top:5px; background: #fffbe6;">
                {{ $procurement->director_note }}
            </div>
        </div>
    @endif

    {{-- AREA TANDA TANGAN --}}
    <br><br>
    <table style="border: none; margin-top: 20px;">
        <tr style="border: none;">
            
            {{-- 1. ADMIN IT (Selalu Muncul) --}}
            <td width="25%" class="text-center" style="border: none; vertical-align: top;">
                <p class="text-bold" style="margin-bottom: 5px;">Diajukan Oleh</p>
                @if(isset($qrAdmin) && $qrAdmin)
                    <img src="data:image/png;base64, {{ $qrAdmin }}" alt="QR Admin" style="width: 70px; height: 70px;">
                    <br>
                    <span style="font-size: 9pt;">Admin IT</span><br>
                    <span style="font-size: 8pt;" class="text-green">(Diajukan)</span>
                @endif
            </td>

            {{-- 2. MANAGER UNIT --}}
            <td width="25%" class="text-center" style="border: none; vertical-align: top;">
                <p class="text-bold" style="margin-bottom: 5px;">Mengetahui</p>
                @if(isset($qrManager) && $qrManager)
                    <img src="data:image/png;base64, {{ $qrManager }}" alt="QR Manager" style="width: 70px; height: 70px;">
                    <br>
                    <span style="font-size: 9pt;">Manager Unit</span><br>
                    <span style="font-size: 8pt;" class="text-green">(Tervalidasi)</span>
                @else
                    <div class="status-box">
                        @if($procurement->status == 'rejected')
                            <span class="text-bold text-red" style="font-size: 9pt;">DITOLAK</span>
                        @else
                            {{-- Jika status submitted_to_manager --}}
                            <span class="italic text-gray" style="font-size: 9pt;">Menunggu<br>Persetujuan</span>
                        @endif
                    </div>
                @endif
            </td>

            {{-- 3. BENDAHARA --}}
            <td width="25%" class="text-center" style="border: none; vertical-align: top;">
                <p class="text-bold" style="margin-bottom: 5px;">Verifikasi</p>
                @if(isset($qrBendahara) && $qrBendahara)
                    <img src="data:image/png;base64, {{ $qrBendahara }}" alt="QR Bendahara" style="width: 70px; height: 70px;">
                    <br>
                    <span style="font-size: 9pt;">Bendahara</span><br>
                    <span style="font-size: 8pt;" class="text-green">(Tervalidasi)</span>
                @else
                    <div class="status-box">
                        @if($procurement->status == 'rejected')
                             <span class="text-gray">-</span>
                        @elseif($procurement->status == 'submitted_to_manager')
                             <span class="italic text-gray" style="font-size: 8pt;">Menunggu<br>Manager</span>
                        @else
                             {{-- Status submitted_to_bendahara --}}
                             <span class="italic text-gray" style="font-size: 9pt;">Menunggu<br>Verifikasi</span>
                        @endif
                    </div>
                @endif
            </td>

            {{-- 4. DIREKTUR --}}
            <td width="25%" class="text-center" style="border: none; vertical-align: top;">
                <p class="text-bold" style="margin-bottom: 5px;">Menyetujui</p>
                @if(isset($qrDirektur) && $qrDirektur)
                    <img src="data:image/png;base64, {{ $qrDirektur }}" alt="QR Direktur" style="width: 70px; height: 70px;">
                    <br>
                    <span style="font-size: 9pt;">Direktur Utama</span><br>
                    <span style="font-size: 8pt;" class="text-green">(Disetujui)</span>
                @else
                    <div class="status-box">
                        @if($procurement->status == 'rejected')
                            <span class="text-gray">-</span>
                        @elseif($procurement->status == 'submitted_to_director')
                             <span class="italic text-gray" style="font-size: 9pt;">Menunggu<br>Persetujuan</span>
                        @else
                             {{-- Masih di Manager atau Bendahara --}}
                             <span class="italic text-gray" style="font-size: 8pt;">Menunggu<br>Validasi Sblmnya</span>
                        @endif
                    </div>
                @endif
            </td>

        </tr>
    </table>

</body>
</html>