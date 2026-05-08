<!DOCTYPE html>
<html>
<head>
    <title>Laporan Mingguan Maintenance</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; font-size: 11px; }
        th { background-color: #f2f2f2; text-align: center; }
        h2 { text-align: center; margin-bottom: 5px; }
        p.date { text-align: center; margin-top: 0; font-size: 14px; color: #555; }
        .bg-date { background-color: #f9fafb; font-weight: bold; }
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

    <h2>Laporan Mingguan Pemeliharaan</h2>
    <p class="date">Periode: {{ $dateLabel ?? ($weekLabel ?? '') }}</p>

    <table>
        <thead>
            <tr>
                <th style="width: 9%;">No Tiket</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 12%;">Pelapor</th>
                <th style="width: 12%;">Ruangan</th>
                <th style="width: 13%;">Keluhan</th>
                <th style="width: 9%;">Status</th>
                <th style="width: 12%;">Tindakan Teknisi</th>
                <th style="width: 13%;">Ditangani Oleh</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Grouping berdasarkan tanggal tetap dipertahankan agar rapi untuk laporan mingguan
                $groupedReports = $reports->groupBy(function($item) {
                    return \Carbon\Carbon::parse($item->created_at)->format('Y-m-d');
                });
            @endphp

            @forelse($groupedReports as $date => $items)
                <tr class="bg-date">
                    <td colspan="8" style="background-color: #f9fafb;">
                         {{ \Carbon\Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </td>
                </tr>
                @foreach($items as $report)
                <tr>
                    <td>{{ $report->ticket_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($report->created_at)->format('d/m/Y') }}<br><small>{{ \Carbon\Carbon::parse($report->created_at)->format('H:i') }}</small></td>
                    <td>{{ $report->pelapor_nama ?? 'Anonim' }}</td>
                    <td>{{ $report->room ? $report->room->name : $report->ruangan }}</td>
                    <td style="font-size: 10px;">{{ $report->keluhan }}</td>
                    <td style="text-align: center;">
                        <span style="padding: 2px 5px; border-radius: 3px; font-size: 9px; font-weight: bold; background-color: {{ $report->status == 'Selesai' ? '#d4edda' : ($report->status == 'Diproses' ? '#fff3cd' : '#f8d7da') }};">
                            {{ $report->status }}
                        </span>
                    </td>
                    <td style="font-size: 9px;">{{ $report->tindakan_teknisi ?? '-' }}</td>
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
                @endforeach
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; font-style: italic; color: #777;">
                        Tidak ada laporan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top: 20px; font-size: 12px;">Demikian laporan ini disusun untuk memberikan gambaran mengenai Pemeliharaan IPSRS periode ini. Atas perhatiannya, kami ucapkan terima kasih.</p>

    <div style="float: right; width: 200px; text-align: center; margin-top: 30px;">
        <p style="margin-bottom: 5px; font-size: 12px;">Mengetahui/Validasi,</p>
        
        @if(isset($qrCode))
            <img src="data:image/png;base64, {!! $qrCode !!}" alt="QR Validasi" style="width: 80px; height: 80px; margin: 5px 0;">
        @else
            <br><br><br>
        @endif
        
        <br>
        <span style="font-size: 12px; font-weight: bold; text-decoration: underline;">
            {{ $validator ?? Auth::user()->name }}
        </span>
        <br>
        <span style="font-size: 10px; color: #555;">Digital Signature</span>
    </div>
</body>
</html>