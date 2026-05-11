<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengadaan Barang Baru #{{ $procurement->id }}</title>
    <style>
        body { font-family: sans-serif; font-size:11px; color: #333; }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #333; padding:6px; }
        th { background:#f0f0f0; text-align: left; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .italic { font-style: italic; }
    </style>
</head>
<body>
    {{-- KOP SURAT --}}
    <div style="text-align:center; margin-bottom:15px;">
        @php 
            $kopFile = public_path('images/KOPSurat.jfif');
        @endphp
        @if(file_exists($kopFile))
            <img src="{{ $kopFile }}" alt="Kop Surat" style="width:100%; max-height:110px;" />
        @else
            <div style="font-size:16px; font-weight:bold;">{{ config('app.name', 'RS Maintenance') }}</div>
            <div style="font-size:12px;">Laporan Pengadaan Barang & Jasa (Non-Maintenance)</div>
            <hr style="margin-top:5px; border:1px solid #000;">
        @endif
    </div>

    {{-- HEADER INFO --}}
    <table style="border:none; margin-bottom:15px;">
        <tr style="border:none;">
            <td style="border:none; width:60%;">
                <h3 style="margin:0;">Pengajuan Barang Baru #{{ $procurement->id }}</h3>
                <div style="margin-top:5px;">Tanggal Pengajuan: {{ $procurement->created_at->format('d/m/Y') }}</div>
            </td>
            <td style="border:none; width:40%; text-align:right;">
                <div style="font-weight:bold; font-size:12px; padding:5px; border:1px solid #333; display:inline-block;">
                    STATUS: {{ strtoupper(str_replace('_', ' ', $procurement->status)) }}
                </div>
            </td>
        </tr>
    </table>

    {{-- INFO RELASI & TUJUAN --}}
    <div style="margin-bottom: 15px; background: #e8f5e9; padding: 10px; border: 1px solid #c8e6c9;">
        <strong>Informasi Pengajuan:</strong><br>
        Ruangan Pemohon: <strong>{{ $procurement->room->name ?? '-' }}</strong><br>
        Tujuan Pengadaan: <strong style="color:#2e7d32;">{{ $procurement->purpose }}</strong>
    </div>

    {{-- TABEL ITEM --}}
    <h4 style="margin-bottom:5px;">Rincian Item Pengadaan</h4>
    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="45%">Nama Barang</th>
                <th width="10%" class="text-center">Jml</th>
                <th width="20%" class="text-right">Harga Satuan</th>
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
                    $qty = isset($it['jumlah']) ? (int)$it['jumlah'] : 1;
                    $price = isset($it['harga_satuan']) ? (float)$it['harga_satuan'] : 0;
                    $name = $it['nama'] ?? '-';
                    
                    $subtotal = $price * $qty;
                    $total += $subtotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $name }}</td>
                    <td class="text-center">{{ $qty }}</td>
                    <td class="text-right">Rp {{ number_format($price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center italic">Tidak ada rincian barang.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right text-bold" style="background: #f0f0f0;">Total Estimasi Biaya</td>
                <td class="text-right text-bold" style="background: #f0f0f0;">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- CATATAN PENOLAKAN --}}
    @if($procurement->reject_note)
        <div style="margin-top: 15px;">
            <strong>Catatan Penolakan:</strong>
            <div style="border:1px dashed #d32f2f; padding:8px; background: #ffebee; color: #c62828;">
                {{ $procurement->reject_note }}
            </div>
        </div>
    @endif

    {{-- AREA TANDA TANGAN (5 KOLOM) --}}
    <div style="margin-top: 30px;">
        <table style="border: none;">
            <tr style="border: none;">
                
                {{-- KEPALA RUANG (DIAJUKAN OLEH) --}}
            <td width="20%" class="text-center" style="border: none; vertical-align: top;">
                <p class="text-bold" style="margin-bottom: 5px;">Diajukan Oleh</p>
                @php
                    $showQrKaru = $qrKaru;
                    // Fallback untuk data lama yang qr_karu-nya masih kosong
                    if(empty($showQrKaru)) {
                        $showQrKaru = base64_encode((string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(100)->generate("Diajukan oleh: " . ($procurement->user->name ?? 'Karu')));
                    }
                @endphp
                <img src="data:image/png;base64,{!! trim($showQrKaru) !!}" style="width: 65px; height: 65px;" />
                <br><span style="font-size: 8pt;">Kepala Ruang</span>
            </td>

                {{-- ADMIN --}}
                <td width="20%" class="text-center" style="border: none; vertical-align: top;">
                    <p class="text-bold" style="margin-bottom: 5px;">Mengetahui</p>
                    @php
                        $showQrAdmin = $qrAdmin;
                        $passedAdmin = in_array($procurement->status, ['pending_management', 'pending_bendahara', 'pending_director', 'approved', 'completed']);
                        // Fallback: Jika data lama kosong tapi statusnya sudah lolos admin, buat QR dadakan
                        if(empty($showQrAdmin) && $passedAdmin) {
                            $showQrAdmin = base64_encode((string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(100)->generate("Disetujui oleh Admin IT\nPengajuan: " . $procurement->purpose));
                        }
                    @endphp
                    @if(!empty($showQrAdmin))
                        <img src="data:image/png;base64,{!! trim($showQrAdmin) !!}" style="width: 65px; height: 65px;" />
                    @else
                        <div style="height: 65px; line-height: 65px; color: #757575; font-style: italic; font-size: 8pt;">(Belum Validasi)</div>
                    @endif
                    <br><span style="font-size: 8pt;">Admin ipsrs</span>
                </td>

                {{-- MANAGEMENT --}}
                <td width="20%" class="text-center" style="border: none; vertical-align: top;">
                    <p class="text-bold" style="margin-bottom: 5px;">Validasi</p>
                    @php
                        $showQrManagement = $qrManagement;
                        $passedManagement = in_array($procurement->status, ['pending_bendahara', 'pending_director', 'approved', 'completed']);
                        if(empty($showQrManagement) && $passedManagement) {
                            $showQrManagement = base64_encode((string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(100)->generate("Disetujui oleh Management\nPengajuan: " . $procurement->purpose));
                        }
                    @endphp
                    @if(!empty($showQrManagement))
                        <img src="data:image/png;base64,{!! trim($showQrManagement) !!}" style="width: 65px; height: 65px;" />
                    @else
                        <div style="height: 65px; line-height: 65px; color: #757575; font-style: italic; font-size: 8pt;">-</div>
                    @endif
                    <br><span style="font-size: 8pt;">Management</span>
                </td>

                {{-- BENDAHARA --}}
                <td width="20%" class="text-center" style="border: none; vertical-align: top;">
                    <p class="text-bold" style="margin-bottom: 5px;">Verifikasi</p>
                    @php
                        $showQrBendahara = $qrBendahara;
                        $passedBendahara = in_array($procurement->status, ['pending_director', 'approved', 'completed']);
                        if(empty($showQrBendahara) && $passedBendahara) {
                            $showQrBendahara = base64_encode((string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(100)->generate("Disetujui oleh Bendahara\nPengajuan: " . $procurement->purpose));
                        }
                    @endphp
                    @if(!empty($showQrBendahara))
                        <img src="data:image/png;base64,{!! trim($showQrBendahara) !!}" style="width: 65px; height: 65px;" />
                    @else
                        <div style="height: 65px; line-height: 65px; color: #757575; font-style: italic; font-size: 8pt;">-</div>
                    @endif
                    <br><span style="font-size: 8pt;">Bendahara</span>
                </td>

                {{-- DIREKTUR --}}
                <td width="20%" class="text-center" style="border: none; vertical-align: top;">
                    <p class="text-bold" style="margin-bottom: 5px;">Menyetujui</p>
                    @php
                        $showQrDirektur = $qrDirektur;
                        $passedDirektur = in_array($procurement->status, ['approved', 'completed']);
                        if(empty($showQrDirektur) && $passedDirektur) {
                            $showQrDirektur = base64_encode((string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(100)->generate("Disetujui oleh Direktur\nPengajuan: " . $procurement->purpose));
                        }
                    @endphp
                    @if(!empty($showQrDirektur))
                        <img src="data:image/png;base64,{!! trim($showQrDirektur) !!}" style="width: 65px; height: 65px;" />
                    @else
                        <div style="height: 65px; line-height: 65px; color: #757575; font-style: italic; font-size: 8pt;">-</div>
                    @endif
                    <br><span style="font-size: 8pt;">Direktur Utama</span>
                </td>

            </tr>
        </table>
    </div>

</body>
</html>