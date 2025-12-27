<?php
// ============================================
// Z9 INTERNATIONAL SOFTWARE HOUSE
// CONTACT MESSAGE SUBMISSION
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
$subject = sanitizeInput($input['subject'] ?? '');
$message = sanitizeInput($input['message'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email) || !validateEmail($email)) {
    $errors[] = 'Valid email is required';
}

if (empty($subject)) {
    $errors[] = 'Subject is required';
}

if (empty($message)) {
    $errors[] = 'Message is required';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Insert into database
$insert_query = "INSERT INTO contact_messages (name, email, phone, subject, message, submitted_at) 
                 VALUES (?, ?, ?, ?, ?, NOW())";

$insert_stmt = $conn->prepare($insert_query);

if ($insert_stmt === false) {
    logError('Prepare failed: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    exit;
}

$insert_stmt->bind_param('sssss', $name, $email, $phone, $subject, $message);

if ($insert_stmt->execute()) {
    // Log successful submission
    logError("Contact message submitted: $name ($email) - Subject: $subject");
    
    // Optionally send email notification (uncomment if email is configured)
    // sendContactConfirmationEmail($email, $name);
    // sendAdminContactNotificationEmail($name, $email, $subject);
    
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Your message has been sent successfully! We will contact you soon.',
        'message_id' => $insert_stmt->insert_id
    ]);
} else {
    logError('Execute failed: ' . $insert_stmt->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again.']);
}

$insert_stmt->close();
$conn->close();

// ============================================
// EMAIL NOTIFICATION FUNCTIONS (Optional)
// ============================================

/*
function sendContactConfirmationEmail($email, $name) {
    $subject = "We Received Your Message - Z9 International";
    $message = "Dear $name,\n\n";
    $message .= "Thank you for contacting Z9 International.\n";
    $message .= "We have received your message and will get back to you as soon as possible.\n";
    $message .= "Our team typically responds within 24-48 hours.\n\n";
    $message .= "Best regards,\n";
    $message .= "Z9 International Team\n";
    
    $headers = "From: info@z9international.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($email, $subject, $message, $headers);
}

function sendAdminContactNotificationEmail($name, $email, $subject) {
    $admin_email = 'admin@z9international.com';
    $message_subject = "New Contact Message from $name";
    $message = "New contact message received:\n\n";
    $message .= "Name: $name\n";
    $message .= "Email: $email\n";
    $message .= "Subject: $subject\n\n";
    $message .= "Login to admin panel to view full details.";
    
    $headers = "From: noreply@z9international.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($admin_email, $message_subject, $message, $headers);
}
*/

?>
