<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <linkpreconnect href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>
        </div>

        <audio id="notifSound" src="{{ asset('notification.mp3') }}" preload="auto"></audio>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
    const checkInterval = 10000; // Cek setiap 10 detik
    const sound = document.getElementById('notifSound');
    let lastCounts = {};
    let isFirstCheck = true;

    // Fungsi untuk update badges di navbar
    function updateNavbarBadges(role, counts) {
        // Fungsi pembantu untuk update elemen desktop & mobile sekaligus
        const updateElement = (id, count) => {
            const ids = [id, id + '-mobile'];
            ids.forEach(targetId => {
                const badge = document.getElementById(targetId);
                if (badge) {
                    if (count > 0) {
                        badge.textContent = count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            });
        };

        if (role === 'admin') {
            updateElement('badge-admin-reports', counts.reports);
            updateElement('badge-admin-apps', counts.apps);
            updateElement('badge-admin-request-apps', counts.request_apps);
            updateElement('badge-admin-procurements', counts.procurements);
        } 
        else if (role === 'direktur') {
            updateElement('badge-director-request-apps', counts.request_apps);
            updateElement('badge-director-apps', counts.pending_apps);
            updateElement('badge-director-procurements', counts.pending_procurements);
            updateElement('badge-director-app-procurements', counts.app_procurements);
        } 
        else if (role === 'management') {
            updateElement('badge-management-request-apps', counts.submitted_apps);
            updateElement('badge-management-apps', counts.submitted_apps);
            updateElement('badge-management-procurements', counts.submitted_procurements);
        } 
        else if (role === 'bendahara') {
            
            updateElement('badge-bendahara-apps', counts.apps);
            updateElement('badge-bendahara-procurements', counts.pending_procurements);
        } 
        else if (role === 'kepala_ruang') {
            updateElement('badge-kepala-ruang-procurements', counts.pending_procurements);
        }
    }

    function playNotification() {
        sound.play().catch(e => console.log('Audio blocked:', e));
    }

    function checkNotifications() {
        fetch('{{ route("notifications.check") }}')
            .then(response => response.json())
            .then(data => {
                // ... Bypass jika error / guest
                if (!data || !data.role) return;

                let shouldNotify = false;
                let message = '';
                let title = '';
                
                // Deteksi URL saat ini untuk Auto-Reload
                const currentPath = window.location.pathname;
                let needReload = false;

                // Update badges navbar untuk semua role
                updateNavbarBadges(data.role, data.counts);

                // === LOGIKA ADMIN ===
if (data.role === 'admin') {
    const currentReports = data.counts.reports || 0;
    const currentApps = data.counts.apps || 0;
    const currentProcurements = data.counts.procurements || 0;
    const currentApprovedApps = data.counts.approved_apps || 0; // Data baru
    const currentApprovedAppProc = data.counts.approved_app_procurements || 0; // Data baru
    
    const lastReports = lastCounts.reports || 0;
    const lastApps = lastCounts.apps || 0;
    const lastProcurements = lastCounts.procurements || 0;
    const lastApprovedApps = lastCounts.approved_apps || 0; // Data lama
    const lastApprovedAppProc = lastCounts.approved_app_procurements || 0; // Data lama

    if (currentProcurements > lastProcurements) {
        title = '✅ Pengadaan Disetujui!';
        message = 'Direktur telah menyetujui pengadaan. Silakan dieksekusi.';
        shouldNotify = true;
        if(currentPath.includes('/admin/procurements')) needReload = true;
    }
    // NOTIFIKASI BARU: Aplikasi Disetujui
    else if (currentApprovedApps > lastApprovedApps) {
        title = '🚀 Aplikasi Disetujui!';
        message = 'Direktur telah menyetujui permintaan aplikasi. Silakan mulai pengerjaan.';
        shouldNotify = true;
        if(currentPath.includes('/apps/ongoing')) needReload = true;
    }
    // NOTIFIKASI BARU: Pengadaan Aplikasi Disetujui
    else if (currentApprovedAppProc > lastApprovedAppProc) {
        title = '💰 Budget Aplikasi Disetujui!';
        message = 'Anggaran untuk pengadaan aplikasi telah di-ACC oleh Direktur.';
        shouldNotify = true;
        if(currentPath.includes('/apps/show')) needReload = true;
    }
    else if (currentReports > lastReports) {
        title = '⚠️ Laporan Masuk!';
        message = 'Ada laporan kerusakan baru.';
        shouldNotify = true;
        if(currentPath.includes('/admin/dashboard') || currentPath === '/' || currentPath === '/dashboard') needReload = true;
    }
    else if (currentApps > lastApps) {
        title = '🔔 Request Aplikasi Baru';
        message = 'Ada request aplikasi baru masuk.';
        shouldNotify = true;
        if(currentPath.includes('/apps') || currentPath.includes('/admin/apps')) needReload = true;
    }
    
    // Simpan history agar tidak spam
    lastCounts.reports = currentReports;
    lastCounts.apps = currentApps;
    lastCounts.procurements = currentProcurements;
    lastCounts.approved_apps = currentApprovedApps; // Simpan state baru
    lastCounts.approved_app_procurements = currentApprovedAppProc; // Simpan state baru
}

                // === LOGIKA DIREKTUR ===
                            else if (data.role === 'direktur') {
                                const currentPendingApps = data.counts.pending_apps || 0;
                                const lastPendingApps = lastCounts.pending_apps || 0;
                                const currentPendingProc = data.counts.pending_procurements || 0;
                                const lastPendingProc = lastCounts.pending_procurements || 0;
                                // TAMBAHKAN INI: Deteksi perubahan jumlah pengadaan aplikasi
                                const currentAppProc = data.counts.app_procurements || 0;
                                const lastAppProc = lastCounts.app_procurements || 0;

                                if (currentPendingApps > lastPendingApps) {
                                    title = '📩 Permintaan Aplikasi';
                                    message = 'Ada aplikasi menunggu persetujuan Direktur.';
                                    shouldNotify = true;
                                    if(currentPath.includes('/director/reports')) needReload = true;
                                } else if (currentPendingProc > lastPendingProc) {
                                    title = '📦 Pengadaan Barang';
                                    message = 'Ada pengajuan pengadaan barang butuh persetujuan.';
                                    shouldNotify = true;
                                    if(currentPath.includes('/director/procurements')) needReload = true;
                                } else if (currentAppProc > lastAppProc) {
                                    // NOTIFIKASI BARU: Validasi Pengadaan Aplikasi
                                    title = '💻 Pengadaan Aplikasi';
                                    message = 'Ada pengadaan aplikasi yang butuh validasi akhir.';
                                    shouldNotify = true;
                                    if(currentPath.includes('/apps')) needReload = true;
                                }

                                Object.assign(lastCounts, data.counts);
                            }

                // === LOGIKA MANAGEMENT ===
                else if (data.role === 'management') {
                    const currentSubmittedApps = data.counts.submitted_apps || 0;
                    const lastSubmittedApps = lastCounts.submitted_apps || 0;
                    const currentSubmittedProc = data.counts.submitted_procurements || 0;
                    const lastSubmittedProc = lastCounts.submitted_procurements || 0;

                    if (currentSubmittedApps > lastSubmittedApps) {
                        title = '📨 Aplikasi Masuk ke Management';
                        message = 'Admin IT meneruskan request aplikasi ke Management.';
                        shouldNotify = true;
                        if(currentPath.includes('/management/reports') || currentPath.includes('/management')) needReload = true;
                    }

                    if (currentSubmittedProc > lastSubmittedProc) {
                        title = '📥 Pengadaan untuk Management';
                        message = 'Ada pengajuan pengadaan yang masuk ke Management.';
                        shouldNotify = true;
                        if(currentPath.includes('/management/procurements') || currentPath.includes('/apps')) needReload = true;
                    }

                    lastCounts.submitted_apps = currentSubmittedApps;
                    lastCounts.submitted_procurements = currentSubmittedProc;
                }

                // === LOGIKA KEPALA RUANG ===
                else if (data.role === 'kepala_ruang') {
                    const currentPendingProc = data.counts.pending_procurements || 0;
                    const lastPendingProc = lastCounts.pending_procurements || 0;

                    if (currentPendingProc > lastPendingProc) {
                        title = '📋 Validasi Pengadaan';
                        message = 'Admin IT mengajukan pengadaan baru.';
                        shouldNotify = true;
                        if(currentPath.includes('/kepala-ruang/procurements')) needReload = true;
                    }

                    lastCounts.pending_procurements = currentPendingProc;
                }

                // === LOGIKA BENDAHARA ===
                else if (data.role === 'bendahara') {
                    const currentPendingProc = data.counts.pending_procurements || 0;
                    const lastPendingProc = lastCounts.pending_procurements || 0;
                    const currentApps = data.counts.apps || 0;
                    const lastApps = lastCounts.apps || 0;
                    const currentRequestApps = data.counts.request_apps || 0;
                    const lastRequestApps = lastCounts.request_apps || 0;

                    if (currentPendingProc > lastPendingProc) {
                        title = '💰 Validasi Keuangan';
                        message = 'Pengadaan masuk untuk validasi anggaran.';
                        shouldNotify = true;
                        if(currentPath.includes('/bendahara/procurements') || currentPath.includes('/apps')) needReload = true;
                    } else if (currentRequestApps > lastRequestApps) {
                        title = '📥 Request Aplikasi Baru';
                        message = 'Management mengirimkan request aplikasi baru.';
                        shouldNotify = true;
                        if(currentPath.includes('/bendahara')) needReload = true;
                    } else if (currentApps > lastApps) {
                        title = '📋 Aplikasi Butuh Validasi';
                        message = 'Ada aplikasi yang membutuhkan validasi anggaran.';
                        shouldNotify = true;
                        if(currentPath.includes('/bendahara')) needReload = true;
                    }

                    lastCounts.pending_procurements = currentPendingProc;
                    lastCounts.apps = currentApps;
                    lastCounts.request_apps = currentRequestApps;
                }

                // Simpan state terbaru ke sessionStorage
                sessionStorage.setItem('notifCounts', JSON.stringify(lastCounts));

                // === EKSEKUSI TAMPILAN NOTIFIKASI POP-UP ===
                if (shouldNotify && !isFirstCheck) {
                    playNotification();

                    Swal.fire({
                        title: title,
                        text: needReload ? message + ' (Memuat ulang...)' : message,
                        icon: 'info',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 8000, 
                        timerProgressBar: true
                    });

                    // Auto reload jika user berada di halaman yang butuh di-refresh
                    if (needReload) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000); 
                    }
                }

                isFirstCheck = false;
            })
            .catch(err => console.error('Gagal cek notifikasi:', err));
    }

    // Inisialisasi data awal pada saat halaman pertama dimuat
    fetch('{{ route("notifications.check") }}')
        .then(r => r.json())
        .then(d => {
            if(!d || !d.role) return;

            updateNavbarBadges(d.role, d.counts);
            
            // Atur nilai default awal berdasarkan role
            if(d.role === 'admin') {
                lastCounts = { reports: d.counts.reports || 0, apps: d.counts.apps || 0, procurements: d.counts.procurements || 0, approved_apps: d.counts.approved_apps || 0, approved_app_procurements: d.counts.approved_app_procurements || 0};
            } else if(d.role === 'direktur') {
                lastCounts = { pending_apps: d.counts.pending_apps || 0, pending_procurements: d.counts.pending_procurements || 0 };
            } else if(d.role === 'management') {
                lastCounts = { submitted_apps: d.counts.submitted_apps || 0, submitted_procurements: d.counts.submitted_procurements || 0 };
            } else if(d.role === 'bendahara') {
                lastCounts = { apps: d.counts.apps || 0, pending_procurements: d.counts.pending_procurements || 0, request_apps: d.counts.request_apps || 0 };
            } else if(d.role === 'kepala_ruang') {
                lastCounts = { pending_procurements: d.counts.pending_procurements || 0 };
            }
            sessionStorage.setItem('notifCounts', JSON.stringify(lastCounts));
            
            setTimeout(checkNotifications, 500);
        });

    setInterval(checkNotifications, checkInterval);
});
        </script>
    </body>
</html>