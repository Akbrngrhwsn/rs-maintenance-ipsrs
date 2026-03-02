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
                let lastCounts = JSON.parse(sessionStorage.getItem('notifCounts')) || {};

                // Fungsi untuk update badges di navbar
                function updateNavbarBadges(role, counts) {
                    if (role === 'admin') {
                        const reportBadge = document.getElementById('badge-admin-reports');
                        const appBadge = document.getElementById('badge-admin-apps');
                        
                        if (counts.reports > 0) {
                            reportBadge.textContent = counts.reports;
                            reportBadge.classList.remove('hidden');
                        } else {
                            reportBadge.classList.add('hidden');
                        }

                        if (counts.apps > 0) {
                            appBadge.textContent = counts.apps;
                            appBadge.classList.remove('hidden');
                        } else {
                            appBadge.classList.add('hidden');
                        }
                    } 
                    else if (role === 'direktur') {
                        const appBadge = document.getElementById('badge-director-apps');
                        const procBadge = document.getElementById('badge-director-procurements');
                        
                        if (counts.pending_apps > 0) {
                            appBadge.textContent = counts.pending_apps;
                            appBadge.classList.remove('hidden');
                        } else {
                            appBadge.classList.add('hidden');
                        }

                        if (counts.pending_procurements > 0) {
                            procBadge.textContent = counts.pending_procurements;
                            procBadge.classList.remove('hidden');
                        } else {
                            procBadge.classList.add('hidden');
                        }
                    } 
                    else if (role === 'management') {
                        const appBadge = document.getElementById('badge-management-apps');
                        const procBadge = document.getElementById('badge-management-procurements');
                        
                        if (counts.submitted_apps > 0) {
                            appBadge.textContent = counts.submitted_apps;
                            appBadge.classList.remove('hidden');
                        } else {
                            appBadge.classList.add('hidden');
                        }

                        if (counts.submitted_procurements > 0) {
                            procBadge.textContent = counts.submitted_procurements;
                            procBadge.classList.remove('hidden');
                        } else {
                            procBadge.classList.add('hidden');
                        }
                    } 
                    else if (role === 'bendahara') {
                        const appBadge = document.getElementById('badge-bendahara-apps');
                        const procBadge = document.getElementById('badge-bendahara-procurements');
                        
                        if (counts.apps > 0) {
                            appBadge.textContent = counts.apps;
                            appBadge.classList.remove('hidden');
                        } else {
                            appBadge.classList.add('hidden');
                        }

                        if (counts.pending_procurements > 0) {
                            procBadge.textContent = counts.pending_procurements;
                            procBadge.classList.remove('hidden');
                        } else {
                            procBadge.classList.add('hidden');
                        }
                    } 
                    else if (role === 'kepala_ruang') {
                        const procBadge = document.getElementById('badge-kepala-ruang-procurements');
                        
                        if (counts.pending_procurements > 0) {
                            procBadge.textContent = counts.pending_procurements;
                            procBadge.classList.remove('hidden');
                        } else {
                            procBadge.classList.add('hidden');
                        }
                    }
                }

                function playNotification() {
                    sound.play().catch(e => console.log('Audio blocked:', e));
                }

                function checkNotifications() {
                    fetch('{{ route("notifications.check") }}')
                        .then(response => response.json())
                        .then(data => {
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
                                const lastReports = lastCounts.reports || 0;
                                const lastApps = lastCounts.apps || 0;

                                if (currentReports > lastReports) {
                                    title = '⚠️ Laporan Masuk!';
                                    message = 'Ada laporan kerusakan baru.';
                                    shouldNotify = true;
                                    // Admin hanya butuh reload jika sedang di Dashboard
                                    if(currentPath.includes('/admin/dashboard')) needReload = true;
                                }
                                else if (currentApps > lastApps) {
                                    title = '🔔 Request Aplikasi Baru';
                                    message = 'Ada request aplikasi baru masuk.';
                                    shouldNotify = true;
                                    if(currentPath.includes('/apps') || currentPath.includes('/admin/apps')) needReload = true;
                                }
                                
                                lastCounts.reports = currentReports;
                                lastCounts.apps = currentApps;
                            }

                            // === LOGIKA DIREKTUR ===
                            else if (data.role === 'direktur') {
                                const currentPendingApps = data.counts.pending_apps || 0;
                                const lastPendingApps = lastCounts.pending_apps || 0;
                                const currentPendingProc = data.counts.pending_procurements || 0;
                                const lastPendingProc = lastCounts.pending_procurements || 0;

                                if (currentPendingApps > lastPendingApps) {
                                    title = '📩 Permintaan Aplikasi ke Direktur';
                                    message = 'Ada aplikasi yang diteruskan ke Direktur untuk persetujuan.';
                                    shouldNotify = true;
                                    if(currentPath.includes('/director/reports') || currentPath.includes('/director/')) needReload = true;
                                }

                                if (currentPendingProc > lastPendingProc) {
                                    title = '📦 Pengadaan untuk Direktur';
                                    message = 'Ada pengajuan pengadaan yang butuh persetujuan Direktur.';
                                    shouldNotify = true;
                                    if(currentPath.includes('/director/procurements')) needReload = true;
                                }

                                lastCounts.pending_apps = currentPendingApps;
                                lastCounts.pending_procurements = currentPendingProc;
                            }

                            // === LOGIKA MANAGEMENT (BARU) ===
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
                                    if(currentPath.includes('/management/procurements')) needReload = true;
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
                                    // Auto reload jika di halaman valid
                                    if(currentPath.includes('/kepala-ruang/procurements')) needReload = true;
                                }

                                lastCounts.pending_procurements = currentPendingProc;
                            }

                            // === LOGIKA BENDAHARA ===
                            else if (data.role === 'bendahara') {
                                const currentPendingProc = data.counts.pending_procurements || 0;
                                const lastPendingProc = lastCounts.pending_procurements || 0;

                                if (currentPendingProc > lastPendingProc) {
                                    title = '💰 Validasi Keuangan';
                                    message = 'Kepala ruang menyetujui pengadaan. Menunggu cek anggaran.';
                                    shouldNotify = true;
                                    // Auto reload jika di halaman validasi bendahara
                                    if(currentPath.includes('/bendahara/procurements')) needReload = true;
                                }

                                lastCounts.pending_procurements = currentPendingProc;
                            }

                            // Simpan state terbaru agar tidak notif berulang
                            sessionStorage.setItem('notifCounts', JSON.stringify(lastCounts));

                            // === EKSEKUSI ===
                            if (shouldNotify) {
                                playNotification();

                                // Tampilkan Pesan
                                Swal.fire({
                                    title: title,
                                    // Tambahkan info jika akan reload
                                    text: needReload ? message + ' (Memuat ulang...)' : message,
                                    icon: 'info',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 8000, // Durasi notif
                                    timerProgressBar: true
                                });

                                // JALANKAN AUTO RELOAD (Hanya jika di halaman relevan)
                                if (needReload) {
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000); // Tunggu 3 detik baru reload
                                }
                            }
                        })
                        .catch(err => console.error('Gagal cek notifikasi:', err));
                }

                // Inisialisasi data awal (Silent Sync)
                fetch('{{ route("notifications.check") }}')
                    .then(r => r.json())
                    .then(d => {
                        // Update badges pada page load
                        updateNavbarBadges(d.role, d.counts);
                        
                        if(!sessionStorage.getItem('notifCounts')) {
                            if(d.role === 'admin') {
                                lastCounts = { reports: d.counts.reports, apps: d.counts.apps };
                            } else if(d.role === 'direktur') {
                                lastCounts = { pending_apps: d.counts.pending_apps, pending_procurements: d.counts.pending_procurements };
                            } else if(d.role === 'management') {
                                lastCounts = { submitted_apps: d.counts.submitted_apps, submitted_procurements: d.counts.submitted_procurements };
                            } else if(d.role === 'bendahara') {
                                lastCounts = { apps: d.counts.apps, pending_procurements: d.counts.pending_procurements };
                            } else if(d.role === 'kepala_ruang') {
                                lastCounts = { pending_procurements: d.counts.pending_procurements };
                            }
                            sessionStorage.setItem('notifCounts', JSON.stringify(lastCounts));
                        }
                    });

                setInterval(checkNotifications, checkInterval);
            });
        </script>
    </body>
</html>