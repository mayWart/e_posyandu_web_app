<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Posyandu Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;
            background-color: #F8FAFC;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(6px);
        }

        .brand-gradient {
            background: linear-gradient(90deg, #2563EB 0%, #06B6D4 100%);
        }

        @keyframes float-up {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-6px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .float-up {
            animation: float-up 4s ease-in-out infinite;
        }

        @keyframes fade-up {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-up {
            animation: fade-up .6s ease forwards;
        }
    </style>
    <script>
        function toggleMobileMenu() {
            const el = document.getElementById('mobileMenu');
            el.classList.toggle('hidden');
        }
        function toggleProfileMenu() {
            const el = document.getElementById('profileMenu');
            el.classList.toggle('hidden');
        }
        // close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            const pm = document.getElementById('profileMenu');
            const btn = document.getElementById('profileBtn');
            if (pm && btn && !btn.contains(e.target) && !pm.contains(e.target)) pm.classList.add('hidden');
        });
    </script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Helper to show toast/modal and optional redirect
        function showAlert(icon, title, text, redirect) {
            Swal.fire({
                icon: icon || 'info',
                title: title || '',
                text: text || '',
                confirmButtonColor: '#10b981'
            }).then(function () {
                if (redirect) window.location = redirect;
            });
        }

        // Confirmation dialog, then navigate to href when confirmed
        function confirmDelete(text, href) {
            Swal.fire({
                title: text || 'Yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = href;
                }
            });
        }
    </script>
</head>

<body class="text-gray-800">

    <header class="sticky top-0 z-50 bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <a href="/" class="flex items-center gap-3 no-underline">
                        <div
                            class="w-10 h-10 rounded-lg brand-gradient flex items-center justify-center text-white font-extrabold">
                            EP</div>
                        <div class="hidden sm:block">
                            <div class="text-lg font-semibold text-slate-800">E-Posyandu</div>
                            <div class="text-xs text-slate-500">Layanan Posyandu Digital</div>
                        </div>
                    </a>
                </div>

                <nav class="hidden md:flex items-center space-x-4" aria-label="Primary">
                    <a href="../pages/dashboard.php" class="text-sm text-slate-700 hover:text-slate-900">Dashboard</a>
                    <a href="../pages/edit_detail_balita.php"
                        class="text-sm text-slate-700 hover:text-slate-900">Penimbangan</a>
                    <a href="../pages/register.php" class="text-sm text-slate-700 hover:text-slate-900">Pendaftaran</a>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="../pages/data_ibu_hamil.php" class="text-sm text-slate-700 hover:text-slate-900">Data Ibu
                            Hamil</a>
                    <?php endif; ?>
                </nav>

                <div class="flex items-center gap-3">
                    <!-- Notification -->
                    <?php $badge = isset($pendingCount) ? intval($pendingCount) : 0; ?>
                    <button class="relative p-2 rounded-md hover:bg-gray-100" aria-label="Notifikasi">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <?php if ($badge > 0): ?>
                            <span
                                class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-semibold leading-none text-white bg-red-600 rounded-full"><?= $badge; ?></span>
                        <?php endif; ?>
                    </button>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="relative">
                            <button id="profileBtn" onclick="toggleProfileMenu()" aria-haspopup="true" aria-expanded="false"
                                class="flex items-center gap-2 bg-white border border-gray-200 rounded-full px-3 py-1 text-sm shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <span
                                    class="w-8 h-8 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center font-semibold"><?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)); ?></span>
                                <span
                                    class="hidden sm:block text-sm text-slate-700"><?= htmlspecialchars($_SESSION['nama']); ?></span>
                            </button>

                            <div id="profileMenu"
                                class="hidden origin-top-right absolute right-0 mt-3 w-56 rounded-lg bg-white shadow-lg ring-1 ring-black/5"
                                role="menu" aria-label="Profile menu">
                                <div class="py-2">
                                    <a href="../pages/dashboard.php"
                                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-50"
                                        role="menuitem">Dashboard</a>
                                    <a href="../pages/data_anak.php"
                                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-gray-50"
                                        role="menuitem">Profil Anak</a>
                                    <div class="border-t my-1"></div>
                                    <a href="../services/logout.php"
                                        class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50"
                                        role="menuitem">Keluar</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="../pages/login.php" class="text-sm font-medium text-blue-600 hover:text-blue-800">Masuk</a>
                        <a href="../pages/register.php"
                            class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Daftar</a>
                    <?php endif; ?>

                    <button class="md:hidden inline-flex items-center p-2 rounded-md text-gray-600 hover:bg-gray-100"
                        onclick="toggleMobileMenu()" aria-label="Open menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobileMenu" class="md:hidden hidden bg-white border-t">
            <div class="px-4 pt-3 pb-4 space-y-2">
                <a href="../pages/dashboard.php"
                    class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-gray-50">Dashboard</a>
                <a href="../pages/edit_detail_balita.php"
                    class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-gray-50">Penimbangan</a>
                <a href="../pages/register.php"
                    class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-gray-50">Pendaftaran</a>

                <?php if (isset($_SESSION['level']) && $_SESSION['level'] == 'admin'): ?>
                    <li>
                        <a href="data_ibu_hamil.php"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-venus-double"></i> <span class="ms-3">Data Ibu Hamil</span>
                        </a>
                    </li>
                <?php endif; ?>
            </div>
        </div>

    </header>

    <main class="py-10">