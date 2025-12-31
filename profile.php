<?php
// ============================================
// USER PROFILE MANAGEMENT
// Update name, country, location, profile picture
// ============================================

require_once 'auth_config.php';

header('Content-Type: application/json');

// Verify user is logged in
$token = $_GET['token'] ?? $_POST['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$user = verifySessionToken($conn, str_replace('Bearer ', '', $token));

if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid or expired session']);
    exit;
}

$user_id = $user['id'];

// ============================================
// GET USER PROFILE
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare(
        "SELECT id, username, email, full_name, country, location, profile_pic_url, status, created_at, last_login 
         FROM users WHERE id = ?"
    );
    
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'user' => $profile
    ]);
    exit;
}

// ============================================
// UPDATE USER PROFILE
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Check if updating profile picture or text fields
    if (!empty($_FILES['profile_pic'])) {
        // ============================================
        // HANDLE PROFILE PICTURE UPLOAD
        // ============================================
        
        $file = $_FILES['profile_pic'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB
        
        // Validate file
        if ($file['size'] > $max_file_size) {
            http_response_code(413);
            echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
            exit;
        }
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_extensions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF allowed']);
            exit;
        }
        
        // Create uploads directory if not exists
        $uploads_dir = __DIR__ . '/uploads/profile_pics/';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true);
        }
        
        // Generate unique filename
        $filename = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
        $filepath = $uploads_dir . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            logError("File upload failed for user $user_id");
            exit;
        }
        
        // Save to database
        $profile_pic_url = '/uploads/profile_pics/' . $filename;
        
        $stmt = $conn->prepare(
            "UPDATE users SET profile_pic_url = ?, updated_at = NOW() WHERE id = ?"
        );
        
        $stmt->bind_param('si', $profile_pic_url, $user_id);
        
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile picture updated successfully!',
            'profile_pic_url' => $profile_pic_url
        ]);
        exit;
    }
    
    // ============================================
    // UPDATE TEXT FIELDS (Name, Country, Location)
    // ============================================
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Collect updates
    $updates = [];
    $types = '';
    $params = [];
    
    // Full Name (optional)
    if (!empty($data['full_name'])) {
        $full_name = sanitizeInput($data['full_name']);
        
        // Log change
        $stmt = $conn->prepare(
            "INSERT INTO profile_update_history (user_id, field_name, old_value, new_value) 
             SELECT ?, 'full_name', full_name, ? FROM users WHERE id = ?"
        );
        $stmt->bind_param('isi', $user_id, $full_name, $user_id);
        $stmt->execute();
        
        $updates[] = "full_name = ?";
        $types .= 's';
        $params[] = $full_name;
    }
    
    // Country (optional)
    if (!empty($data['country'])) {
        $country = sanitizeInput($data['country']);
        
        // Log change
        $stmt = $conn->prepare(
            "INSERT INTO profile_update_history (user_id, field_name, old_value, new_value) 
             SELECT ?, 'country', country, ? FROM users WHERE id = ?"
        );
        $stmt->bind_param('isi', $user_id, $country, $user_id);
        $stmt->execute();
        
        $updates[] = "country = ?";
        $types .= 's';
        $params[] = $country;
    }
    
    // Location (REQUIRED)
    if (!empty($data['location'])) {
        $location = sanitizeInput($data['location']);
        
        if (strlen($location) < 3) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Location must be at least 3 characters']);
            exit;
        }
        
        // Log change
        $stmt = $conn->prepare(
            "INSERT INTO profile_update_history (user_id, field_name, old_value, new_value) 
             SELECT ?, 'location', location, ? FROM users WHERE id = ?"
        );
        $stmt->bind_param('isi', $user_id, $location, $user_id);
        $stmt->execute();
        
        $updates[] = "location = ?";
        $types .= 's';
        $params[] = $location;
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }
    
    // Add updated_at and user_id
    $updates[] = "updated_at = NOW()";
    $types .= 'i';
    $params[] = $user_id;
    
    // Build and execute query
    $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Update failed']);
        logError("Profile update failed for user $user_id");
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully!'
    ]);
    exit;
}

// ============================================
// LOGOUT
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $stmt = $conn->prepare("DELETE FROM user_sessions WHERE session_token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);

$conn->close();
?>
