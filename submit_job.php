<?php
// ============================================
// Z9 INTERNATIONAL SOFTWARE HOUSE
// JOB APPLICATION SUBMISSION
// ============================================

// Include database connection
require_once 'db.php';

// Get JSON data from request
$input = json_decode(file_get_contents('php://input'), true);

// Check if data is provided
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data provided']);
    exit;
}

// Extract and sanitize input
$name = sanitizeInput($input['name'] ?? '');
$email = sanitizeInput($input['email'] ?? '');
$phone = sanitizeInput($input['phone'] ?? '');
$cnic = sanitizeInput($input['cnic'] ?? '');
$role = sanitizeInput($input['role'] ?? '');
$experience = intval($input['experience'] ?? 0);
$tech_stack = sanitizeInput($input['tech_stack'] ?? '');
$projects = sanitizeInput($input['projects'] ?? '');
$bio = sanitizeInput($input['bio'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email) || !validateEmail($email)) {
    $errors[] = 'Valid email is required';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required';
}

if (empty($cnic)) {
    $errors[] = 'CNIC is required';
}

if (empty($role)) {
    $errors[] = 'Position is required';
}

if ($experience < 0) {
    $errors[] = 'Years of experience must be a valid number';
}

if (empty($tech_stack)) {
    $errors[] = 'Technical stack is required';
}

if (empty($projects)) {
    $errors[] = 'Previous projects description is required';
}

if (empty($bio)) {
    $errors[] = 'Bio is required';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Check if applicant already applied with same email
$check_query = "SELECT id FROM job_applications WHERE email = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param('s', $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You have already applied with this email address']);
    exit;
}

// Insert into database
$insert_query = "INSERT INTO job_applications (name, email, phone, cnic, role, experience, tech_stack, projects, bio, submitted_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$insert_stmt = $conn->prepare($insert_query);

if ($insert_stmt === false) {
    logError('Prepare failed: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    exit;
}

$insert_stmt->bind_param('sssssisss', $name, $email, $phone, $cnic, $role, $experience, $tech_stack, $projects, $bio);

if ($insert_stmt->execute()) {
    // Log successful submission
    logError("Job application submitted: $name ($email) for position: $role");
    
    // Optionally send email notification (uncomment if email is configured)
    // sendApplicationConfirmationEmail($email, $name, $role);
    // sendAdminNotificationEmail($name, $email, $role);
    
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Your application has been submitted successfully!',
        'application_id' => $insert_stmt->insert_id
    ]);
} else {
    logError('Execute failed: ' . $insert_stmt->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to submit application. Please try again.']);
}

$insert_stmt->close();
$check_stmt->close();
$conn->close();

// ============================================
// EMAIL NOTIFICATION FUNCTIONS (Optional)
// ============================================

/*
function sendApplicationConfirmationEmail($email, $name, $role) {
    $subject = "Application Received - Z9 International";
    $message = "Dear $name,\n\n";
    $message .= "Thank you for applying for the position of $role at Z9 International.\n";
    $message .= "We have received your application and will review it shortly.\n";
    $message .= "We will contact you if you move forward in the hiring process.\n\n";
    $message .= "Best regards,\n";
    $message .= "Z9 International HR Team\n";
    
    $headers = "From: info@z9international.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($email, $subject, $message, $headers);
}

function sendAdminNotificationEmail($name, $email, $role) {
    $admin_email = 'admin@z9international.com';
    $subject = "New Job Application - $name";
    $message = "New application received:\n\n";
    $message .= "Name: $name\n";
    $message .= "Email: $email\n";
    $message .= "Position: $role\n\n";
    $message .= "Login to admin panel to view full details.";
    
    $headers = "From: noreply@z9international.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($admin_email, $subject, $message, $headers);
}
*/

?>
