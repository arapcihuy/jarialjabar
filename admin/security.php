<?php
require_once __DIR__ . '/../config.php';

class Security {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->ensureTablesExist();
    }

    private function ensureTablesExist() {
        // Create login_attempts table if not exists
        $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            attempts INT DEFAULT 0,
            last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            blocked_until TIMESTAMP NULL,
            UNIQUE KEY unique_ip (ip_address)
        )";
        $this->conn->query($sql);
    }

    public function checkRateLimit($ip) {
        // Cleanup old blocked entries that have expired
        $this->conn->query("UPDATE login_attempts SET attempts = 0, blocked_until = NULL WHERE blocked_until < NOW()");
        
        $stmt = $this->conn->prepare("SELECT attempts, blocked_until FROM login_attempts WHERE ip_address = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if ($row['blocked_until'] && strtotime($row['blocked_until']) > time()) {
                return [
                    'allowed' => false,
                    'message' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.'
                ];
            }
            if ($row['attempts'] >= MAX_LOGIN_ATTEMPTS) {
                // Block for 15 minutes
                $blocked_until = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                $upd = $this->conn->prepare("UPDATE login_attempts SET blocked_until = ? WHERE ip_address = ?");
                $upd->bind_param("ss", $blocked_until, $ip);
                $upd->execute();
                return [
                    'allowed' => false,
                    'message' => 'Terlalu banyak percobaan login. Akun Anda dikunci sementara selama 15 menit.'
                ];
            }
        }
        return ['allowed' => true];
    }

    public function recordFailedLogin($ip) {
        $stmt = $this->conn->prepare("INSERT INTO login_attempts (ip_address, attempts, last_attempt) VALUES (?, 1, NOW()) ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
    }

    public function clearLoginAttempts($ip) {
        $stmt = $this->conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
        $stmt->bind_param("s", $ip);
        $stmt->execute();
    }

    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
