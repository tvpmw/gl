<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GL Admin Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #3a7bd5, #00d2ff);
            --surface-color: #f8fafc;
            --nav-height: 64px;
        }
        
        body {
            background-color: var(--surface-color);
            min-height: 100vh;
        }

        .top-navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: saturate(200%) blur(8px);
            height: var(--nav-height);
            border-bottom: 1px solid rgba(231, 235, 240, 0.8);
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .nav-link {
            position: relative;
            padding: 0.75rem 1.25rem !important;
            color: #64748b !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #3a7bd5 !important;
        }

        .nav-link.active {
            color: #3a7bd5 !important;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 1.25rem;
            right: 1.25rem;
            height: 2px;
            background: var(--primary-gradient);
            border-radius: 2px;
        }

        .user-profile {
            background: rgba(248, 250, 252, 0.8);
            border-radius: 12px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .user-profile:hover {
            background: rgba(231, 235, 240, 0.8);
        }

        .notification-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: rgba(248, 250, 252, 0.8);
            transition: all 0.3s ease;
            position: relative;
        }

        .notification-btn:hover {
            background: rgba(231, 235, 240, 0.8);
            transform: translateY(-2px);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary-gradient);
            border: 2px solid #fff;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 12px;
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background: rgba(231, 235, 240, 0.8);
            transform: translateX(5px);
        }

        .main-content {
            margin-top: var(--nav-height);
            min-height: calc(100vh - var(--nav-height));
            padding: 2rem;
            position: relative;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            .nav-link.active::before {
                display: none;
            }
            .nav-link.active {
                background: var(--primary-gradient);
                color: white !important;
                border-radius: 8px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg top-navbar fixed-top">
        <div class="container-fluid px-4">
            <a class="brand-logo d-flex align-items-center gap-2" href="/dashboard">
                <i class="fas fa-analytics"></i>
                <span>GL Admin</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= current_url() == base_url('transactions') ? 'active' : '' ?>" href="/transactions">
                            <i class="fas fa-exchange-alt me-2"></i>Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= current_url() == base_url('accounts') ? 'active' : '' ?>" href="/accounts">
                            <i class="fas fa-book me-2"></i>Chart of Accounts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= current_url() == base_url('reports') ? 'active' : '' ?>" href="/reports">
                            <i class="fas fa-chart-bar me-2"></i>Reports
                        </a>
                    </li>
                </ul>

                <!-- Replace the existing navbar-nav section with this -->
                <ul class="navbar-nav align-items-center gap-3">
                    <li class="nav-item">
                        <button class="notification-btn" id="themeToggle" title="Toggle theme">
                            <i class="fas fa-sun"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <a class="notification-btn" href="#" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-profile d-flex align-items-center gap-2" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name=Admin+User&background=3a7bd5&color=fff" class="rounded-circle" width="32" height="32" alt="Profile">
                            <span>Admin User</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/profile"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/settings"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Add before bootstrap.bundle.js -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?= $this->renderSection('scripts') ?>
    <!-- Add in the head section -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <!-- Add before closing body tag, after Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <!-- Add in head section -->
    <style>
        :root {
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: rgba(0,0,0,0.08);
        }
    
        [data-bs-theme="dark"] {
            --bg-main: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: rgba(255,255,255,0.08);
        }
    
        body {
            background-color: var(--bg-main);
            color: var(--text-main);
        }
    
        .card {
            background-color: var(--bg-card);
        }
    
        .text-muted {
            color: var(--text-muted) !important;
        }
    </style>
    
    <!-- Add before closing body tag -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('themeToggle');
        const icon = themeToggle.querySelector('i');
        const html = document.documentElement;
        
        // Load theme from localStorage
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-bs-theme', savedTheme);
        updateIcon(savedTheme);
        
        themeToggle.addEventListener('click', function() {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });
        
        function updateIcon(theme) {
            icon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        }
    });
    </script>
    
    <!-- Add before closing body tag -->
    <script>
    <!-- Add in the head section, after existing styles -->
    <style>
        .theme-switch {
            width: 60px;
            height: 30px;
            background-color: #f1f5f9;
            border-radius: 15px;
            position: relative;
            cursor: pointer;
            transition: background-color 0.3s;
            border: 2px solid #e2e8f0;
        }
    
        .theme-switch.dark {
            background-color: #334155;
            border-color: #475569;
        }
    
        .theme-switch .switch-handle {
            width: 24px;
            height: 24px;
            background-color: white;
            border-radius: 50%;
            position: absolute;
            top: 1px;
            left: 2px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    
        .theme-switch.dark .switch-handle {
            left: 32px;
            background-color: #1e293b;
        }
    
        .theme-switch .switch-handle i {
            font-size: 14px;
            color: #fbbf24;
        }
    
        .theme-switch.dark .switch-handle i {
            color: #f1f5f9;
        }
    </style>
    
    <!-- Replace the theme toggle button in navbar with this -->
    <li class="nav-item">
        <div class="theme-switch" id="themeToggle">
            <div class="switch-handle">
                <i class="fas fa-sun"></i>
            </div>
        </div>
    </li>
    
    <!-- Replace the theme toggle script with this -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeSwitch = document.getElementById('themeToggle');
        const icon = themeSwitch.querySelector('i');
        const html = document.documentElement;
        
        // Load saved theme
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
</body>
</html>