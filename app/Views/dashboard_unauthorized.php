<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<style>
    .welcome-container {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
    }

    .welcome-card {
        max-width: 800px;
        padding: 3rem;
        text-align: center;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        transform: translateY(0);
        transition: all 0.3s ease;
    }

    .welcome-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(31, 38, 135, 0.2);
    }

    .welcome-icon {
        font-size: 4rem;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, var(--primary-color), #4f46e5);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .welcome-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #1a1a1a, #4a4a4a);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .welcome-subtitle {
        font-size: 1.1rem;
        color: #666;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
        100% { transform: translateY(0px); }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="welcome-container">
    <div class="welcome-card">
        <div class="welcome-icon animate-float">
            <i class="fas fa-chart-line"></i>
        </div>
        <h1 class="welcome-title">Selamat Datang di GL System</h1>
        <p class="welcome-subtitle">
            Sistem informasi akuntansi yang membantu Anda mengelola keuangan dengan lebih efisien dan akurat.
        </p>        
    </div>
</div>
<?= $this->endSection() ?>