<?php

declare(strict_types=1);

/**
 * Database handler for user management and scan history
 * Supports both SQLite (default) and MySQL
 */
class PlagiaDatabase
{
    private PDO $pdo;
    private static ?self $instance = null;
    private string $dbType;

    private function __construct()
    {
        // Check if MySQL config exists, otherwise use SQLite
        if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
            $this->dbType = 'mysql';
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $this->pdo = new PDO($dsn, DB_USER, defined('DB_PASS') ? DB_PASS : '');
        } else {
            $this->dbType = 'sqlite';
            $dbPath = __DIR__ . '/../data/plagiascope.db';
            $dbDir = dirname($dbPath);

            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0777, true);
            }

            $this->pdo = new PDO('sqlite:' . $dbPath);
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if ($this->dbType === 'sqlite') {
            $this->initTablesSqlite();
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initTablesSqlite(): void
    {
        // Users table - SQLite syntax
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            google_id TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            name TEXT NOT NULL,
            avatar TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_premium INTEGER DEFAULT 0
        )");

        // Scan history table - SQLite syntax
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS scans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            session_id TEXT,
            text_preview TEXT,
            file_name TEXT,
            plagiarism_score REAL,
            sources_count INTEGER,
            result_data TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        // Create indexes
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_google ON users(google_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_scans_user ON scans(user_id)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_scans_session ON scans(session_id)");
    }

    /**
     * Find or create user from Google OAuth data
     */
    public function findOrCreateUser(array $googleData): array
    {
        // Try to find existing user
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE google_id = ?");
        $stmt->execute([$googleData['id']]);
        $user = $stmt->fetch();

        if ($user) {
            // Update last login
            $stmt = $this->pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$user['id']]);
            return $user;
        }

        // Create new user
        $stmt = $this->pdo->prepare("INSERT INTO users (google_id, email, name, avatar) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $googleData['id'],
            $googleData['email'],
            $googleData['name'],
            $googleData['picture'] ?? null
        ]);

        return $this->getUserById((int)$this->pdo->lastInsertId());
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Save a scan to history
     */
    public function saveScan(?int $userId, string $sessionId, array $data): int
    {
        $textPreview = substr($data['text'] ?? ($data['file_name'] ?? 'File upload'), 0, 200);

        $stmt = $this->pdo->prepare("INSERT INTO scans 
            (user_id, session_id, text_preview, file_name, plagiarism_score, sources_count, result_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");

        $resultData = json_encode($data['result'] ?? []);

        $stmt->execute([
            $userId,
            $sessionId,
            $textPreview,
            $data['file_name'] ?? null,
            $data['score'] ?? 0,
            $data['sources'] ?? 0,
            $resultData
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Get user's scan history
     */
    public function getUserScans(int $userId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM scans WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Count scans for a session (for guest rate limiting)
     */
    public function countSessionScans(string $sessionId, int $hours = 24): int
    {
        if ($this->dbType === 'mysql') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM scans 
                WHERE session_id = ? AND user_id IS NULL 
                AND created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)");
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM scans 
                WHERE session_id = ? AND user_id IS NULL 
                AND created_at > datetime('now', '-? hours')");
        }
        $stmt->execute([$sessionId, $hours]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Count scans for a user today
     */
    public function countUserScansToday(int $userId): int
    {
        if ($this->dbType === 'mysql') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM scans 
                WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM scans 
                WHERE user_id = ? AND created_at > datetime('now', '-1 day')");
        }
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function getScanById(int $scanId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM scans WHERE id = ?");
        $stmt->execute([$scanId]);
        $scan = $stmt->fetch();
        return $scan ?: null;
    }
}
