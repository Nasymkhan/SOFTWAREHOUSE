<?php
// ============================================
// Z9 INTERNATIONAL SOFTWARE HOUSE
// ADMIN PANEL - DASHBOARD
// ============================================

session_start();

// Check if logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Include database connection
require_once 'db.php';

// Get statistics
$job_stats = $conn->query("SELECT COUNT(*) as total FROM job_applications");
$job_count = $job_stats->fetch_assoc()['total'];

$message_stats = $conn->query("SELECT COUNT(*) as total FROM contact_messages");
$message_count = $message_stats->fetch_assoc()['total'];

$pending_stats = $conn->query("SELECT COUNT(*) as total FROM job_applications WHERE status = 'pending'");
$pending_count = $pending_stats->fetch_assoc()['total'];

$new_messages = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'new'");
$new_msg_count = $new_messages->fetch_assoc()['total'];

// Get recent applications
$recent_apps = $conn->query("SELECT * FROM job_applications ORDER BY submitted_at DESC LIMIT 5");

// Get recent messages
$recent_msgs = $conn->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC LIMIT 5");

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Z9 International</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        .navbar {
            background: linear-gradient(135deg, #0066cc 0%, #00d4ff 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            font-size: 1.5rem;
        }

        .navbar-right {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .navbar-right a, .navbar-right button {
            color: white;
            text-decoration: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .navbar-right a:hover, .navbar-right button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 2.5rem;
            color: #0066cc;
            font-weight: bold;
        }

        .data-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f5f5f5;
            font-weight: 600;
            color: #333;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .action-btn {
            padding: 5px 10px;
            margin: 0 3px;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background 0.3s;
        }

        .action-btn:hover {
            background: #0052a3;
        }

        .action-btn.delete {
            background: #ff6b6b;
        }

        .action-btn.delete:hover {
            background: #ff5252;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-reviewed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-accepted {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .no-data {
            text-align: center;
            color: #999;
            padding: 2rem;
        }

        .welcome-message {
            background: linear-gradient(135deg, #0066cc 0%, #00d4ff 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .welcome-message h2 {
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 8px;
            }

            .action-btn {
                padding: 4px 8px;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><i class="fas fa-shield-alt"></i> Z9 International Admin</h1>
        <div class="navbar-right">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</span>
            <a href="?action=logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-message">
            <h2>Dashboard</h2>
            <p>Overview of job applications and contact messages</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Applications</h3>
                <div class="stat-number"><?php echo $job_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Applications</h3>
                <div class="stat-number"><?php echo $pending_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Messages</h3>
                <div class="stat-number"><?php echo $message_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>New Messages</h3>
                <div class="stat-number"><?php echo $new_msg_count; ?></div>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="data-section">
            <h3 class="section-title">Recent Job Applications</h3>
            <?php if ($recent_apps->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Experience</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($app = $recent_apps->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['name']); ?></td>
                                <td><?php echo htmlspecialchars($app['email']); ?></td>
                                <td><?php echo htmlspecialchars($app['role']); ?></td>
                                <td><?php echo $app['experience']; ?> years</td>
                                <td><?php echo date('M d, Y', strtotime($app['submitted_at'])); ?></td>
                                <td><span class="status-badge status-<?php echo $app['status']; ?>"><?php echo ucfirst($app['status']); ?></span></td>
                                <td>
                                    <button class="action-btn" onclick="viewApplication(<?php echo $app['id']; ?>)">View</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">No applications yet</div>
            <?php endif; ?>
        </div>

        <!-- Recent Messages -->
        <div class="data-section">
            <h3 class="section-title">Recent Contact Messages</h3>
            <?php if ($recent_msgs->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($msg = $recent_msgs->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                <td><?php echo htmlspecialchars(substr($msg['subject'], 0, 40)); ?>...</td>
                                <td><?php echo date('M d, Y', strtotime($msg['submitted_at'])); ?></td>
                                <td><span class="status-badge status-<?php echo ($msg['status'] === 'new' ? 'pending' : $msg['status']); ?>"><?php echo ucfirst($msg['status']); ?></span></td>
                                <td>
                                    <button class="action-btn" onclick="viewMessage(<?php echo $msg['id']; ?>)">View</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">No messages yet</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function viewApplication(id) {
            alert('View application: ' + id + ' - Feature coming soon');
        }

        function viewMessage(id) {
            alert('View message: ' + id + ' - Feature coming soon');
        }
    </script>
</body>
</html>
