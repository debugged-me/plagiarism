<?php
session_start();

// Check if user is logged in
if (empty($_SESSION['is_logged_in']) || empty($_SESSION['user_id'])) {
    header('Location: /auth/google');
    exit;
}

require_once __DIR__ . '/app/database.php';

$db = PlagiaDatabase::getInstance();
$user = $db->getUserById((int)$_SESSION['user_id']);
$scans = $db->getUserScans((int)$_SESSION['user_id'], 20);

if (!$user) {
    // User not found in database, clear session
    session_destroy();
    header('Location: /auth/google');
    exit;
}

$todayScans = $db->countUserScansToday((int)$_SESSION['user_id']);
$limit = !empty($_SESSION['is_premium']) ? 'Unlimited' : '50/day';
$remaining = !empty($_SESSION['is_premium']) ? '∞' : max(0, 50 - $todayScans);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | PlagiaScope</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #0f172a;
            --text-muted: #64748b;
            --accent: #3b82f6;
            --accent-light: #dbeafe;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --radius: 12px;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }
        
        .brand {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent);
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 16px;
        }
        
        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .nav-links a:hover {
            color: var(--accent);
        }
        
        /* Profile Card */
        .profile-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 32px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent-light);
        }
        
        .avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--accent);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 600;
        }
        
        .profile-info h1 {
            font-size: 24px;
            margin-bottom: 4px;
        }
        
        .profile-info p {
            color: var(--text-muted);
            margin-bottom: 12px;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: var(--accent-light);
            color: var(--accent);
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .badge.premium {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 4px;
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 14px;
        }
        
        /* Action Buttons */
        .actions {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            font-size: 15px;
        }
        
        .btn-primary {
            background: var(--accent);
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
        }
        
        .btn-secondary:hover {
            background: var(--bg);
        }
        
        /* History Section */
        .history-section {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .history-header {
            padding: 24px 24px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .history-header h2 {
            font-size: 18px;
        }
        
        .history-list {
            padding: 16px;
        }
        
        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }
        
        .history-item:last-child {
            border-bottom: none;
        }
        
        .history-item:hover {
            background: var(--bg);
            border-radius: 8px;
        }
        
        .history-preview {
            flex: 1;
            min-width: 0;
        }
        
        .history-preview p {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: var(--text);
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
            font-size: 13px;
            font-weight: 600;
        }
        
        .score-low { background: #dcfce7; color: #166534; }
        .score-med { background: #fef3c7; color: #92400e; }
        .score-high { background: #fee2e2; color: #991b1b; }
        
        .history-date {
            color: var(--text-muted);
            font-size: 13px;
        }
        
        .empty-state {
            text-align: center;
            padding: 48px;
            color: var(--text-muted);
        }
        
        .empty-state svg {
            width: 48px;
            height: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <a href="/" class="brand">PlagiaScope</a>
            <nav class="nav-links">
                <a href="/chat.php">New Scan</a>
                <a href="/auth/logout">Logout</a>
            </nav>
        </header>
        
        <div class="profile-card">
            <?php if (!empty($_SESSION['user_avatar'])): ?>
                <img src="<?php echo htmlspecialchars($_SESSION['user_avatar']); ?>" alt="Avatar" class="avatar">
            <?php else: ?>
                <div class="avatar-placeholder">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
                <p><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                <span class="badge <?php echo !empty($_SESSION['is_premium']) ? 'premium' : ''; ?>">
                    <?php echo !empty($_SESSION['is_premium']) ? '⭐ Premium' : 'Free Plan'; ?>
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
            <a href="/chat.php" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                New Scan
            </a>
            <?php if (empty($_SESSION['is_premium'])): ?>
                <button class="btn btn-secondary" onclick="alert('Premium coming soon!')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    Upgrade to Premium
                </button>
            <?php endif; ?>
        </div>
        
        <div class="history-section">
            <div class="history-header">
                <h2>Scan History</h2>
                <span class="stat-label">Last 20 scans</span>
            </div>
            <div class="history-list">
                <?php if (empty($scans)): ?>
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p>No scans yet. Start by clicking "New Scan" above!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($scans as $scan): 
                        $score = (int)($scan['plagiarism_score'] ?? 0);
                        $scoreClass = $score < 20 ? 'score-low' : ($score < 50 ? 'score-med' : 'score-high');
                    ?>
                        <div class="history-item">
                            <div class="history-preview">
                                <p><?php echo htmlspecialchars($scan['text_preview'] ?? 'File upload'); ?></p>
                                <div class="history-meta">
                                    <span class="score-badge <?php echo $scoreClass; ?>">
                                        <?php echo $score; ?>% match
                                    </span>
                                    <span class="history-date">
                                        <?php echo date('M j, Y g:i A', strtotime($scan['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
