<?php
require_once __DIR__ . '/app/secure_config.php';
require_once __DIR__ . '/app/session.php';
start_app_session();

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
$avatar = $_SESSION['user_avatar'] ?? $user['avatar'] ?? null;
$isPremium = !empty($_SESSION['is_premium']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | PlagiaScope</title>
    <link href="https://fonts.googleapis.com/css2?family=Shippori+Mincho+B1:wght@400;700;800&family=Noto+Sans+JP:wght@300;400;500;600;700&family=Zen+Kaku+Gothic+New:wght@300;400;500;700;900&display=swap" rel="stylesheet">
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
            --hint: #a09890;
            --accent: #1a3de4;
            --acc2: #4466f5;
            --acc-soft: #eaedff;
            --acc-brd: #b8c4fd;
            --acc-text: #1a3de4;
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
            --font-display: 'Shippori Mincho B1', serif;
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
            --hint: #3a4560;
            --accent: #5577ff;
            --acc2: #7a99ff;
            --acc-soft: #0e1530;
            --acc-brd: #1e3060;
            --acc-text: #7a99ff;
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

        /* ── Topbar ── */
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
            color: #fff;
            font-weight: 700;
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
        }

        .nav-link:hover {
            color: var(--accent);
            border-color: var(--acc-brd);
            background: var(--acc-soft);
        }

        /* ── Page layout ── */
        .main {
            max-width: 980px;
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

        /* Two-column profile shell */
        .profile-shell {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 24px;
            margin-bottom: 24px;
            align-items: start;
        }

        @media (max-width: 700px) {
            .profile-shell {
                grid-template-columns: 1fr;
            }
        }

        /* ── Left: Profile card ── */
        .profile-card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--r);
            box-shadow: var(--sh);
            overflow: hidden;
            transition: box-shadow .3s, border-color .3s;
        }

        .profile-card:hover {
            box-shadow: var(--sh2);
            border-color: var(--border2);
        }

        .profile-header {
            background: linear-gradient(135deg, var(--accent) 0%, var(--acc2) 100%);
            padding: 36px 24px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
        }

        .avatar {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, .85);
            box-shadow: 0 6px 20px rgba(0, 0, 0, .18);
            transition: transform .2s;
        }

        .avatar:hover {
            transform: scale(1.05);
        }

        .avatar-placeholder {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .2);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 700;
            font-family: var(--font-display);
            border: 3px solid rgba(255, 255, 255, .85);
        }

        .profile-name {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.01em;
            text-align: center;
        }

        .profile-email {
            font-family: var(--font-mono);
            font-size: 12px;
            color: rgba(255, 255, 255, .75);
            text-align: center;
        }

        .plan-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 14px;
            border-radius: 20px;
            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 600;
            background: rgba(255, 255, 255, .2);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .35);
        }

        .plan-badge.premium {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            border-color: transparent;
            box-shadow: 0 2px 8px rgba(245, 158, 11, .4);
        }

        .profile-body {
            padding: 20px 24px 24px;
        }

        .meta-list {
            display: flex;
            flex-direction: column;
            gap: 0;
            margin-bottom: 20px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
        }

        .meta-row:last-child {
            border-bottom: none;
        }

        .meta-row .label {
            color: var(--muted);
        }

        .meta-row .value {
            font-family: var(--font-mono);
            font-weight: 600;
            color: var(--ink);
        }

        .btn-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 11px 16px;
            border-radius: var(--rx);
            font-family: var(--font-mono);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1.5px solid transparent;
            transition: all .2s;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--ink);
            transform: translateY(-1px);
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
            box-shadow: 0 3px 10px rgba(245, 158, 11, .3);
        }

        .btn-premium:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 16px rgba(245, 158, 11, .4);
        }

        /* ── Right: Stats + info ── */
        .right-col {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--r);
            padding: 24px 16px;
            text-align: center;
            box-shadow: var(--sh);
            transition: transform .2s, box-shadow .2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--sh2);
            border-color: var(--border2);
        }

        .stat-value {
            font-family: var(--font-display);
            font-size: 38px;
            font-weight: 700;
            color: var(--accent);
            display: block;
            line-height: 1.1;
        }

        .stat-label {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--muted);
            letter-spacing: .04em;
            margin-top: 6px;
            display: block;
        }

        /* Usage progress bar (free users only) */
        .usage-card {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--r);
            padding: 20px 24px;
            box-shadow: var(--sh);
        }

        .usage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .usage-header span {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--muted);
        }

        .usage-header strong {
            font-family: var(--font-display);
            font-size: 15px;
            font-weight: 600;
        }

        .progress-bg {
            height: 8px;
            border-radius: 4px;
            background: var(--s2);
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--accent), var(--acc2));
            transition: width .6s ease;
        }

        /* Info tiles */
        .info-tiles {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .info-tile {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--rs);
            padding: 14px 16px;
            box-shadow: var(--sh);
        }

        .info-tile .tile-label {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--hint);
            display: block;
            margin-bottom: 4px;
        }

        .info-tile .tile-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
        }

        /* ── History section ── */
        .history-section {
            background: var(--surface);
            border: 1.5px solid var(--border);
            border-radius: var(--r);
            box-shadow: var(--sh);
            overflow: hidden;
        }

        .history-header {
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
        }

        .history-header h2 {
            font-family: var(--font-display);
            font-size: 17px;
            font-weight: 600;
        }

        .history-header span {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--muted);
        }

        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 24px;
            border-bottom: 1px solid var(--border);
            cursor: pointer;
            transition: background .15s;
            gap: 12px;
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .history-item:hover {
            background: var(--s2);
        }

        .h-preview {
            flex: 1;
            min-width: 0;
        }

        .h-text {
            font-size: 14px;
            font-weight: 500;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 5px;
        }

        .h-meta {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .score-pill {
            padding: 3px 9px;
            border-radius: 5px;
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

        .h-date {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--hint);
        }

        .h-chevron {
            color: var(--hint);
            font-size: 18px;
            flex-shrink: 0;
        }

        .empty-state {
            text-align: center;
            padding: 56px 32px;
            color: var(--muted);
        }

        .empty-state svg {
            width: 48px;
            height: 48px;
            opacity: .4;
            margin-bottom: 16px;
            stroke: var(--hint);
        }

        .empty-state p {
            font-family: var(--font-mono);
            font-size: 14px;
        }

        /* ── Modal ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(14, 12, 9, .65);
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
            max-width: 860px;
            max-height: 88vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transform: translateY(16px);
            transition: transform .3s ease;
        }

        .modal-overlay.open .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            padding: 18px 28px;
            border-bottom: 1.5px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--s2);
        }

        .modal-header h3 {
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 600;
        }

        .modal-close {
            width: 38px;
            height: 38px;
            border-radius: 9px;
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
            padding: 28px;
            overflow-y: auto;
        }

        /* Score block inside modal */
        .scan-score-block {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 24px 28px;
            border-radius: var(--r);
            margin-bottom: 28px;
        }

        .scan-score-block.low {
            background: var(--okbg);
            border: 1.5px solid var(--okbrd);
        }

        .scan-score-block.med {
            background: var(--wbg);
            border: 1.5px solid var(--wbrd);
        }

        .scan-score-block.high {
            background: var(--dbg);
            border: 1.5px solid var(--dbrd);
        }

        .big-score {
            font-family: var(--font-display);
            font-size: 54px;
            font-weight: 700;
            line-height: 1;
        }

        .scan-score-block.low .big-score {
            color: var(--ok);
        }

        .scan-score-block.med .big-score {
            color: var(--warn);
        }

        .scan-score-block.high .big-score {
            color: var(--danger);
        }

        .score-label {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }

        .scan-meta-grid {
            display: grid;
            gap: 10px;
            margin-bottom: 28px;
        }

        .scan-meta-row {
            display: flex;
            font-size: 14px;
            gap: 8px;
        }

        .scan-meta-row strong {
            color: var(--muted);
            font-weight: 500;
            min-width: 120px;
        }

        .text-section {
            margin-bottom: 28px;
        }

        .text-section h4 {
            font-family: var(--font-display);
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .text-content {
            background: var(--s2);
            border: 1.5px solid var(--border);
            border-radius: var(--rx);
            padding: 16px;
            font-size: 13px;
            line-height: 1.7;
            color: var(--ink2);
            max-height: 260px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: var(--font-body);
        }

        .sources-section h4 {
            font-family: var(--font-display);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .source-item {
            display: flex;
            gap: 16px;
            padding: 16px;
            border: 1.5px solid var(--border);
            border-radius: var(--rx);
            margin-bottom: 12px;
            background: var(--surface);
            transition: border-color .2s, box-shadow .2s, transform .2s;
        }

        .source-item:hover {
            border-color: var(--border2);
            box-shadow: var(--sh);
            transform: translateY(-1px);
        }

        .source-rank {
            width: 40px;
            height: 40px;
            border-radius: 9px;
            background: var(--acc-soft);
            display: grid;
            place-items: center;
            font-family: var(--font-mono);
            font-size: 14px;
            font-weight: 600;
            color: var(--acc-text);
            flex-shrink: 0;
        }

        .source-info {
            flex: 1;
            min-width: 0;
        }

        .source-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .source-url {
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--accent);
            text-decoration: none;
            word-break: break-all;
        }

        .source-url:hover {
            text-decoration: underline;
        }

        .source-match {
            font-family: var(--font-mono);
            font-size: 11px;
            color: var(--muted);
            margin-top: 6px;
        }
    </style>
</head>

<body>

    <!-- Topbar -->
    <div class="topbar">
        <a href="<?php echo htmlspecialchars(app_path('/')); ?>" class="brand">
            <div class="brand-ico">P</div>
            <span class="brand-name">Plagia<em>Scope</em></span>
        </a>
        <div class="top-r">
            <a href="<?php echo htmlspecialchars(app_path('chat')); ?>" class="nav-link">New Scan</a>
            <button class="dm-btn" id="dmToggle" title="Toggle theme">
                <svg id="sunIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5" />
                    <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                </svg>
                <svg id="moonIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                </svg>
            </button>
            <a href="<?php echo htmlspecialchars(app_path('auth/logout')); ?>" class="nav-link">Logout</a>
        </div>
    </div>

    <main class="main">

        <!-- Profile shell: left sidebar + right content -->
        <div class="profile-shell">

            <!-- Left: Profile card -->
            <div class="profile-card">
                <div class="profile-header">
                    <?php if (!empty($avatar)): ?>
                        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="avatar">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></div>
                    <div class="profile-email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
                    <span class="plan-badge <?php echo $isPremium ? 'premium' : ''; ?>">
                        <?php echo $isPremium ? '★ Premium' : 'Free Plan'; ?>
                    </span>
                </div>
                <div class="profile-body">
                    <div class="meta-list">
                        <div class="meta-row">
                            <span class="label">Daily limit</span>
                            <span class="value"><?php echo htmlspecialchars($limit); ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="label">Scans today</span>
                            <span class="value"><?php echo $todayScans; ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="label">Remaining</span>
                            <span class="value"><?php echo $remaining; ?></span>
                        </div>
                        <div class="meta-row">
                            <span class="label">Total scans</span>
                            <span class="value"><?php echo count($scans); ?></span>
                        </div>
                    </div>
                    <div class="btn-stack">
                        <a href="<?php echo htmlspecialchars(app_path('chat')); ?>" class="btn btn-primary">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            New Scan
                        </a>
                        <?php if (!$isPremium): ?>
                            <a href="<?php echo htmlspecialchars(app_path('premium')); ?>" class="btn btn-premium">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.86L12 17.77l-6.18 3.23L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                </svg>
                                Upgrade to Premium
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo htmlspecialchars(app_path('auth/logout')); ?>" class="btn btn-secondary">
                            Sign Out
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div class="right-col">

                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-value"><?php echo count($scans); ?></span>
                        <span class="stat-label">Total Scans</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?php echo $todayScans; ?></span>
                        <span class="stat-label">Scans Today</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?php echo $remaining; ?></span>
                        <span class="stat-label">Remaining</span>
                    </div>
                </div>

                <!-- Usage progress (free users only) -->
                <?php if (!$isPremium): ?>
                    <div class="usage-card">
                        <div class="usage-header">
                            <strong>Daily Usage</strong>
                            <span><?php echo $todayScans; ?> / 10 scans used</span>
                        </div>
                        <div class="progress-bg">
                            <div class="progress-fill" style="width: <?php echo min(100, ($todayScans / 10) * 100); ?>%"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Info tiles -->
                <div class="info-tiles">
                    <div class="info-tile">
                        <span class="tile-label">Account type</span>
                        <span class="tile-value"><?php echo $isPremium ? 'Premium' : 'Free'; ?></span>
                    </div>
                    <div class="info-tile">
                        <span class="tile-label">Sign-in method</span>
                        <span class="tile-value">Google OAuth</span>
                    </div>
                    <div class="info-tile">
                        <span class="tile-label">History shown</span>
                        <span class="tile-value">Last 20 scans</span>
                    </div>
                    <div class="info-tile">
                        <span class="tile-label">Daily quota</span>
                        <span class="tile-value"><?php echo $limit; ?></span>
                    </div>
                </div>

            </div><!-- /right-col -->
        </div><!-- /profile-shell -->

        <!-- Scan History -->
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
                        <p>No scans yet. Click "New Scan" above to get started!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($scans as $scan):
                        $score = (int)($scan['plagiarism_score'] ?? 0);
                        $scoreClass = $score < 20 ? 'score-low' : ($score < 50 ? 'score-med' : 'score-high');
                    ?>
                        <div class="history-item" onclick="openScanModal(<?php echo htmlspecialchars(json_encode($scan)); ?>)">
                            <div class="h-preview">
                                <div class="h-text"><?php echo htmlspecialchars($scan['text_preview'] ?? 'File upload'); ?></div>
                                <div class="h-meta">
                                    <span class="score-pill <?php echo $scoreClass; ?>"><?php echo $score; ?>% match</span>
                                    <span class="h-date"><?php echo date('M j, Y · g:i A', strtotime($scan['created_at'])); ?></span>
                                </div>
                            </div>
                            <span class="h-chevron">›</span>
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
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>

    <script>
        // ── Dark mode ──
        const html = document.documentElement;
        const dmToggle = document.getElementById('dmToggle');
        const sunIcon = document.getElementById('sunIcon');
        const moonIcon = document.getElementById('moonIcon');

        function applyTheme(dark) {
            html.setAttribute('data-theme', dark ? 'dark' : 'light');
            sunIcon.style.display = dark ? 'none' : 'block';
            moonIcon.style.display = dark ? 'block' : 'none';
        }

        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(savedTheme === 'dark' || (!savedTheme && prefersDark));

        dmToggle.addEventListener('click', () => {
            const isDark = html.getAttribute('data-theme') === 'dark';
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
            applyTheme(!isDark);
        });

        // ── Modal ──
        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }

        function openScanModal(scan) {
            const resultData = JSON.parse(scan.result_data || '{}');
            const sources = resultData.sources || [];
            const score = Math.round(scan.plagiarism_score || 0);
            const cls = score < 20 ? 'low' : score < 50 ? 'med' : 'high';
            const verdict = cls === 'low' ? 'Low match' : cls === 'med' ? 'Moderate match' : 'High match';
            const fullText = resultData.text ? escapeHtml(resultData.text) : '';

            const sourcesHtml = sources.length === 0 ?
                '<p style="color:var(--muted);text-align:center;padding:20px;font-family:var(--font-mono);font-size:13px;">No matching sources found.</p>' :
                sources.map((src, i) => `
                    <div class="source-item">
                        <div class="source-rank">${String(i + 1).padStart(2, '0')}</div>
                        <div class="source-info">
                            <div class="source-title">${escapeHtml(src.title || 'Untitled')}</div>
                            <a class="source-url" href="${escapeHtml(src.url || '#')}" target="_blank" rel="noopener">${escapeHtml(src.url || '')}</a>
                            <div class="source-match">Match: ${(src.score || 0).toFixed(1)}% &nbsp;·&nbsp; ${src.plagiarismWords || 0} words</div>
                        </div>
                    </div>`).join('');

            document.getElementById('modalBody').innerHTML = `
                <div class="scan-score-block ${cls}">
                    <span class="big-score">${score}%</span>
                    <div>
                        <div style="font-size:16px;font-weight:600;font-family:var(--font-display);">${verdict}</div>
                        <div class="score-label">Plagiarism score</div>
                    </div>
                </div>
                <div class="scan-meta-grid">
                    <div class="scan-meta-row"><strong>Date scanned:</strong> ${new Date(scan.created_at).toLocaleString()}</div>
                    <div class="scan-meta-row"><strong>Sources found:</strong> ${sources.length}</div>
                    ${scan.file_name ? `<div class="scan-meta-row"><strong>File:</strong> ${escapeHtml(scan.file_name)}</div>` : ''}
                </div>
                ${fullText ? `
                <div class="text-section">
                    <h4>Original Text</h4>
                    <div class="text-content">${fullText}</div>
                </div>` : ''}
                <div class="sources-section">
                    <h4>Matching Sources (${sources.length})</h4>
                    ${sourcesHtml}
                </div>`;

            document.getElementById('scanModal').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeScanModal(event) {
            if (!event || event.target.id === 'scanModal') {
                document.getElementById('scanModal').classList.remove('open');
                document.body.style.overflow = '';
            }
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeScanModal();
        });
    </script>
</body>

</html>