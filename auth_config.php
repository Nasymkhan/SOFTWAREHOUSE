<?php
// ============================================
// AUTHENTICATION CONFIGURATION & HELPERS
// Unified Login System for all Z9 Projects
// ============================================

// Database Connection
$db_host = 'localhost';
$db_user = 'root';
$db_password = ''; // Change to your password
$db_name = 'z9_international';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// ============================================
// SECURITY FUNCTIONS
// ============================================

/**
 * Hash a password securely using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify a password against its hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Sanitize input to prevent XSS attacks
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength (min 8 chars, 1 uppercase, 1 lowercase, 1 number)
 */
function validatePasswordStrength($password) {
    if (strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    return true;
}

/**
 * Validate username (alphanumeric, underscore, 3-50 chars)
 */
function validateUsername($username) {
    if (strlen($username) < 3 || strlen($username) > 50) {
        return false;
    }
    return preg_match('/^[a-zA-Z0-9_]+$/', $username);
}

/**
 * Generate secure session token
 */
function generateSessionToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Get user agent string
 */
function getUserAgent() {
    return substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 500);
}

/**
 * Get client IP address (handles proxies)
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/**
 * Log login attempt (success or failure)
 */
function logLoginAttempt($conn, $user_id, $username, $email, $platform, $status, $reason = null) {
    $ip = getClientIP();
    $user_agent = getUserAgent();
    
    $stmt = $conn->prepare(
        "INSERT INTO login_history (user_id, username, email, ip_address, user_agent, platform, login_status, failure_reason) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    
    $stmt->bind_param('issssss', $user_id, $username, $email, $ip, $user_agent, $platform, $status, $reason);
    return $stmt->execute();
}

/**
 * Start session for user
 */
function startUserSession($conn, $user_id, $platform = 'z9-software-house') {
    // Generate secure token
    $token = generateSessionToken();
    $ip = getClientIP();
    $user_agent = getUserAgent();
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    // Store session
    $stmt = $conn->prepare(
        "INSERT INTO user_sessions (user_id, session_token, platform, user_agent, ip_address, expires_at) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    
    $stmt->bind_param('isssss', $user_id, $token, $platform, $user_agent, $ip, $expires);
    $stmt->execute();
    
    // Update last login
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    return $token;
}

/**
 * Verify session token
 */
function verifySessionToken($conn, $token) {
    $stmt = $conn->prepare(
        "SELECT u.id, u.username, u.email, u.full_name, u.profile_pic_url, u.status, s.expires_at 
         FROM user_sessions s 
         JOIN users u ON s.user_id = u.id 
         WHERE s.session_token = ? AND s.expires_at > NOW()"
    );
    
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Log error to file
 */
function logError($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_file = __DIR__ . '/error.log';
    $log_message = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// ============================================
// CORS HEADERS (Allow AJAX requests)
// ============================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

?>
