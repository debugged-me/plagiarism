<?php
require_once __DIR__ . '/app/secure_config.php';
require_once __DIR__ . '/app/session.php';
start_app_session();

// Check if user is logged in
if (empty($_SESSION['is_logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: ' . app_path('auth/google'));
    exit;
}

require_once __DIR__ . '/app/database.php';

$db = PlagiaDatabase::getInstance();
$user = $db->getUserById((int)$_SESSION['user_id']);

if (!$user) {
    session_destroy();
    header('Location: ' . app_path('auth/google'));
    exit;
}

$scans = $db->getUserScans((int)$_SESSION['user_id'], 20);
$todayScans = $db->countUserScansToday((int)$_SESSION['user_id']);
$limit = !empty($_SESSION['is_premium']) ? 'Unlimited' : '10/day';
$remaining = !empty($_SESSION['is_premium']) ? '∞' : max(0, 10 - $todayScans);

// Get avatar from session or database
$avatar = $_SESSION['user_avatar'] ?? $user['avatar'] ?? null;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | PlagiaScope</title>
    <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho+B1:wght@400;500;600;700;800&family=Shippori+Mincho:wght@400;500;600;700;800&family=Noto+Sans+JP:wght@300;400;500;600;700&family=Zen+Kaku+Gothic+New:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: var(--font-body);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            transition: background .4s, color .4s;
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
            animation: topIn .5s ease both;
            transition: background .4s;
        }

        @keyframes topIn {
            from {
                opacity: 0;
                transform: translateY(-100%);
            }

            to {
                opacity: 1;
                transform: none;
            }
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

        .dm-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: var(--surface);
            display: grid;
            place-items: center;
            cursor: pointer;
            font-size: 16px;
            transition: all .2s;
        }

        .dm-btn:hover {
            border-color: var(--acc-brd);
            background: var(--acc-soft);
            transform: scale(1.08) rotate(15deg);
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

        .main {
            max-width: 960px;
            margin: 0 auto;
            padding: 40px 24px;
            animation: fadeIn .6s ease both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .profile-card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--r);
            padding: 36px;
            box-shadow: var(--sh);
            display: flex;
            align-items: center;
            gap: 28px;
            margin-bottom: 24px;
            transition: all .3s ease;
        }

        .profile-card:hover {
            box-shadow: var(--sh2);
            border-color: var(--border2);
        }

        .avatar-wrap {
            position: relative;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border);
            transition: all .2s;
        }

        .avatar:hover {
            border-color: var(--accent);
            transform: scale(1.05);
        }

        .avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--accent);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 600;
            font-family: var(--font-display);
            border: 3px solid var(--border);
        }

        .profile-info h1 {
            font-family: var(--font-display);
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--ink);
        }

        .profile-info .email {
            font-family: var(--font-mono);
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: var(--acc-soft);
            color: var(--accent);
            border: 1px solid var(--acc-brd);
            border-radius: 20px;
            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .02em;
        }

        .badge.premium {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #fff;
            border-color: transparent;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--r);
            padding: 28px 20px;
            box-shadow: var(--sh);
            text-align: center;
            transition: all .2s;
        }

        .stat-card:hover {
            border-color: var(--border2);
            transform: translateY(-2px);
            box-shadow: var(--sh2);
        }

        .stat-value {
            font-family: var(--font-display);
            font-size: 40px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 8px;
        }

        .stat-label {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--muted);
            letter-spacing: .02em;
        }

        .actions {
            display: flex;
            gap: 16px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 28px;
            border-radius: var(--rx);
            font-family: var(--font-mono);
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
            cursor: pointer;
            border: 1.5px solid transparent;
            flex: 1;
            min-width: 160px;
            max-width: 240px;
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
            border-color: var(--border);
        }

        .btn-secondary:hover {
            background: var(--s2);
            border-color: var(--border2);
        }

        .btn-premium {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 14px rgba(245, 158, 11, .3);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, .4);
        }

        .history-section {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--r);
            box-shadow: var(--sh);
            overflow: hidden;
        }

        .history-header {
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
        }

        .history-header h2 {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 600;
            color: var(--ink);
        }

        .history-header span {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--muted);
        }

        .history-list {
            padding: 8px;
        }

        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-radius: var(--rx);
            transition: all .2s;
            margin-bottom: 4px;
        }

        .history-item:last-child {
            margin-bottom: 0;
        }

        .history-item:hover {
            background: var(--bg);
        }

        .history-preview {
            flex: 1;
            min-width: 0;
        }

        .history-preview p {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--ink);
            font-size: 14px;
            font-weight: 500;
        }

        .history-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 4px;
        }

        .score-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 600;
        }

        .score-low {
            background: var(--okbg);
            color: var(--ok);
            border: 1px solid var(--okbrd);
        }

        .score-med {
            background: var(--wbg);
            color: var(--warn);
            border: 1px solid var(--wbrd);
        }

        .score-high {
            background: var(--dbg);
            color: var(--danger);
            border: 1px solid var(--dbrd);
        }

        .history-date {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--muted);
        }

        .empty-state {
            text-align: center;
            padding: 60px 48px;
            color: var(--muted);
        }

        .empty-state svg {
            width: 56px;
            height: 56px;
            margin-bottom: 20px;
            opacity: .5;
            stroke: var(--faint);
        }

        .empty-state p {
            font-family: var(--font-mono);
            font-size: 14px;
        }

        /* Clickable history items */
        .history-item {
            position: relative;
        }

        .history-item:hover {
            background: var(--s2);
            border-color: var(--border2);
        }

        .view-details {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--accent);
            opacity: 0;
            transition: opacity .2s;
        }

        .history-item:hover .view-details {
            opacity: 1;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(14, 12, 9, .7);
            backdrop-filter: blur(8px);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all .3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .modal-overlay.open {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--r);
            box-shadow: var(--sh2), 0 25px 50px -12px rgba(14, 12, 9, .25);
            width: 100%;
            max-width: 900px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transform: translateY(20px);
            transition: transform .3s ease;
        }

        .modal-overlay.open .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            padding: 20px 32px;
            border-bottom: 1.5px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--s2);
        }

        .modal-header h3 {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 600;
            color: var(--ink);
        }

        .modal-close {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            transition: all .2s;
            display: grid;
            place-items: center;
        }

        .modal-close:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: var(--dbg);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 32px;
            overflow-y: auto;
        }

        .scan-summary {
            display: flex;
            gap: 40px;
            align-items: flex-start;
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1.5px solid var(--border);
        }

        @media (max-width: 640px) {
            .scan-summary {
                flex-direction: column;
                gap: 20px;
            }
        }

        .scan-score {
            text-align: center;
            padding: 28px 40px;
            border-radius: var(--r);
            min-width: 160px;
            flex-shrink: 0;
        }

        .scan-score.low {
            background: var(--okbg);
            border: 2px solid var(--okbrd);
        }

        .scan-score.med {
            background: var(--wbg);
            border: 2px solid var(--wbrd);
        }

        .scan-score.high {
            background: var(--dbg);
            border: 2px solid var(--dbrd);
        }

        .score-num {
            font-family: var(--font-display);
            font-size: 52px;
            font-weight: 700;
            display: block;
            line-height: 1;
        }

        .scan-score.low .score-num {
            color: var(--ok);
        }

        .scan-score.med .score-num {
            color: var(--warn);
        }

        .scan-score.high .score-num {
            color: var(--danger);
        }

        .score-label {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-top: 8px;
            display: block;
        }

        .scan-meta {
            flex: 1;
            display: grid;
            gap: 12px;
        }

        .scan-meta p {
            font-family: var(--font-body);
            font-size: 15px;
            color: var(--ink);
            margin: 0;
            line-height: 1.5;
        }

        .scan-meta strong {
            color: var(--muted);
            font-weight: 500;
            display: inline-block;
            min-width: 100px;
        }

        .text-section {
            margin-bottom: 32px;
        }

        .text-section h4 {
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--ink);
        }

        .text-content {
            background: var(--s2);
            border: 1.5px solid var(--border);
            border-radius: var(--rx);
            padding: 20px;
            font-family: var(--font-body);
            font-size: 14px;
            line-height: 1.7;
            color: var(--ink2);
            max-height: 300px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .sources-section h4 {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--ink);
        }

        .source-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            border: 1.5px solid var(--border);
            border-radius: var(--rx);
            margin-bottom: 16px;
            background: var(--surface);
            transition: all .2s;
        }

        .source-item:hover {
            border-color: var(--border2);
            box-shadow: var(--sh);
            transform: translateY(-2px);
        }

        .source-rank {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--s2);
            display: grid;
            place-items: center;
            font-family: var(--font-mono);
            font-size: 15px;
            font-weight: 600;
            color: var(--accent);
            flex-shrink: 0;
        }

        .source-info {
            flex: 1;
            min-width: 0;
        }

        .source-title {
            font-family: var(--font-body);
            font-size: 16px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .source-url {
            font-family: var(--font-mono);
            font-size: 13px;
            color: var(--accent);
            text-decoration: none;
            word-break: break-all;
        }

        .source-url:hover {
            text-decoration: underline;
        }

        .source-match {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--muted);
            margin-top: 10px;
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
            <button class="dm-btn" id="dmToggle" title="Toggle theme" style="display: grid; place-items: center;">
                <svg id="sunIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5" />
                    <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                </svg>
                <svg id="moonIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                </svg>
            </button>
            <a href="<?php echo htmlspecialchars(app_path('auth/logout')); ?>" class="nav-link">Logout</a>
        </div>
    </div>

    <main class="main">
        <div class="profile-card">
            <div class="avatar-wrap">
                <?php if (!empty($avatar)): ?>
                    <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="avatar">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
                <p class="email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                <span class="badge <?php echo !empty($_SESSION['is_premium']) ? 'premium' : ''; ?>">
                    <?php echo !empty($_SESSION['is_premium']) ? '★ Premium' : 'Free Plan'; ?>
                </span>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($scans); ?></div>
                <div class="stat-label">Total Scans</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $todayScans; ?></div>
                <div class="stat-label">Scans Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $remaining; ?></div>
                <div class="stat-label">Remaining (<?php echo $limit; ?>)</div>
            </div>
        </div>

        <div class="actions">
            <a href="<?php echo htmlspecialchars(app_path('chat')); ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                New Scan
            </a>
            <?php if (empty($_SESSION['is_premium'])): ?>
                <a href="<?php echo htmlspecialchars(app_path('premium')); ?>" class="btn btn-premium">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.86L12 17.77l-6.18 3.23L7 14.14 2 9.27l6.91-1.01L12 2z" />
                    </svg>
                    Upgrade to Premium
                </a>
            <?php endif; ?>
        </div>

        <div class="history-section">
            <div class="history-header">
                <h2>Scan History</h2>
                <span>Last 20 scans</span>
            </div>
            <div class="history-list">
                <?php if (empty($scans)): ?>
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p>No scans yet. Start by clicking "New Scan" above!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($scans as $scan):
                        $score = (int)($scan['plagiarism_score'] ?? 0);
                        $scoreClass = $score < 20 ? 'score-low' : ($score < 50 ? 'score-med' : 'score-high');
                        $resultData = json_decode($scan['result_data'] ?? '{}', true);
                        $sources = $resultData['sources'] ?? [];
                    ?>
                        <div class="history-item" onclick="openScanModal(<?php echo htmlspecialchars(json_encode($scan)); ?>)" style="cursor: pointer;">
                            <div class="history-preview">
                                <p><?php echo htmlspecialchars($scan['text_preview'] ?? 'File upload'); ?></p>
                                <div class="history-meta">
                                    <span class="score-badge <?php echo $scoreClass; ?>">
                                        <?php echo $score; ?>% match
                                    </span>
                                    <span class="history-date">
                                        <?php echo date('M j, Y g:i A', strtotime($scan['created_at'])); ?>
                                    </span>
                                    <span class="view-details">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                        Click to view details
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Scan Detail Modal -->
    <div class="modal-overlay" id="scanModal" onclick="closeScanModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3>Scan Details</h3>
                <button class="modal-close" onclick="closeScanModal()" aria-label="Close">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>

    <script>
        // Dark mode toggle
        const dmToggle = document.getElementById('dmToggle');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');
        const html = document.documentElement;

        function updateThemeIcon() {
            const isDark = html.getAttribute('data-theme') === 'dark';
            sunIcon.style.display = isDark ? 'none' : 'block';
            moonIcon.style.display = isDark ? 'block' : 'none';
        }

        // Check saved preference
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.setAttribute('data-theme', 'dark');
        }
        updateThemeIcon();

        dmToggle.addEventListener('click', () => {
            const isDark = html.getAttribute('data-theme') === 'dark';
            if (isDark) {
                html.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            } else {
                html.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
            updateThemeIcon();
        });

        // Modal functions
        function openScanModal(scan) {
            const modal = document.getElementById('scanModal');
            const body = document.getElementById('modalBody');

            const resultData = JSON.parse(scan.result_data || '{}');
            const sources = resultData.sources || [];
            const score = Math.round(scan.plagiarism_score || 0);

            let sourcesHtml = '';
            if (sources.length === 0) {
                sourcesHtml = '<p style="color: var(--muted); text-align: center; padding: 20px;">No matching sources found.</p>';
            } else {
                sourcesHtml = sources.map((src, i) => `
                    <div class="source-item">
                        <div class="source-rank">${String(i + 1).padStart(2, '0')}</div>
                        <div class="source-info">
                            <div class="source-title">${escapeHtml(src.title || 'Untitled')}</div>
                            <a class="source-url" href="${escapeHtml(src.url || '#')}" target="_blank" rel="noopener">${escapeHtml(src.url || '')}</a>
                            <div class="source-match">Match: ${src.score ? src.score.toFixed(1) : '0'}% | ${src.plagiarismWords || 0} words</div>
                        </div>
                    </div>
                `).join('');
            }

            // Get full original text
            const fullText = resultData.text ? escapeHtml(resultData.text) : '';
            const textTooLong = fullText.length > 3000;

            body.innerHTML = `
                <div class="scan-summary">
                    <div class="scan-score ${score > 50 ? 'high' : score > 20 ? 'med' : 'low'}">
                        <span class="score-num">${score}%</span>
                        <span class="score-label">Plagiarism Score</span>
                    </div>
                    <div class="scan-meta">
                        <p><strong>Date:</strong> ${new Date(scan.created_at).toLocaleString()}</p>
                        <p><strong>Sources Found:</strong> ${sources.length}</p>
                        ${scan.file_name ? `<p><strong>File:</strong> ${escapeHtml(scan.file_name)}</p>` : ''}
                    </div>
                </div>
                
                ${fullText ? `
                <div class="text-section">
                    <h4>Original Text</h4>
                    <div class="text-content">${fullText}</div>
                </div>
                ` : ''}
                
                <div class="sources-section">
                    <h4>Matching Sources (${sources.length})</h4>
                    <div class="sources-list">${sourcesHtml}</div>
                </div>
            `;

            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeScanModal(event) {
            if (!event || event.target.id === 'scanModal' || event.target.classList.contains('modal-close')) {
                document.getElementById('scanModal').classList.remove('open');
                document.body.style.overflow = '';
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeScanModal();
        });
    </script>
</body>

</html>