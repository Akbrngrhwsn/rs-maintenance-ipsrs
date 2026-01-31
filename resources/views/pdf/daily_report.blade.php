<!DOCTYPE html>
<html>
<head>
    <title>Laporan Harian</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; font-size: 12px; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; margin-bottom: 5px; }
        p.date { text-align: center; margin-top: 0; font-size: 14px; color: #555; }
    </style>
</head>
<body>
    <div style="text-align:center; margin-bottom:20px;">
        @php
            // Pastikan path sesuai dengan file yang ada di server
            $kopPath = public_path('images/KOPSurat.jfif');
        @endphp
        
        @if(file_exists($kopPath))
            <img src="{{ $kopPath }}" alt="Kop Surat" style="width:100%; max-height:120px; object-fit:contain;" />
        @else
            {{-- Fallback jika gambar tidak ditemukan --}}
            <h1 style="margin:0;">{{ config('app.name') }}</h1>
            <p style="margin:0;">Laporan Maintenance & Perbaikan Fasilitas</p>
            <hr style="border: 1px solid black; margin-top: 10px;">
        @endif
    </div>

    <h2>Laporan Harian Pemeliharaan</h2>
    {{-- Tambahkan locale('id') agar tanggal pasti Bahasa Indonesia --}}
    <p class="date">Tanggal: {{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('d F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">No Tiket</th>
                <th style="width: 15%;">Pelapor</th>
                <th style="width: 15%;">Ruangan</th>
                <th>Keluhan</th>
                <th style="width: 12%;">Status</th>
                <th>Tindakan Teknisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr>
                <td>{{ $report->ticket_number }}</td>
                <td>{{ $report->pelapor_nama ?? 'Anonim' }}</td>
                <td>{{ $report->ruangan }}</td>
                <td>{{ $report->keluhan }}</td>
                <td style="text-align: center;">
                    {{-- Opsional: Badge warna sederhana dengan CSS inline --}}
                    <span style="
                        padding: 2px 5px; border-radius: 3px; font-size: 10px; font-weight: bold;
                        background-color: {{ $report->status == 'Selesai' ? '#d4edda' : ($report->status == 'Diproses' ? '#fff3cd' : '#f8d7da') }};
                    ">
                        {{ $report->status }}
                    </span>
                </td>
                <td>{{ $report->tindakan_teknisi ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; font-style: italic; color: #777;">
                    Tidak ada laporan pada tanggal ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-bottom: 6px">Demikian laporan ini disusun untuk memberikan gambaran mengenai Pemeliharaan Sistem IT periode ini.
        Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>

    <div style="float: right; width: 200px; text-align: center; margin-top: 30px;">
        <p style="margin-bottom: 5px;">Mengetahui/Validasi,</p>
        
        {{-- Tampilkan QR Code (Variable $qrCode dikirim dari Controller) --}}
        @if(isset($qrCode))
            <img src="data:{{ $qrMime ?? 'image/png' }};base64, {!! $qrCode !!}" alt="QR Validasi" style="width: 80px; height: 80px; margin: 5px 0;">
        @else
            <br><br><br> {{-- Spasi jika QR gagal generate --}}
        @endif
        
        <br>
        
        {{-- Gunakan variable $validator dari controller agar SAMA PERSIS dengan isi QR Code --}}
        <span style="font-size: 12px; font-weight: bold; text-decoration: underline;">
            {{ $validator ?? Auth::user()->name }}
        </span>
        <br>
        <span style="font-size: 10px; color: #555;">
            Digital Signature
        </span>
    </div>
</body>
</html>