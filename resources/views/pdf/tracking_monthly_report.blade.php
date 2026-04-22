<!DOCTYPE html>
<html>
<head>
    <title>Laporan Riwayat Kerusakan</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; font-size: 11px; }
        th { background-color: #f2f2f2; text-align: center; }
        h2 { text-align: center; margin-bottom: 5px; }
        p.date { text-align: center; margin-top: 0; font-size: 14px; color: #555; }
        .status-selesai { background-color: #d4edda; color: #155724; }
        .status-ditolak { background-color: #f8d7da; color: #721c24; }
        .status-pengadaan { background-color: #d1ecf1; color: #0c5460; }
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
            <p style="margin:0;">Laporan Maintenance & Perbaikan Fasilitas</p>
            <hr style="border: 1px solid black; margin-top: 10px;">
        @endif
    </div>

    <h2>Laporan Riwayat Kerusakan (Selesai)</h2>
    <p class="date">Periode: {{ $dateString }}</p>
    <p class="date">Diunduh oleh: <strong>{{ $validator }}</strong> ({{ ucfirst($role) }})</p>

    <table>
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 10%;">No Tiket</th>
                <th style="width: 10%;">Tanggal Lapor</th>
                <th style="width: 10%;">Tanggal Selesai</th>
                <th style="width: 12%;">Ruangan</th>
                <th style="width: 15%;">Keluhan</th>
                <th style="width: 10%;">Tindakan</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 19%;">Ditangani Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $report)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="font-family: monospace; font-size: 10px;">{{ $report->ticket_number }}</td>
                <td>{{ \Carbon\Carbon::parse($report->created_at)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($report->updated_at)->format('d/m/Y') }}</td>
                <td>{{ $report->room ? $report->room->name : $report->ruangan }}</td>
                <td style="font-size: 10px;">{{ Str::limit($report->keluhan, 35) }}</td>
                <td style="font-size: 9px;">{{ Str::limit($report->tindakan_teknisi ?? '-', 25) }}</td>
                <td style="text-align: center;">
                    @if($report->status == 'Selesai')
                        <span class="status-selesai" style="padding: 2px 5px; border-radius: 3px; font-size: 9px; font-weight: bold;">✓ Selesai</span>
                    @elseif($report->status == 'Ditolak')
                        <span class="status-ditolak" style="padding: 2px 5px; border-radius: 3px; font-size: 9px; font-weight: bold;">✗ Ditolak</span>
                    @elseif($report->status == 'Tidak Selesai')
                        <span class="status-pengadaan" style="padding: 2px 5px; border-radius: 3px; font-size: 9px; font-weight: bold;">📦 Pengadaan</span>
                    @else
                        <span style="padding: 2px 5px; border-radius: 3px; font-size: 9px; font-weight: bold; background-color: #e2e3e5; color: #383d41;">{{ $report->status }}</span>
                    @endif
                </td>
                <td style="font-size: 8px;">
                    @php
                        $handlers = [];
                        if($report->handled_by_admin) $handlers[] = 'Admin: ' . $report->handled_by_admin;
                        if($report->handled_by_karu) $handlers[] = 'Karu: ' . $report->handled_by_karu;
                        if($report->handled_by_management) $handlers[] = 'Mgmt: ' . $report->handled_by_management;
                        if($report->handled_by_bendahara) $handlers[] = 'Ben: ' . $report->handled_by_bendahara;
                        if($report->handled_by_director) $handlers[] = 'Dir: ' . $report->handled_by_director;
                    @endphp
                    {{ implode(' | ', $handlers) ?: '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; font-style: italic; color: #777;">
                    Tidak ada riwayat kerusakan pada periode ini.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="8" style="text-align: right;">Total Laporan Selesai:</td>
                <td style="text-align: center;">{{ count($reports) }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="margin-top: 20px; font-size: 12px;">
        Laporan riwayat kerusakan ini menunjukkan semua perbaikan yang telah diselesaikan pada periode {{ $dateString }}. 
        Untuk informasi lebih lanjut, silakan hubungi Tim Maintenance.
    </p>

    <div style="float: right; width: 200px; text-align: center; margin-top: 30px;">
        <p style="margin-bottom: 5px; font-size: 12px;">Diunduh oleh,</p>
        
        @if(isset($qrCode))
            <img src="data:image/png;base64, {!! $qrCode !!}" alt="QR Validasi" style="width: 80px; height: 80px; margin: 5px 0;">
        @else
            <br><br><br>
        @endif
        
        <br>
        <span style="font-size: 12px; font-weight: bold; text-decoration: underline;">
            {{ $validator }}
        </span>
        <br>
        <span style="font-size: 10px; color: #555;">{{ now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }} WIB</span>
    </div>
</body>
</html>
