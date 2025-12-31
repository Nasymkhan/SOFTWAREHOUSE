<?php
// ============================================
// USER REGISTRATION HANDLER
// Unified signup for all Z9 Projects
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
$required_fields = ['username', 'email', 'password', 'full_name', 'country', 'location'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Sanitize inputs
$username = sanitizeInput($data['username']);
$email = sanitizeInput($data['email']);
$password = $data['password']; // Don't sanitize password
$full_name = sanitizeInput($data['full_name']);
$country = sanitizeInput($data['country']);
$location = sanitizeInput($data['location']);

// ============================================
// VALIDATION
// ============================================

// Validate username format and length
if (!validateUsername($username)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username must be 3-50 characters, alphanumeric and underscore only']);
    exit;
}

// Validate email format
if (!validateEmail($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate password strength
if (!validatePasswordStrength($password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 8 characters with uppercase, lowercase, and numbers'
    ]);
    exit;
}

// Validate location is provided (REQUIRED)
if (strlen($location) < 3) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Location is required (minimum 3 characters)']);
    exit;
}

// Validate country is provided
if (strlen($country) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Country is required']);
    exit;
}

// ============================================
// CHECK FOR EXISTING ACCOUNT
// ============================================

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
$stmt->bind_param('ss', $email, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email or username already registered']);
    logError("Registration attempt with existing email: $email");
    exit;
}

// ============================================
// CREATE NEW USER ACCOUNT
// ============================================

try {
    $password_hash = hashPassword($password);
    
    $stmt = $conn->prepare(
        "INSERT INTO users (username, email, password_hash, full_name, country, location, status, email_verified) 
         VALUES (?, ?, ?, ?, ?, ?, 'active', TRUE)"
    );
    
    $stmt->bind_param('ssssss', $username, $email, $password_hash, $full_name, $country, $location);
    
    if (!$stmt->execute()) {
        throw new Exception('Database insert failed');
    }
    
    $user_id = $conn->insert_id;
    
    // Grant access to main project
    $stmt = $conn->prepare(
        "INSERT INTO user_project_access (user_id, project_name, access_level) VALUES (?, 'z9-software-house', 'viewer')"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    // Log successful registration
    logLoginAttempt($conn, $user_id, $username, $email, 'z9-software-house', 'success');
    
    // Create session
    $session_token = startUserSession($conn, $user_id, 'z9-software-house');
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully!',
        'session_token' => $session_token,
        'user' => [
            'id' => $user_id,
            'username' => $username,
            'email' => $email,
            'full_name' => $full_name,
            'country' => $country,
            'location' => $location
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
    logError("Registration error for $email: " . $e->getMessage());
}

$conn->close();
?>
