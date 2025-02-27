<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GL Admin Dashboard</title>
    <link rel="icon" href="<?=base_url('favicon.ico')?>" type="image/x-icon" />
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Theme Variables & Layout Styles -->
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --surface-color: #f8fafc;            
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: rgba(0,0,0,0.08);
            --border-radius: 12px;
            --nav-bg: rgba(255, 255, 255, 0.95);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
            --nav-height: 70px;  /* This already exists */
            --nav-offset: calc(var(--nav-height)); /* Add this new variable */
        }
    
        [data-bs-theme="dark"] {
            --bg-main: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: rgba(255,255,255,0.08);
            --surface-color: #0f172a;
            --nav-bg: rgba(15, 23, 42, 0.95);
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        .top-navbar {
            background: var(--nav-bg);
            backdrop-filter: saturate(200%) blur(16px);
            height: var(--nav-height);
            border-bottom: 1px solid var(--border-color);
            padding: 0.5rem 1.5rem;
            transition: all 0.3s ease;
        }

        .navbar-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        }

        .brand-logo {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
        padding: 0.5rem;
        border-radius: var(--border-radius);
        transition: all 0.3s ease;
        white-space: nowrap;
        }

        .brand-logo span {
            display: inline-block !important; /* Override previous mobile hiding */
        }

        .brand-logo:hover {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
        }

        .brand-logo i {
            font-size: 1.75rem;
            background: linear-gradient(135deg, var(--primary-color), #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-menu {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 0.25rem;
            border: 1px solid var(--border-color);
        }

        [data-bs-theme="dark"] .nav-menu {
            background: rgba(30, 41, 59, 0.5);
        }

        .nav-link {
            position: relative;
            padding: 0.75rem 1.25rem !important;
            color: var(--text-muted) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: calc(var(--border-radius) - 4px);
            margin: 0 0.25rem;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            background: rgba(79, 70, 229, 0.1);
        }

        .nav-link.active {
            color: #fff !important;
            background: var(--primary-color);
        }

        .nav-link i {
            transition: transform 0.3s ease;
        }

        .nav-link:hover i {
            transform: translateY(-2px);
        }

        .user-profile {
            background: rgba(255, 255, 255, 0.8);
            border-radius: var(--border-radius);
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        [data-bs-theme="dark"] .user-profile {
            background: rgba(30, 41, 59, 0.8);
        }

        .user-profile:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .user-profile img {
            border: 2px solid var(--primary-color);
            padding: 2px;
            transition: all 0.3s ease;
        }

        .user-profile:hover img {
            transform: rotate(360deg);
        }

        .notification-btn {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
        }

        [data-bs-theme="dark"] .notification-btn {
            background: rgba(30, 41, 59, 0.8);
        }

        .notification-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: var(--primary-color);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary-color);
            border: 2px solid var(--bg-card);
            min-width: 22px;
            height: 22px;
            border-radius: 11px;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .dropdown-menu {
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            border-radius: var(--border-radius);
            padding: 0.75rem;
            background: var(--bg-card);
            margin-top: 0.75rem;
        }

        .dropdown-item {
            border-radius: calc(var(--border-radius) - 4px);
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .dropdown-item i {
            font-size: 1.1rem;
            color: var(--text-muted);
            transition: all 0.3s ease;
        }

        .main-content {
            padding-top: var(--nav-offset);
            min-height: calc(50vh - var(--nav-height));
            position: relative;
            background: var(--bg-main);
        }

        .dropdown-item:hover {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
        }

        .dropdown-item:hover i {
            color: var(--primary-color);
            transform: translateX(4px);
        }

        .theme-switch {
            width: 64px;
            height: 34px;
            background-color: #e2e8f0;
            border-radius: 17px;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid var(--border-color);
        }
    
        .theme-switch.dark {
            background-color: #334155;
        }
    
        .theme-switch .switch-handle {
            width: 26px;
            height: 26px;
            background-color: white;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-md);
        }
    
        .theme-switch.dark .switch-handle {
            left: 32px;
            background-color: var(--primary-color);
        }
    
        .theme-switch .switch-handle i {
            font-size: 14px;
            color: #fbbf24;
            transition: all 0.3s ease;
        }
    
        .theme-switch.dark .switch-handle i {
            color: #fff;
            transform: rotate(360deg);
        }

        .navbar-toggler {
        border: 1px solid var(--border-color);
        padding: 0.5rem;
        border-radius: var(--border-radius);
        color: var(--text-main);
        display: none; /* Hide by default on desktop */
        }

        .navbar-toggler:focus {
        box-shadow: none;
        border-color: var(--primary-color);
        }

        .utility-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        @media (max-width: 991.98px) {
        .top-navbar {
            padding: 0.5rem 1rem;
        }

        .nav-menu {
            background: none;
            backdrop-filter: none;
            border: none;
            padding: 0;
        }

        .nav-link {
            margin: 0.5rem 0;
        }

        .navbar-collapse {
            background: var(--bg-card);
            margin: 1rem -1rem -0.5rem;
            padding: 1rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
        }

        .utility-nav {
            border-top: 1px solid var(--border-color);
            margin-top: 1rem;
            padding-top: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .utility-nav-item {
            display: flex;
            align-items: center;
            background: rgba(var(--primary-color-rgb), 0.1);
            padding: 0.5rem;
            border-radius: var(--border-radius);
            gap: 0.5rem;
        }

        .theme-switch {
            margin: 0;
        }

        .notification-btn {
            margin: 0;
        }

        .navbar-toggler {
            display: block; /* Show on mobile */
        }

        .navbar-container {
            gap: 1rem;
        }

        .user-profile {
            margin: 0;
            background: rgba(var(--primary-color-rgb), 0.1);
        }

        .user-profile span {
            display: inline-block !important; /* Keep username visible on mobile */
        }

            .navbar-nav.align-items-center {
                flex-direction: row;
                justify-content: center;
                gap: 1rem;
                padding-top: 1rem;
                margin-top: 1rem;
                border-top: 1px solid var(--border-color);
            }
        }

        @media (max-width: 575.98px) {
            .brand-logo span {
                display: none;
            }

            .user-profile span {
                display: none;
            }

            .top-navbar {
            padding: 0.5rem;
            }

            .brand-logo {
                font-size: 1.25rem;
                padding: 0.5rem;
            }

            .utility-nav {
                gap: 0.5rem;
            }
        }        
        [data-bs-theme="dark"] {
            --bg-main: #1a1f37;            /* Darker background */
            --bg-card: #1e293b;            /* Card background */
            --text-main: #ffffff;          /* Brighter text */
            --text-muted: #a0aec0;         /* Lighter muted text */
            --border-color: rgba(255,255,255,0.1);
            --surface-color: #1a1f37;
            --nav-bg: rgba(26, 31, 55, 0.95);
            /* Update border colors for dark mode */
            --border-dark: rgba(255, 255, 255, 0.8);
        }

        // Add these styles after the existing .dropdown-item:hover styles
        [data-bs-theme="dark"] .table {
            color: var(--text-main);
        }

        [data-bs-theme="dark"] .nilai,
        [data-bs-theme="dark"] .nilai2 {
            color: var(--text-main);
            border-right: 1px solid var(--border-dark);
        }

        [data-bs-theme="dark"] tr[style*="border"] {
            border-color: var(--border-dark) !important;
        }

        [data-bs-theme="dark"] [style*="border"] {
            border-color: var(--border-dark) !important;
        }

        [data-bs-theme="dark"] .bold {
            color: var(--text-main);
        }

        [data-bs-theme="dark"] .table-header {
            background-color: var(--bg-card);
            border-color: var(--border-color);
        }

        [data-bs-theme="dark"] .table-header td {
            border-top: 1px solid var(--border-dark);
            border-bottom: 1px solid var(--border-dark);
        }

        [data-bs-theme="dark"] td {
            border-color: var(--border-color);
        }

        [data-bs-theme="dark"] .page {
            background-color: var(--bg-card);
        }
        
        @media print
        {    
            .no-print, .no-print *
            {
                display: none !important;
            }
        }

        html, body {
            height: 100%;
        }

        .main-content {
            flex: 1;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .footer {
            background: var(--bg-card);
            color: var(--text-muted);
            padding: 1rem;
            text-align: center;
            border-top: 1px solid var(--border-color);
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>
<body>
<div class="wrapper">
    <!-- Replace the existing nav opening -->
    <nav class="navbar navbar-expand-lg top-navbar fixed-top">
        <div class="container-fluid">
            <a class="brand-logo" href="<?= base_url() ?>">
                <i class="fas fa-analytics"></i>
                <span>GL Admin</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto nav-menu">                    
                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(current_url(), base_url('cms/dashboard')) ? 'active' : '' ?>" href="<?= base_url('cms/dashboard') ?>">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>            
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= str_starts_with(current_url(), base_url('cms/jurnal')) ? 'active' : '' ?>" href="<?= base_url('cms/jurnal') ?>">
                            <i class="fas fa-file-alt me-2"></i> Jurnal
                        </a>            
                    </li>

                    <!-- Dropdown Report -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= str_starts_with(current_url(), base_url('cms/report')) ? 'active' : '' ?>" href="#" id="reportDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-book me-2"></i>Report
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= base_url('cms/report/labarugi-filter') ?>"><i class="fas fa-chart-line me-2"></i>Laba Rugi</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('cms/report/neraca-filter') ?>"><i class="fas fa-balance-scale me-2"></i>Neraca</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('cms/report/bukubesar-filter') ?>"><i class="fas fa-book-open me-2"></i>Buku Besar Rekening</a></li>
                            <li><a class="dropdown-item" href="<?= base_url('cms/report/bukubesar-filterket') ?>"><i class="fas fa-book-open me-2"></i>Buku Besar Keterangan</a></li>
                        </ul>
                    </li>
                </ul>

                <div class="utility-nav">
                    <div class="utility-nav-item">
                        <div class="theme-switch" id="themeToggle">
                            <div class="switch-handle">
                                <i class="fas fa-sun"></i>
                            </div>
                        </div>
                    </div>

                    <div class="utility-nav-item">
                        <?php
                        $uname = !empty(detailUser()->user_nama) ? detailUser()->user_nama : '';
                        $ab = explode(' ', $uname);
                        if ($ab) {
                            $uname = $ab[0];
                        }
                        ?>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle user-profile" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($uname) ?>&background=4f46e5&color=fff" class="rounded-circle" width="32" height="32" alt="Profile">
                                <span><?= $uname ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/profile"><i class="fas fa-user"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="/settings"><i class="fas fa-cog"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>Copyright &copy; <?= date('Y') ?> PT. Sadar Jaya Mandiri.</strong>    
            </div>
            <div class="d-none d-sm-block">
                <b>Version</b> 1.0.0
            </div>
        </div>
    </footer>
</div>

    <!-- JavaScript Dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <!-- Theme Toggle Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeSwitch = document.getElementById('themeToggle');
        const icon = themeSwitch.querySelector('i');
        const html = document.documentElement;
        
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-bs-theme', savedTheme);
        updateTheme(savedTheme);
        
        themeSwitch.addEventListener('click', function() {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateTheme(newTheme);
        });
        
        function updateTheme(theme) {
            if (theme === 'dark') {
                themeSwitch.classList.add('dark');
                icon.className = 'fas fa-moon';
            } else {
                themeSwitch.classList.remove('dark');
                icon.className = 'fas fa-sun';
            }
        }
    });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>