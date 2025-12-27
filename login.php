<?php
// ============================================
// USER LOGIN HANDLER
// Unified authentication across all Z9 Projects
// ============================================

require_once 'auth_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($data['username']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username/Email and password required']);
    exit;
}

$username_or_email = sanitizeInput($data['username']);
$password = $data['password']; // Don't sanitize password

// ============================================
// FIND USER BY USERNAME OR EMAIL
// ============================================

$stmt = $conn->prepare(
    "SELECT id, username, email, password_hash, full_name, profile_pic_url, country, location, status, login_attempts 
     FROM users 
     WHERE (username = ? OR email = ?) AND status = 'active'"
);

$stmt->bind_param('ss', $username_or_email, $username_or_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Log failed attempt
    logLoginAttempt($conn, null, $username_or_email, $username_or_email, 'z9-software-house', 'failed', 'User not found');
    
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    exit;
}

$user = $result->fetch_assoc();

// ============================================
// CHECK LOGIN ATTEMPTS (Prevent brute force)
// ============================================

if ($user['login_attempts'] >= 5) {
    // Lock account after 5 failed attempts
    $stmt = $conn->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    
    logLoginAttempt($conn, $user['id'], $user['username'], $user['email'], 'z9-software-house', 'suspended', 'Too many failed login attempts');
    
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Account suspended due to too many failed login attempts. Contact support.']);
    exit;
}

// ============================================
// VERIFY PASSWORD
// ============================================

if (!verifyPassword($password, $user['password_hash'])) {
    // Increment login attempts
    $attempts = $user['login_attempts'] + 1;
    $stmt = $conn->prepare("UPDATE users SET login_attempts = ?, last_login_attempt = NOW() WHERE id = ?");
    $stmt->bind_param('ii', $attempts, $user['id']);
    $stmt->execute();
    
    logLoginAttempt($conn, $user['id'], $user['username'], $user['email'], 'z9-software-house', 'failed', 'Invalid password');
    
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    exit;
}

// ============================================
// LOGIN SUCCESS - CREATE SESSION
// ============================================

try {
    $platform = $data['platform'] ?? 'z9-software-house';
    $session_token = startUserSession($conn, $user['id'], $platform);
    
    logLoginAttempt($conn, $user['id'], $user['username'], $user['email'], $platform, 'success');
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'session_token' => $session_token,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'country' => $user['country'],
            'location' => $user['location'],
            'profile_pic_url' => $user['profile_pic_url']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Login failed']);
    logError("Login error for {$user['username']}: " . $e->getMessage());
}

$conn->close();
?>
