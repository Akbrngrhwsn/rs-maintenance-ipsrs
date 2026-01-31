<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Mingguan Maintenance</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
        th { background: #f3f4f6; text-align: center; font-size: 10px; text-transform: uppercase; }
        
        .bg-date { background-color: #f9fafb; font-weight: bold; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .italic { font-style: italic; }
        
        .item-table { width: 100%; border: none; }
        .item-table td { border: none; padding: 1px 0; font-size: 9px; color: #555; }

        .footer-signature {
            float: right; 
            width: 220px; 
            text-align: center; 
            margin-top: 30px;
            page-break-inside: avoid;
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
                    <div style="font-size:12px;">Laporan Maintenance & Perbaikan Fasilitas IT</div>
                </div>
                <hr />
            @endif
        @endif
    </div>

    <h3 style="text-align: center; margin-bottom: 5px;">LAPORAN MINGGUAN MAINTENANCE</h3>
    {{-- Menggunakan $dateLabel jika ada, fallback ke $weekLabel --}}
    <div style="text-align: center;">Periode: {{ $dateLabel ?? ($weekLabel ?? '') }}</div>
    <br>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Waktu</th>
                <th style="width: 18%;">Ruangan / Tiket</th>
                <th style="width: 20%;">Masalah</th>
                <th style="width: 20%;">Tindakan</th>
                <th style="width: 22%;">Detail (Item)</th>
                <th style="width: 12%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedHistory = $reports->groupBy(function($item) {
                    return $item->created_at->format('Y-m-d');
                });
            @endphp

            @forelse($groupedHistory as $date => $items)
                <tr class="bg-date">
                    <td colspan="6">
                        📅 {{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        <span style="font-weight: normal; font-size: 9px;">({{ $items->count() }} Laporan)</span>
                    </td>
                </tr>

                @foreach($items as $report)
                <tr>
                    <td class="text-center">{{ $report->created_at->format('H:i') }}</td>
                    <td>
                        <div class="font-bold">{{ $report->ruangan }}</div>
                        @if($report->ticket_number)
                            <div style="font-family: monospace; font-size: 9px; color: #666;">{{ $report->ticket_number }}</div>
                        @endif
                    </td>
                    <td>{{ $report->keluhan }}</td>
                    <td class="italic">{{ $report->tindakan_teknisi ?? '-' }}</td>
                    <td>
                        @if($report->procurement && !empty($report->procurement->items))
                            <table class="item-table">
                                @foreach($report->procurement->items as $it)
                                    <tr><td>• {{ $it['nama'] ?? '-' }} ({{ $it['jumlah'] ?? 1 }}x)</td></tr>
                                @endforeach
                            </table>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center font-bold">{{ $report->status }}</td>
                </tr>
                @endforeach
            @empty
                <tr><td colspan="6" class="text-center italic" style="color: #777; padding: 20px;">Data tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 15px; margin-bottom: 6px">
        Demikian laporan ini disusun untuk memberikan gambaran mengenai pemeliharaan sistem IT periode ini.
        Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
    </p>

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