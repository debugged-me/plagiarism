<?php
require_once __DIR__ . '/app/secure_config.php';
require_once __DIR__ . '/app/session.php';
start_app_session();

// Check if user is logged in
if (empty($_SESSION['is_logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: ' . app_path('auth/google'));
    exit;
}

// Redirect if already premium
if (!empty($_SESSION['is_premium'])) {
    header('Location: ' . app_path('user'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade to Premium | PlagiaScope</title>
    <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho+B1:wght@400;500;600;700;800&family=Shippori+Mincho:wght@400;500;600;700;800&family=Noto+Sans+JP:wght@300;400;500;600;700&family=Zen+Kaku+Gothic+New:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --bg: #f5f3ef;
            --surface: #ffffff;
            --s2: #eeecea;
            --border: #ddd9d0;
            --border2: #ccc8be;
            --ink: #0e0c09;
            --ink2: #2a2620;
            --muted: #6b6560;
            --faint: #a09890;
            --accent: #1a3de4;
            --acc2: #4466f5;
            --acc-soft: #eaedff;
            --acc-brd: #b8c4fd;
            --danger: #d42020;
            --dbg: #fef2f2;
            --dbrd: #fecaca;
            --warn: #c47a00;
            --wbg: #fffbeb;
            --wbrd: #fde68a;
            --ok: #157a3a;
            --okbg: #ecfdf3;
            --okbrd: #a7f3c8;
            --nav-bg: rgba(245, 243, 239, .94);
            --sh: 0 1px 6px rgba(14, 12, 9, .07);
            --sh2: 0 4px 18px rgba(14, 12, 9, .09);
            --r: 14px;
            --rs: 9px;
            --rx: 7px;
            --font-display: 'Shippori Mincho B1', 'Shippori Mincho', serif;
            --font-body: 'Noto Sans JP', sans-serif;
            --font-mono: 'Zen Kaku Gothic New', monospace;
            --gold: #f59e0b;
            --gold-soft: #fef3c7;
        }
        
        [data-theme="dark"] {
            --bg: #080a0f;
            --surface: #0f1119;
            --s2: #161b26;
            --border: #1f2840;
            --border2: #263350;
            --ink: #eceef8;
            --ink2: #bfc6dc;
            --muted: #6e7d9a;
            --faint: #3a4560;
            --accent: #5577ff;
            --acc2: #7a99ff;
            --acc-soft: #0e1530;
            --acc-brd: #1e3060;
            --nav-bg: rgba(8, 10, 15, .94);
            --sh: 0 1px 6px rgba(0, 0, 0, .35);
            --sh2: 0 4px 18px rgba(0, 0, 0, .45);
            --gold: #fbbf24;
            --gold-soft: rgba(251, 191, 36, .1);
        }
        
        body {
            background: var(--bg);
            color: var(--ink);
            font-family: var(--font-body);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        
        .topbar {
            background: var(--nav-bg);
            border-bottom: 1px solid var(--border);
            padding: 0 36px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(20px) saturate(180%);
            box-shadow: var(--sh);
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .brand-ico {
            width: 32px;
            height: 32px;
            background: var(--accent);
            border-radius: 10px;
            display: grid;
            place-items: center;
            font-size: 15px;
            color: #fff;
            font-weight: 600;
            transition: transform .35s cubic-bezier(.34, 1.56, .64, 1), background .2s;
        }
        
        .brand:hover .brand-ico {
            transform: rotate(-15deg) scale(1.1);
            background: var(--ink);
        }
        
        .brand-name {
            font-family: var(--font-display);
            font-size: 19px;
            font-weight: 700;
            letter-spacing: .04em;
            color: var(--ink);
        }
        
        .brand-name em {
            font-style: italic;
            color: var(--accent);
        }
        
        .top-r {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .nav-link {
            font-family: var(--font-mono);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .04em;
            color: var(--muted);
            text-decoration: none;
            padding: 7px 13px;
            border-radius: var(--rx);
            border: 1.5px solid transparent;
            transition: all .2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .nav-link:hover {
            color: var(--accent);
            border-color: var(--acc-brd);
            background: var(--acc-soft);
        }
        
        .dm-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: var(--surface);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all .2s;
        }
        
        .dm-btn:hover {
            border-color: var(--acc-brd);
            background: var(--acc-soft);
            transform: scale(1.08) rotate(15deg);
        }
        
        .main {
            max-width: 960px;
            margin: 0 auto;
            padding: 40px 24px;
        }
        
        .hero {
            text-align: center;
            margin-bottom: 48px;
            padding: 32px;
            background: linear-gradient(135deg, var(--gold-soft) 0%, var(--bg) 100%);
            border: 1.5px solid var(--border);
            border-radius: var(--r);
            box-shadow: var(--sh);
        }
        
        .hero-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, var(--gold), #f59e0b);
            border-radius: 20px;
            display: grid;
            place-items: center;
            box-shadow: 0 10px 30px rgba(245, 158, 11, .3);
        }
        
        .hero-icon svg {
            width: 40px;
            height: 40px;
            color: #fff;
        }
        
        .hero h1 {
            font-family: var(--font-display);
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--ink);
        }
        
        .hero p {
            font-size: 16px;
            color: var(--muted);
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 48px;
        }
        
        @media (max-width: 640px) {
            .pricing-grid { grid-template-columns: 1fr; }
        }
        
        .plan {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--r);
            padding: 32px;
            box-shadow: var(--sh);
            transition: all .3s;
        }
        
        .plan:hover {
            box-shadow: var(--sh2);
            transform: translateY(-4px);
        }
        
        .plan.popular {
            border-color: var(--gold);
            background: linear-gradient(180deg, var(--surface) 0%, var(--gold-soft) 100%);
            position: relative;
        }
        
        .plan-badge {
            position: absolute;
            top: -12px;
            right: 24px;
            background: var(--gold);
            color: #fff;
            padding: 6px 14px;
            border-radius: 20px;
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .05em;
        }
        
        .plan-name {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--ink);
        }
        
        .plan-price {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 24px;
        }
        
        .plan-price .amount {
            font-family: var(--font-display);
            font-size: 42px;
            font-weight: 700;
            color: var(--ink);
        }
        
        .plan-price .period {
            font-family: var(--font-mono);
            font-size: 14px;
            color: var(--muted);
        }
        
        .plan-features {
            list-style: none;
            margin-bottom: 24px;
        }
        
        .plan-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            font-size: 14px;
            color: var(--ink);
            border-bottom: 1px solid var(--border);
        }
        
        .plan-features li:last-child {
            border-bottom: none;
        }
        
        .plan-features li svg {
            width: 18px;
            height: 18px;
            color: var(--ok);
            flex-shrink: 0;
        }
        
        .plan-features li.not-included {
            color: var(--muted);
        }
        
        .plan-features li.not-included svg {
            color: var(--danger);
        }
        
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 24px;
            border-radius: var(--rx);
            font-family: var(--font-mono);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
            cursor: pointer;
            border: none;
            width: 100%;
        }
        
        .btn-primary {
            background: var(--accent);
            color: #fff;
        }
        
        .btn-primary:hover {
            background: var(--ink);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 61, 228, .25);
        }
        
        .btn-secondary {
            background: var(--surface);
            color: var(--ink);
            border: 1.5px solid var(--border);
        }
        
        .btn-secondary:hover {
            background: var(--s2);
            border-color: var(--border2);
        }
        
        .btn-gold {
            background: linear-gradient(135deg, var(--gold), #f59e0b);
            color: #fff;
        }
        
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(245, 158, 11, .35);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 48px;
        }
        
        @media (max-width: 768px) {
            .features-grid { grid-template-columns: 1fr; }
        }
        
        .feature-card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--r);
            padding: 24px;
            text-align: center;
            box-shadow: var(--sh);
            transition: all .2s;
        }
        
        .feature-card:hover {
            border-color: var(--border2);
            box-shadow: var(--sh2);
        }
        
        .feature-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 16px;
            background: var(--acc-soft);
            border-radius: 12px;
            display: grid;
            place-items: center;
            color: var(--accent);
        }
        
        .feature-card h3 {
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--ink);
        }
        
        .feature-card p {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.5;
        }
        
        .back-link {
            text-align: center;
            margin-top: 24px;
        }
        
        .back-link a {
            font-family: var(--font-mono);
            font-size: 13px;
            color: var(--muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color .2s;
        }
        
        .back-link a:hover {
            color: var(--accent);
        }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="<?php echo htmlspecialchars(app_path('/')); ?>" class="brand">
            <div class="brand-ico">P</div>
            <span class="brand-name">Plagia<em>Scope</em></span>
        </a>
        <div class="top-r">
            <a href="<?php echo htmlspecialchars(app_path('chat')); ?>" class="nav-link">New Scan</a>
            <button class="dm-btn" id="dmToggle" title="Toggle theme">
                <svg id="sunIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
                <svg id="moonIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                </svg>
            </button>
            <a href="<?php echo htmlspecialchars(app_path('user')); ?>" class="nav-link">My Account</a>
        </div>
    </div>
    
    <main class="main">
        <div class="hero">
            <div class="hero-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.86L12 17.77l-6.18 3.23L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <h1>Upgrade to Premium</h1>
            <p>Unlock unlimited scans and advanced features. Choose the plan that works best for you.</p>
        </div>
        
        <div class="pricing-grid">
            <div class="plan">
                <div class="plan-name">Free</div>
                <div class="plan-price">
                    <span class="amount">$0</span>
                    <span class="period">/month</span>
                </div>
                <ul class="plan-features">
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        10 scans per day
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Basic plagiarism check
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Web sources only
                    </li>
                    <li class="not-included">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        Academic database access
                    </li>
                    <li class="not-included">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        Priority support
                    </li>
                </ul>
                <button class="btn btn-secondary" onclick="history.back()">Current Plan</button>
            </div>
            
            <div class="plan popular">
                <div class="plan-badge">RECOMMENDED</div>
                <div class="plan-name">Premium</div>
                <div class="plan-price">
                    <span class="amount">$9</span>
                    <span class="period">/month</span>
                </div>
                <ul class="plan-features">
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <strong>Unlimited</strong> scans
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Advanced plagiarism detection
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Web + Academic databases
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        PDF & document upload
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Priority email support
                    </li>
                </ul>
                <button class="btn btn-gold" onclick="alert('Premium upgrade coming soon!')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.86L12 17.77l-6.18 3.23L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    Upgrade Now
                </button>
            </div>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                    </svg>
                </div>
                <h3>Unlimited Scans</h3>
                <p>Scan as many documents as you need without daily limits</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                    </svg>
                </div>
                <h3>Academic Database</h3>
                <p>Access to journals, papers, and academic publications</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
                <h3>File Upload</h3>
                <p>Upload PDF, DOC, and DOCX files directly</p>
            </div>
        </div>
        
        <div class="back-link">
            <a href="<?php echo htmlspecialchars(app_path('user')); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to My Account
            </a>
        </div>
    </main>
    
    <script>
        const dmToggle = document.getElementById('dmToggle');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');
        const html = document.documentElement;
        
        function updateThemeIcon() {
            const isDark = html.getAttribute('data-theme') === 'dark';
            sunIcon.style.display = isDark ? 'block' : 'none';
            moonIcon.style.display = isDark ? 'none' : 'block';
        }
        
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.setAttribute('data-theme', 'dark');
        }
        updateThemeIcon();
        
        dmToggle.addEventListener('click', () => {
            const isDark = html.getAttribute('data-theme') === 'dark';
            html.setAttribute('data-theme', isDark ? 'light' : 'dark');
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
            updateThemeIcon();
        });
    </script>
</body>
</html>
