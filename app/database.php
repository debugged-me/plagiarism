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
        $useMysql = defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER');
        $fallback = $this->shouldFallbackToSqlite();
        error_log('DB: useMysql=' . ($useMysql ? 'yes' : 'no') . ', fallback=' . ($fallback ? 'yes' : 'no') . ', host=' . ($_SERVER['HTTP_HOST'] ?? 'none'));

        if ($useMysql) {
            try {
                $this->connectMysql();
                error_log('DB: Connected to MySQL');
            } catch (PDOException $e) {
                if (!$fallback) {
                    error_log('DB: MySQL failed, not falling back. Error: ' . $e->getMessage());
                    throw $e;
                }

                error_log('DB: MySQL unavailable, falling back to SQLite: ' . $e->getMessage());
                $this->connectSqlite();
            }
        } else {
            error_log('DB: Using SQLite (no MySQL config)');
            $this->connectSqlite();
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if ($this->dbType === 'sqlite') {
            $this->initTablesSqlite();
        }
    }

    private function connectMysql(): void
    {
        $this->dbType = 'mysql';
        $password = defined('DB_PASS') ? DB_PASS : '';
        $dsn = $this->buildMysqlDsn(DB_HOST);

        try {
            $this->pdo = new PDO($dsn, DB_USER, $password);
            return;
        } catch (PDOException $e) {
            if (!$this->shouldRetryMysqlOverTcp($e)) {
                throw $e;
            }
        }

        $fallbackDsn = $this->buildMysqlDsn('127.0.0.1');
        $this->pdo = new PDO($fallbackDsn, DB_USER, $password);
    }

    private function buildMysqlDsn(string $host): string
    {
        return 'mysql:host=' . $host . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    }

    private function connectSqlite(): void
    {
        $this->dbType = 'sqlite';
        $dbPath = __DIR__ . '/../data/plagiascope.db';
        $dbDir = dirname($dbPath);

        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        $this->pdo = new PDO('sqlite:' . $dbPath);
        error_log('DB: SQLite connected to ' . $dbPath . ', file_exists=' . (file_exists($dbPath) ? 'yes' : 'no'));
    }

    private function shouldFallbackToSqlite(): bool
    {
        if (defined('FORCE_SQLITE') && FORCE_SQLITE) {
            return true;
        }

        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
        $serverAddr = (string)($_SERVER['SERVER_ADDR'] ?? '');

        return $host === ''
            || str_contains($host, 'localhost')
            || str_contains($host, '127.0.0.1')
            || str_contains($host, '::1')
            || $serverAddr === '127.0.0.1'
            || $serverAddr === '::1';
    }

    private function shouldRetryMysqlOverTcp(PDOException $e): bool
    {
        if (!$this->shouldFallbackToSqlite()) {
            return false;
        }

        if (!defined('DB_HOST') || strtolower((string) DB_HOST) !== 'localhost') {
            return false;
        }

        return str_contains($e->getMessage(), '[2002]')
            || str_contains(strtolower($e->getMessage()), 'no such file or directory');
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
        error_log('DB: findOrCreateUser on ' . $this->dbType);
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
        $newId = (int)$this->pdo->lastInsertId();
        error_log('DB: Created new user with id=' . $newId);

        // Immediately try to fetch it back
        $newUser = $this->getUserById($newId);
        error_log('DB: Immediately fetched new user: ' . ($newUser ? 'found' : 'NOT FOUND'));
        return $newUser;
    }

    public function getUserById(int $id): ?array
    {
        error_log('DB: getUserById(' . $id . ') on ' . $this->dbType);
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        error_log('DB: getUserById result=' . ($user ? 'found' : 'not found'));
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

        // Include original text and file info in result_data for display
        $resultToSave = $data['result'] ?? [];
        if (!empty($data['text'])) {
            $resultToSave['text'] = $data['text'];
        }
        if (!empty($data['file_name'])) {
            $resultToSave['file_name'] = $data['file_name'];
        }
        $resultData = json_encode($resultToSave);

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
        // LIMIT must be cast to int directly for MySQL compatibility
        $sql = "SELECT * FROM scans WHERE user_id = ? ORDER BY created_at DESC LIMIT " . (int)$limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Count scans for a session (for guest rate limiting)
     */
    public function countSessionScans(string $sessionId, int $hours = 24): int
    {
        $hours = (int)$hours;
        if ($this->dbType === 'mysql') {
            $sql = "SELECT COUNT(*) FROM scans 
                WHERE session_id = ? AND user_id IS NULL 
                AND created_at > DATE_SUB(NOW(), INTERVAL {$hours} HOUR)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$sessionId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM scans 
                WHERE session_id = ? AND user_id IS NULL 
                AND created_at > datetime('now', '-{$hours} hours')");
            $stmt->execute([$sessionId]);
        }
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
