<?php
/**
 * ETSU YAHAYA ABUBAKAR POLYTECHNIC MINNA - Complete Portal System
 * Affiliation: NUHU BAMALI POLYTECHNIC, ZARIA
 * 
 * This is a standalone file that creates the database tables automatically.
 * Just place this file in your web server directory and access it.
 */

// ========== DATABASE CONFIGURATION ==========
$db_host = 'localhost';
$db_name = 'eyaimas_portal';
$db_user = 'root';
$db_pass = '';

// School Information
define('SCHOOL_NAME', 'ETSU YAHAYA ABUBAKAR POLYTECHNIC MINNA');
define('SCHOOL_SHORT', 'EYAIMAS POLYTECHNIC');
define('SCHOOL_AFFILIATION', 'In Affiliation with NUHU BAMALI POLYTECHNIC, ZARIA');
define('SCHOOL_ADDRESS', 'Gbeganako Village Gidan Mongoro Bida Road Minna Niger state P.M.B 96');
define('SCHOOL_EMAIL', 'talk2eyaimas@gmail.com');
define('SCHOOL_PHONE1', '07037119521');
define('SCHOOL_PHONE2', '08032883038');
define('SCHOOL_WEBSITE', 'https://eyaimas.com.ng');
define('PRIMARY_COLOR', '#27ae60'); // Lemon Green
define('SECONDARY_COLOR', '#2980b9'); // Blue
define('SITE_URL', 'http://localhost');

// Enable error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// ========== DATABASE CONNECTION & SETUP ==========
try {
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db_name`");
    
    // ========== CREATE TABLES ==========
    
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `matric_no` VARCHAR(50) UNIQUE,
        `fullname` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) UNIQUE NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(20),
        `address` TEXT,
        `department` VARCHAR(100),
        `level` VARCHAR(20),
        `profile_pic` VARCHAR(255),
        `role` ENUM('admin', 'student', 'lecturer', 'staff') DEFAULT 'student',
        `status` ENUM('active', 'inactive', 'graduated') DEFAULT 'active',
        `email_verified` BOOLEAN DEFAULT FALSE,
        `verification_token` VARCHAR(255),
        `reset_token` VARCHAR(255),
        `reset_expires` DATETIME,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Courses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `courses` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `course_code` VARCHAR(20) UNIQUE NOT NULL,
        `course_title` VARCHAR(200) NOT NULL,
        `credit_unit` INT DEFAULT 3,
        `department` VARCHAR(100),
        `level` VARCHAR(20),
        `semester` ENUM('first', 'second') DEFAULT 'first',
        `lecturer_id` INT,
        `description` TEXT,
        `status` ENUM('active', 'inactive') DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`lecturer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
    )");
    
    // Enrollments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `enrollments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `course_id` INT NOT NULL,
        `session` VARCHAR(20) NOT NULL,
        `semester` ENUM('first', 'second') NOT NULL,
        `status` ENUM('enrolled', 'dropped', 'completed') DEFAULT 'enrolled',
        `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_enrollment` (`student_id`, `course_id`, `session`, `semester`)
    )");
    
    // Grades table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `grades` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `course_id` INT NOT NULL,
        `session` VARCHAR(20) NOT NULL,
        `semester` ENUM('first', 'second') NOT NULL,
        `test_score` DECIMAL(5,2) DEFAULT 0,
        `exam_score` DECIMAL(5,2) DEFAULT 0,
        `total_score` DECIMAL(5,2) GENERATED ALWAYS AS (test_score + exam_score) STORED,
        `grade` CHAR(2),
        `grade_point` DECIMAL(3,2),
        `remark` VARCHAR(50),
        `updated_by` INT,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_grade` (`student_id`, `course_id`, `session`, `semester`)
    )");
    
    // Payments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `payments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` INT NOT NULL,
        `invoice_no` VARCHAR(50) UNIQUE NOT NULL,
        `amount` DECIMAL(10,2) NOT NULL,
        `payment_type` ENUM('tuition', 'acceptance', 'accommodation', 'library', 'sport', 'other') DEFAULT 'tuition',
        `session` VARCHAR(20),
        `semester` ENUM('first', 'second'),
        `payment_method` VARCHAR(50),
        `transaction_ref` VARCHAR(100),
        `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
        `paid_at` DATETIME,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    )");
    
    // News/Announcements table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `announcements` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(200) NOT NULL,
        `content` TEXT NOT NULL,
        `category` ENUM('academic', 'event', 'general', 'exam', 'admission') DEFAULT 'general',
        `target_audience` ENUM('all', 'students', 'staff', 'lecturers') DEFAULT 'all',
        `image` VARCHAR(255),
        `posted_by` INT,
        `is_pinned` BOOLEAN DEFAULT FALSE,
        `status` ENUM('published', 'draft', 'archived') DEFAULT 'published',
        `expires_at` DATETIME,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`posted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
    )");
    
    // Events table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `events` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(200) NOT NULL,
        `description` TEXT,
        `event_date` DATE NOT NULL,
        `event_time` TIME,
        `location` VARCHAR(255),
        `image` VARCHAR(255),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Library table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `library` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(200) NOT NULL,
        `author` VARCHAR(100),
        `file_path` VARCHAR(255),
        `category` VARCHAR(100),
        `downloads` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `messages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `sender_id` INT NOT NULL,
        `receiver_id` INT NOT NULL,
        `subject` VARCHAR(200),
        `message` TEXT NOT NULL,
        `is_read` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    )");
    
    // ========== INSERT DEFAULT ADMIN ==========
    $admin_email = 'admin@eyaimas.edu.ng';
    $admin_password = password_hash('Admin@123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$admin_email]);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO users (fullname, email, password, role, email_verified) VALUES (?, ?, ?, 'admin', TRUE)")->execute(['System Administrator', $admin_email, $admin_password]);
    }
    
    // ========== INSERT SAMPLE COURSES ==========
    $sample_courses = [
        ['CSC101', 'Introduction to Computer Science', 3, 'Computer Science', '100', 'first', 'Basic concepts of computing'],
        ['CSC102', 'Programming Fundamentals', 3, 'Computer Science', '100', 'first', 'Introduction to programming'],
        ['MAT101', 'General Mathematics I', 3, 'Mathematics', '100', 'first', 'Algebra and Trigonometry'],
        ['ENG101', 'Use of English', 2, 'General Studies', '100', 'first', 'English language and communication'],
        ['PHY101', 'General Physics I', 3, 'Sciences', '100', 'first', 'Mechanics and properties of matter'],
        ['CSC201', 'Data Structures', 3, 'Computer Science', '200', 'first', 'Advanced programming concepts'],
        ['CSC202', 'Database Management', 3, 'Computer Science', '200', 'first', 'Database design and SQL'],
        ['MAT201', 'Calculus I', 3, 'Mathematics', '200', 'first', 'Limits and derivatives'],
    ];
    
    foreach ($sample_courses as $course) {
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE course_code = ?");
        $stmt->execute([$course[0]]);
        if (!$stmt->fetch()) {
            $pdo->prepare("INSERT INTO courses (course_code, course_title, credit_unit, department, level, semester, description) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute($course);
        }
    }
    
    // ========== INSERT SAMPLE ANNOUNCEMENTS ==========
    $sample_announcements = [
        ['Welcome to EYAIMAS Polytechnic', 'We are pleased to welcome all new and returning students to the 2024/2025 academic session. Registration is now open.', 'general', 'all'],
        ['First Semester Examination', 'The first semester examination will commence on December 15th, 2024. All students are advised to prepare adequately.', 'exam', 'students'],
        ['Matriculation Ceremony', 'The matriculation ceremony for fresh students will hold on November 10th, 2024 at the Polytechnic Auditorium.', 'event', 'students'],
        ['Course Registration Deadline', 'Course registration closes on November 30th, 2024. Late registration will attract a penalty fee.', 'academic', 'students'],
    ];
    
    foreach ($sample_announcements as $ann) {
        $stmt = $pdo->prepare("SELECT id FROM announcements WHERE title = ? LIMIT 1");
        $stmt->execute([$ann[0]]);
        if (!$stmt->fetch()) {
            $pdo->prepare("INSERT INTO announcements (title, content, category, target_audience, status) VALUES (?, ?, ?, ?, 'published')")->execute($ann);
        }
    }
    
    // ========== INSERT SAMPLE EVENTS ==========
    $sample_events = [
        ['Matriculation Ceremony', 'Official induction of fresh students', '2024-11-10', '10:00:00', 'Polytechnic Auditorium'],
        ['ICT Workshop', 'Free ICT training for students', '2024-11-25', '09:00:00', 'Computer Lab 1'],
        ['Career Fair', 'Meet with top employers', '2024-12-05', '11:00:00', 'School Hall'],
        ['End of Semester Party', 'Celebrate the end of exams', '2024-12-20', '16:00:00', 'Student Center'],
    ];
    
    foreach ($sample_events as $event) {
        $stmt = $pdo->prepare("SELECT id FROM events WHERE title = ? LIMIT 1");
        $stmt->execute([$event[0]]);
        if (!$stmt->fetch()) {
            $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, location) VALUES (?, ?, ?, ?, ?)")->execute($event);
        }
    }
    
} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// ========== HELPER FUNCTIONS ==========
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function calculateGradePoint($score) {
    if ($score >= 70) return ['A', 5.00, 'Excellent'];
    if ($score >= 60) return ['B', 4.00, 'Very Good'];
    if ($score >= 50) return ['C', 3.00, 'Good'];
    if ($score >= 45) return ['D', 2.00, 'Pass'];
    if ($score >= 40) return ['E', 1.00, 'Poor'];
    return ['F', 0.00, 'Fail'];
}

function getStudentCGPA($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT g.total_score, c.credit_unit FROM grades g JOIN courses c ON g.course_id = c.id WHERE g.student_id = ?");
    $stmt->execute([$student_id]);
    $grades = $stmt->fetchAll();
    
    $total_points = 0;
    $total_credits = 0;
    foreach ($grades as $grade) {
        $gp = calculateGradePoint($grade['total_score'])[1];
        $total_points += $gp * $grade['credit_unit'];
        $total_credits += $grade['credit_unit'];
    }
    return $total_credits > 0 ? round($total_points / $total_credits, 2) : 0;
}

// ========== HANDLE POST ACTIONS ==========
$action = $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    // Student Login
    if ($action === 'login') {
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['matric_no'] = $user['matric_no'];
            
            $response['success'] = true;
            $response['message'] = 'Login successful';
            $response['role'] = $user['role'];
        } else {
            $response['message'] = 'Invalid email or password';
        }
        echo json_encode($response);
        exit;
    }
    
    // Student Registration
    if ($action === 'register') {
        $fullname = trim($data['fullname'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirm = $data['confirm_password'] ?? '';
        $phone = trim($data['phone'] ?? '');
        $matric_no = trim($data['matric_no'] ?? '');
        
        if ($password !== $confirm) {
            $response['message'] = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $response['message'] = 'Password must be at least 6 characters';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Invalid email address';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR matric_no = ?");
            $stmt->execute([$email, $matric_no]);
            if ($stmt->fetch()) {
                $response['message'] = 'Email or Matric Number already registered';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, phone, matric_no, role, email_verified) VALUES (?, ?, ?, ?, ?, 'student', TRUE)");
                if ($stmt->execute([$fullname, $email, $hashed, $phone, $matric_no])) {
                    $response['success'] = true;
                    $response['message'] = 'Registration successful! Please login.';
                } else {
                    $response['message'] = 'Registration failed';
                }
            }
        }
        echo json_encode($response);
        exit;
    }
    
    // Update Profile
    if ($action === 'update_profile' && isLoggedIn()) {
        $fullname = trim($data['fullname'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $address = trim($data['address'] ?? '');
        
        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, phone = ?, address = ? WHERE id = ?");
        if ($stmt->execute([$fullname, $phone, $address, getUserId()])) {
            $_SESSION['user_name'] = $fullname;
            $response['success'] = true;
            $response['message'] = 'Profile updated successfully';
        } else {
            $response['message'] = 'Update failed';
        }
        echo json_encode($response);
        exit;
    }
    
    // Change Password
    if ($action === 'change_password' && isLoggedIn()) {
        $current = $data['current_password'] ?? '';
        $new = $data['new_password'] ?? '';
        $confirm = $data['confirm_password'] ?? '';
        
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([getUserId()]);
        $user = $stmt->fetch();
        
        if (password_verify($current, $user['password'])) {
            if ($new !== $confirm) {
                $response['message'] = 'New passwords do not match';
            } elseif (strlen($new) < 6) {
                $response['message'] = 'Password must be at least 6 characters';
            } else {
                $hashed = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, getUserId()]);
                $response['success'] = true;
                $response['message'] = 'Password changed successfully';
            }
        } else {
            $response['message'] = 'Current password is incorrect';
        }
        echo json_encode($response);
        exit;
    }
    
    // Send Message
    if ($action === 'send_message' && isLoggedIn()) {
        $receiver_email = trim($data['receiver_email'] ?? '');
        $subject = trim($data['subject'] ?? '');
        $message = trim($data['message'] ?? '');
        
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$receiver_email]);
        $receiver = $stmt->fetch();
        
        if ($receiver) {
            $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([getUserId(), $receiver['id'], $subject, $message])) {
                $response['success'] = true;
                $response['message'] = 'Message sent successfully';
            } else {
                $response['message'] = 'Failed to send message';
            }
        } else {
            $response['message'] = 'Recipient not found';
        }
        echo json_encode($response);
        exit;
    }
}

// ========== LOGOUT ==========
if ($action === 'logout') {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ========== GET DATA FOR LOGGED IN USERS ==========
$student_courses = [];
$student_grades = [];
$student_payments = [];
$announcements_list = [];
$events_list = [];
$messages_received = [];
$messages_sent = [];
$cgpa = 0;
$total_credits = 0;

if (isLoggedIn()) {
    $user_id = getUserId();
    $user_role = getUserRole();
    
    // Get announcements
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE status = 'published' ORDER BY is_pinned DESC, created_at DESC LIMIT 10");
    $stmt->execute();
    $announcements_list = $stmt->fetchAll();
    
    // Get events
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 10");
    $stmt->execute();
    $events_list = $stmt->fetchAll();
    
    if ($user_role === 'student') {
        // Get enrolled courses
        $stmt = $pdo->prepare("SELECT c.*, e.status as enrollment_status FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE e.student_id = ? AND e.status = 'enrolled'");
        $stmt->execute([$user_id]);
        $student_courses = $stmt->fetchAll();
        
        // Get grades
        $stmt = $pdo->prepare("SELECT g.*, c.course_code, c.course_title, c.credit_unit FROM grades g JOIN courses c ON g.course_id = c.id WHERE g.student_id = ?");
        $stmt->execute([$user_id]);
        $student_grades = $stmt->fetchAll();
        
        // Calculate CGPA
        $cgpa = getStudentCGPA($pdo, $user_id);
        
        // Get payments
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $student_payments = $stmt->fetchAll();
        
        // Get messages
        $stmt = $pdo->prepare("SELECT m.*, u.fullname as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $messages_received = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("SELECT m.*, u.fullname as receiver_name FROM messages m JOIN users u ON m.receiver_id = u.id WHERE m.sender_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $messages_sent = $stmt->fetchAll();
    }
    
    // Get user details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SCHOOL_SHORT; ?> - Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        :root {
            --primary: <?php echo PRIMARY_COLOR; ?>;
            --secondary: <?php echo SECONDARY_COLOR; ?>;
            --primary-dark: #1e8449;
            --secondary-dark: #1a5276;
        }
        .bg-primary { background-color: var(--primary); }
        .bg-secondary { background-color: var(--secondary); }
        .text-primary { color: var(--primary); }
        .text-secondary { color: var(--secondary); }
        .border-primary { border-color: var(--primary); }
        .hover\:bg-primary-dark:hover { background-color: var(--primary-dark); }
        .btn-primary {
            background-color: var(--primary);
            color: white;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        .btn-secondary {
            background-color: var(--secondary);
            color: white;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: var(--secondary-dark);
        }
        .gradient-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .sidebar {
            transition: transform 0.3s ease;
        }
        .sidebar.active {
            transform: translateX(0);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 1000;
                height: 100vh;
                overflow-y: auto;
            }
            .sidebar.active {
                transform: translateX(0);
            }
        }
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
        .toast {
            background: white;
            padding: 12px 20px;
            margin-top: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-left: 4px solid var(--primary);
            animation: slideIn 0.3s ease;
        }
        .toast.error { border-left-color: #e74c3c; }
        .toast.success { border-left-color: var(--primary); }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .grade-A { background: #27ae60; color: white; }
        .grade-B { background: #2ecc71; color: white; }
        .grade-C { background: #f39c12; color: white; }
        .grade-D { background: #e67e22; color: white; }
        .grade-E { background: #95a5a6; color: white; }
        .grade-F { background: #e74c3c; color: white; }
    </style>
</head>
<body class="bg-gray-100">

<?php if (!isLoggedIn()): ?>
<!-- ========== LANDING PAGE ========== -->
<header class="gradient-header text-white">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold"><?php echo SCHOOL_NAME; ?></h1>
                <p class="text-sm opacity-90"><?php echo SCHOOL_AFFILIATION; ?></p>
            </div>
            <div class="flex gap-3">
                <button onclick="showAuth('login')" class="bg-white text-green-600 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition">Login</button>
                <button onclick="showAuth('register')" class="border-2 border-white px-6 py-2 rounded-full font-semibold hover:bg-white hover:text-green-600 transition">Register</button>
            </div>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="relative bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'); min-height: 500px;">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative container mx-auto px-4 py-24 text-center text-white">
        <h2 class="text-4xl md:text-6xl font-bold mb-4">Welcome to EYAIMAS Polytechnic</h2>
        <p class="text-xl md:text-2xl mb-8">Excellence in Education, Character, and Service</p>
        <button onclick="showAuth('register')" class="btn-primary px-8 py-3 rounded-full text-lg font-semibold">Apply Now</button>
    </div>
</section>

<!-- Features -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h3 class="text-3xl font-bold text-center mb-12">Why Choose EYAIMAS?</h3>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center p-6 rounded-xl shadow-lg card-hover">
                <i class="fas fa-graduation-cap text-5xl text-primary mb-4"></i>
                <h4 class="text-xl font-bold mb-2">Quality Education</h4>
                <p class="text-gray-600">Accredited programs with experienced lecturers and modern facilities.</p>
            </div>
            <div class="text-center p-6 rounded-xl shadow-lg card-hover">
                <i class="fas fa-laptop-code text-5xl text-primary mb-4"></i>
                <h4 class="text-xl font-bold mb-2">ICT Driven</h4>
                <p class="text-gray-600">State-of-the-art computer labs and e-learning platforms.</p>
            </div>
            <div class="text-center p-6 rounded-xl shadow-lg card-hover">
                <i class="fas fa-handshake text-5xl text-primary mb-4"></i>
                <h4 class="text-xl font-bold mb-2">Industry Partnerships</h4>
                <p class="text-gray-600">Strong ties with industries for internships and job placements.</p>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h3 class="text-3xl font-bold mb-4">About Our Institution</h3>
                <p class="text-gray-600 mb-4">ETSU YAHAYA ABUBAKAR POLYTECHNIC MINNA is a premier institution dedicated to providing quality technical and vocational education in affiliation with NUHU BAMALI POLYTECHNIC, ZARIA.</p>
                <p class="text-gray-600 mb-4">Located at Gbeganako Village, Gidan Mongoro, Bida Road, Minna, Niger State, we offer a conducive learning environment with modern facilities.</p>
                <div class="flex flex-col gap-2 mt-6">
                    <p><i class="fas fa-map-marker-alt text-primary w-6"></i> <?php echo SCHOOL_ADDRESS; ?></p>
                    <p><i class="fas fa-phone text-primary w-6"></i> <?php echo SCHOOL_PHONE1; ?> | <?php echo SCHOOL_PHONE2; ?></p>
                    <p><i class="fas fa-envelope text-primary w-6"></i> <?php echo SCHOOL_EMAIL; ?></p>
                    <p><i class="fas fa-globe text-primary w-6"></i> <?php echo SCHOOL_WEBSITE; ?></p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-primary text-white p-6 rounded-xl text-center">
                    <div class="text-4xl font-bold">20+</div>
                    <div>Programs</div>
                </div>
                <div class="bg-secondary text-white p-6 rounded-xl text-center">
                    <div class="text-4xl font-bold">50+</div>
                    <div>Lecturers</div>
                </div>
                <div class="bg-green-500 text-white p-6 rounded-xl text-center">
                    <div class="text-4xl font-bold">1000+</div>
                    <div>Students</div>
                </div>
                <div class="bg-orange-500 text-white p-6 rounded-xl text-center">
                    <div class="text-4xl font-bold">95%</div>
                    <div>Graduate Success</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- News & Events -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h3 class="text-3xl font-bold text-center mb-12">Latest News & Events</h3>
        <div class="grid md:grid-cols-2 gap-8">
            <div>
                <h4 class="text-xl font-bold mb-4 flex items-center"><i class="fas fa-newspaper text-primary mr-2"></i> Announcements</h4>
                <?php foreach ($announcements_list as $ann): ?>
                <div class="border-b border-gray-200 py-3">
                    <p class="font-semibold"><?php echo htmlspecialchars($ann['title']); ?></p>
                    <p class="text-sm text-gray-500"><?php echo formatDate($ann['created_at']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <div>
                <h4 class="text-xl font-bold mb-4 flex items-center"><i class="fas fa-calendar-alt text-secondary mr-2"></i> Upcoming Events</h4>
                <?php foreach ($events_list as $event): ?>
                <div class="border-b border-gray-200 py-3">
                    <p class="font-semibold"><?php echo htmlspecialchars($event['title']); ?></p>
                    <p class="text-sm text-gray-500"><?php echo formatDate($event['event_date']); ?> at <?php echo $event['event_time']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<footer class="gradient-header text-white py-8">
    <div class="container mx-auto px-4 text-center">
        <p>&copy; <?php echo date('Y'); ?> <?php echo SCHOOL_NAME; ?>. All rights reserved.</p>
        <p class="text-sm opacity-75 mt-2">In Affiliation with NUHU BAMALI POLYTECHNIC, ZARIA</p>
    </div>
</footer>

<!-- Auth Modal -->
<div id="authModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 id="authTitle" class="text-2xl font-bold text-primary">Student Login</h3>
            <button onclick="closeAuth()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        
        <!-- Login Form -->
        <div id="loginForm">
            <input type="email" id="loginEmail" placeholder="Email Address" class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-4 focus:outline-none focus:border-primary">
            <input type="password" id="loginPassword" placeholder="Password" class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-4 focus:outline-none focus:border-primary">
            <button onclick="handleLogin()" class="btn-primary w-full py-3 rounded-lg font-semibold">Login</button>
            <p class="text-center mt-4 text-gray-600">Don't have an account? <a onclick="showAuth('register')" class="text-primary cursor-pointer">Register</a></p>
        </div>
        
        <!-- Register Form -->
        <div id="registerForm" style="display: none;">
            <input type="text" id="regFullname" placeholder="Full Name" class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-3">
            <input type="email" id="regEmail" placeholder="Email Address" class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-3">
            <input type="text" id="regMatric" placeholder="Matric Number" class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-3">
            <input type="tel" id="regPhone" placeholder="Phone Number" class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-3">
            <input type="password" id="regPassword" placeholder="Password (min 6 characters)" class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-3">
            <input type="password" id="regConfirm" placeholder="Confirm Password" class="w-full border border-gray-300 rounded-lg px-4 py-3 mb-4">
            <button onclick="handleRegister()" class="btn-primary w-full py-3 rounded-lg font-semibold">Register</button>
            <p class="text-center mt-4 text-gray-600">Already have an account? <a onclick="showAuth('login')" class="text-primary cursor-pointer">Login</a></p>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ========== STUDENT PORTAL DASHBOARD ========== -->
<div class="flex min-h-screen">
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar bg-white shadow-xl w-72 fixed h-full overflow-y-auto z-30">
        <div class="gradient-header p-5">
            <div class="flex items-center gap-3">
                <i class="fas fa-graduation-cap text-3xl text-white"></i>
                <div>
                    <h2 class="text-white font-bold text-lg"><?php echo SCHOOL_SHORT; ?></h2>
                    <p class="text-white text-xs opacity-90">Student Portal</p>
                </div>
            </div>
        </div>
        
        <div class="p-4 text-center border-b">
            <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-2">
                <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 2)); ?>
            </div>
            <h3 class="font-bold"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></h3>
            <p class="text-sm text-gray-500"><?php echo $_SESSION['matric_no'] ?? 'Student'; ?></p>
        </div>
        
        <nav class="p-4">
            <a onclick="showPage('dashboard')" class="nav-item flex items-center gap-3 p-3 rounded-lg hover:bg-green-50 cursor-pointer transition" data-page="dashboard">
                <i class="fas fa-tachometer-alt w-5 text-primary"></i> Dashboard
            </a>
            <a onclick="showPage('courses')" class="nav-item flex items-center gap-3 p-3 rounded-lg hover:bg-green-50 cursor-pointer transition" data-page="courses">
                <i class="fas fa-book w-5 text-primary"></i> My Courses
            </a>
            <a onclick="showPage('grades')" class="nav-item flex items-center gap-3 p-3 rounded-lg hover:bg-green-50 cursor-pointer transition" data-page="grades">
                <i class="fas fa-chart-line w-5 text-primary"></i> Grades & CGPA
            </a>
            <a onclick="showPage('payments')" class="nav-item flex items-center gap-3 p-3 rounded-lg hover:bg-green-50 cursor-pointer transition" data-page="payments">
                <i class="fas fa-credit-card w-5 text-primary"></i> Payments
            </a>
            <a onclick="showPage('messages')" class="nav-item flex items-center gap-3 p-3 rounded-lg hover:bg-green-50 cursor-pointer transition" data-page="messages">
                <i class="fas fa-envelope w-5 text-primary"></i> Messages
                <?php 
                $unread = count(array_filter($messages_received, fn($m) => !$m['is_read']));
                if ($unread > 0): ?>
                <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5 ml-auto"><?php echo $unread; ?></span>
                <?php endif; ?>
            </a>
            <a onclick="showPage('profile')" class="nav-item flex items-center gap-3 p-3 rounded-lg hover:bg-green-50 cursor-pointer transition" data-page="profile">
                <i class="fas fa-user w-5 text-primary"></i> Profile
            </a>
            <a onclick="showPage('settings')" class="nav-item flex items-center gap-3 p-3 rounded-lg hover:bg-green-50 cursor-pointer transition" data-page="settings">
                <i class="fas fa-cog w-5 text-primary"></i> Settings
            </a>
            <hr class="my-4">
            <a href="?action=logout" class="flex items-center gap-3 p-3 rounded-lg hover:bg-red-50 text-red-500 cursor-pointer transition">
                <i class="fas fa-sign-out-alt w-5"></i> Logout
            </a>
        </nav>
    </div>
    
    <!-- Mobile Menu Button -->
    <button onclick="toggleSidebar()" class="fixed bottom-4 left-4 z-40 bg-primary text-white p-3 rounded-full shadow-lg md:hidden">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Main Content -->
    <div class="flex-1 md:ml-72">
        <!-- Header -->
        <header class="gradient-header text-white p-4 sticky top-0 z-20">
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-bold" id="pageTitle">Dashboard</h1>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <i class="fas fa-bell text-xl cursor-pointer" onclick="toggleNotifications()"></i>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="hidden md:inline"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></span>
                        <i class="fas fa-user-circle text-2xl"></i>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Notifications Dropdown -->
        <div id="notificationsDropdown" class="hidden fixed top-16 right-4 w-80 bg-white rounded-lg shadow-xl z-50">
            <div class="p-3 border-b font-bold">Notifications</div>
            <div class="max-h-96 overflow-y-auto">
                <?php foreach ($announcements_list as $ann): ?>
                <div class="p-3 border-b hover:bg-gray-50">
                    <p class="font-semibold text-sm"><?php echo htmlspecialchars($ann['title']); ?></p>
                    <p class="text-xs text-gray-500"><?php echo formatDate($ann['created_at']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Dashboard Pages -->
        <div class="p-6">
            <!-- Dashboard Home -->
            <div id="dashboard-page" class="page-content">
                <div class="grid md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl p-6 shadow-md card-hover">
                        <i class="fas fa-book-open text-3xl text-primary mb-3"></i>
                        <p class="text-gray-500">Enrolled Courses</p>
                        <p class="text-3xl font-bold"><?php echo count($student_courses); ?></p>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-md card-hover">
                        <i class="fas fa-chart-line text-3xl text-primary mb-3"></i>
                        <p class="text-gray-500">Current CGPA</p>
                        <p class="text-3xl font-bold"><?php echo number_format($cgpa, 2); ?></p>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-md card-hover">
                        <i class="fas fa-credit-card text-3xl text-primary mb-3"></i>
                        <p class="text-gray-500">Total Fees Paid</p>
                        <p class="text-3xl font-bold">₦<?php echo number_format(array_sum(array_column($student_payments, 'amount')), 2); ?></p>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-md card-hover">
                        <i class="fas fa-tasks text-3xl text-primary mb-3"></i>
                        <p class="text-gray-500">Credit Units</p>
                        <p class="text-3xl font-bold"><?php echo array_sum(array_column($student_courses, 'credit_unit')); ?></p>
                    </div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="font-bold text-lg mb-4 flex items-center"><i class="fas fa-bullhorn text-primary mr-2"></i> Announcements</h3>
                        <?php foreach (array_slice($announcements_list, 0, 5) as $ann): ?>
                        <div class="border-b py-3">
                            <p class="font-semibold"><?php echo htmlspecialchars($ann['title']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo substr(htmlspecialchars($ann['content']), 0, 100); ?>...</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="font-bold text-lg mb-4 flex items-center"><i class="fas fa-calendar-alt text-primary mr-2"></i> Upcoming Events</h3>
                        <?php foreach ($events_list as $event): ?>
                        <div class="border-b py-3">
                            <p class="font-semibold"><?php echo htmlspecialchars($event['title']); ?></p>
                            <p class="text-sm text-gray-500"><?php echo formatDate($event['event_date']); ?> | <?php echo $event['location']; ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- My Courses Page -->
            <div id="courses-page" class="page-content hidden">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-6">My Registered Courses</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="p-3 text-left">Course Code</th>
                                    <th class="p-3 text-left">Course Title</th>
                                    <th class="p-3 text-center">Credit Unit</th>
                                    <th class="p-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($student_courses as $course): ?>
                                <tr class="border-b">
                                    <td class="p-3 font-semibold"><?php echo htmlspecialchars($course['course_code']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($course['course_title']); ?></td>
                                    <td class="p-3 text-center"><?php echo $course['credit_unit']; ?></td>
                                    <td class="p-3 text-center"><span class="bg-green-100 text-green-600 px-2 py-1 rounded-full text-sm">Enrolled</span></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($student_courses)): ?>
                                <tr><td colspan="4" class="p-6 text-center text-gray-500">No courses enrolled yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Grades Page -->
            <div id="grades-page" class="page-content hidden">
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Academic Records</h2>
                        <div class="bg-primary text-white p-4 rounded-xl text-center">
                            <p class="text-sm">Cumulative GPA</p>
                            <p class="text-3xl font-bold"><?php echo number_format($cgpa, 2); ?></p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="p-3 text-left">Course Code</th>
                                    <th class="p-3 text-left">Course Title</th>
                                    <th class="p-3 text-center">Credit Unit</th>
                                    <th class="p-3 text-center">Test</th>
                                    <th class="p-3 text-center">Exam</th>
                                    <th class="p-3 text-center">Total</th>
                                    <th class="p-3 text-center">Grade</th>
                                    <th class="p-3 text-center">GP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $semester_points = 0;
                                $semester_credits = 0;
                                foreach ($student_grades as $grade): 
                                    $gpInfo = calculateGradePoint($grade['total_score']);
                                    $semester_points += $gpInfo[1] * $grade['credit_unit'];
                                    $semester_credits += $grade['credit_unit'];
                                ?>
                                <tr class="border-b">
                                    <td class="p-3 font-semibold"><?php echo htmlspecialchars($grade['course_code']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($grade['course_title']); ?></td>
                                    <td class="p-3 text-center"><?php echo $grade['credit_unit']; ?></td>
                                    <td class="p-3 text-center"><?php echo $grade['test_score']; ?></td>
                                    <td class="p-3 text-center"><?php echo $grade['exam_score']; ?></td>
                                    <td class="p-3 text-center font-semibold"><?php echo $grade['total_score']; ?></td>
                                    <td class="p-3 text-center"><span class="grade-<?php echo $gpInfo[0]; ?> px-3 py-1 rounded-full text-sm font-bold"><?php echo $gpInfo[0]; ?></span></td>
                                    <td class="p-3 text-center"><?php echo number_format($gpInfo[1], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($student_grades)): ?>
                                <tr><td colspan="8" class="p-6 text-center text-gray-500">No grades available yet.</td></tr>
                                <?php else: ?>
                                <tr class="bg-gray-50 font-bold">
                                    <td colspan="2" class="p-3">Semester Summary</td>
                                    <td class="p-3 text-center"><?php echo $semester_credits; ?></td>
                                    <td colspan="3"></td>
                                    <td class="p-3 text-center">Semester GPA</td>
                                    <td class="p-3 text-center"><?php echo $semester_credits > 0 ? number_format($semester_points / $semester_credits, 2) : '0.00'; ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Payments Page -->
            <div id="payments-page" class="page-content hidden">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-6">Payment History</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="p-3 text-left">Invoice No</th>
                                    <th class="p-3 text-left">Payment Type</th>
                                    <th class="p-3 text-center">Amount</th>
                                    <th class="p-3 text-center">Session</th>
                                    <th class="p-3 text-center">Status</th>
                                    <th class="p-3 text-center">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($student_payments as $payment): ?>
                                <tr class="border-b">
                                    <td class="p-3"><?php echo htmlspecialchars($payment['invoice_no']); ?></td>
                                    <td class="p-3 capitalize"><?php echo $payment['payment_type']; ?></td>
                                    <td class="p-3 text-center font-semibold">₦<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td class="p-3 text-center"><?php echo $payment['session']; ?></td>
                                    <td class="p-3 text-center">
                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $payment['status'] === 'completed' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600'; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-center"><?php echo formatDate($payment['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($student_payments)): ?>
                                <tr><td colspan="6" class="p-6 text-center text-gray-500">No payment records found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Messages Page -->
            <div id="messages-page" class="page-content hidden">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="font-bold text-lg mb-4 flex items-center"><i class="fas fa-inbox text-primary mr-2"></i> Received Messages</h3>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            <?php foreach ($messages_received as $msg): ?>
                            <div class="border rounded-lg p-3 <?php echo !$msg['is_read'] ? 'bg-green-50' : ''; ?>">
                                <div class="flex justify-between">
                                    <span class="font-semibold">From: <?php echo htmlspecialchars($msg['sender_name']); ?></span>
                                    <span class="text-xs text-gray-500"><?php echo formatDate($msg['created_at']); ?></span>
                                </div>
                                <p class="font-medium text-sm mt-1"><?php echo htmlspecialchars($msg['subject']); ?></p>
                                <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($msg['message']); ?></p>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($messages_received)): ?>
                            <p class="text-gray-500 text-center py-6">No messages received.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="font-bold text-lg mb-4 flex items-center"><i class="fas fa-paper-plane text-primary mr-2"></i> Send Message</h3>
                        <div class="space-y-4">
                            <input type="email" id="msgReceiver" placeholder="Recipient Email" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-primary">
                            <input type="text" id="msgSubject" placeholder="Subject" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-primary">
                            <textarea id="msgBody" rows="4" placeholder="Your message..." class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-primary"></textarea>
                            <button onclick="sendMessage()" class="btn-primary px-6 py-2 rounded-lg font-semibold">Send Message</button>
                        </div>
                        <hr class="my-6">
                        <h3 class="font-bold text-lg mb-4 flex items-center"><i class="fas fa-paper-plane text-primary mr-2"></i> Sent Messages</h3>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            <?php foreach ($messages_sent as $msg): ?>
                            <div class="border rounded-lg p-3">
                                <div class="flex justify-between">
                                    <span class="font-semibold">To: <?php echo htmlspecialchars($msg['receiver_name']); ?></span>
                                    <span class="text-xs text-gray-500"><?php echo formatDate($msg['created_at']); ?></span>
                                </div>
                                <p class="font-medium text-sm mt-1"><?php echo htmlspecialchars($msg['subject']); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Page -->
            <div id="profile-page" class="page-content hidden">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-6">My Profile</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="text-center">
                            <div class="w-32 h-32 bg-primary rounded-full flex items-center justify-center text-white text-4xl font-bold mx-auto mb-4">
                                <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 2)); ?>
                            </div>
                            <h3 class="font-bold text-xl"><?php echo htmlspecialchars($user_data['fullname'] ?? ''); ?></h3>
                            <p class="text-gray-500"><?php echo $user_data['matric_no'] ?? 'Not set'; ?></p>
                        </div>
                        <div>
                            <div class="mb-4">
                                <label class="block text-gray-600 mb-1">Full Name</label>
                                <input type="text" id="profileName" value="<?php echo htmlspecialchars($user_data['fullname'] ?? ''); ?>" class="w-full border rounded-lg px-4 py-2">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-600 mb-1">Email Address</label>
                                <input type="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-600 mb-1">Phone Number</label>
                                <input type="tel" id="profilePhone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" class="w-full border rounded-lg px-4 py-2">
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-600 mb-1">Address</label>
                                <textarea id="profileAddress" rows="3" class="w-full border rounded-lg px-4 py-2"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                            </div>
                            <button onclick="updateProfile()" class="btn-primary px-6 py-2 rounded-lg font-semibold">Update Profile</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Page -->
            <div id="settings-page" class="page-content hidden">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-6">Account Settings</h2>
                    <div class="max-w-md">
                        <div class="mb-6">
                            <label class="block text-gray-600 mb-1">Current Password</label>
                            <input type="password" id="currentPass" class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <div class="mb-6">
                            <label class="block text-gray-600 mb-1">New Password</label>
                            <input type="password" id="newPass" class="w-full border rounded-lg px-4 py-2">
                            <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
                        </div>
                        <div class="mb-6">
                            <label class="block text-gray-600 mb-1">Confirm New Password</label>
                            <input type="password" id="confirmPass" class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <button onclick="changePassword()" class="btn-primary px-6 py-2 rounded-lg font-semibold">Change Password</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container"></div>

<script>
    // ========== UI Helpers ==========
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i> ${message}`;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
    
    // ========== Auth Functions ==========
    function showAuth(type) {
        document.getElementById('authModal').classList.remove('hidden');
        if (type === 'login') {
            document.getElementById('authTitle').innerText = 'Student Login';
            document.getElementById('loginForm').style.display = 'block';
            document.getElementById('registerForm').style.display = 'none';
        } else {
            document.getElementById('authTitle').innerText = 'Student Registration';
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
        }
    }
    
    function closeAuth() {
        document.getElementById('authModal').classList.add('hidden');
    }
    
    async function handleLogin() {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        
        if (!email || !password) {
            showToast('Please enter email and password', 'error');
            return;
        }
        
        const response = await fetch('?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const data = await response.json();
        if (data.success) {
            showToast('Login successful! Redirecting...');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    }
    
    async function handleRegister() {
        const fullname = document.getElementById('regFullname').value;
        const email = document.getElementById('regEmail').value;
        const matric_no = document.getElementById('regMatric').value;
        const phone = document.getElementById('regPhone').value;
        const password = document.getElementById('regPassword').value;
        const confirm = document.getElementById('regConfirm').value;
        
        if (!fullname || !email || !matric_no || !password) {
            showToast('Please fill all required fields', 'error');
            return;
        }
        if (password !== confirm) {
            showToast('Passwords do not match', 'error');
            return;
        }
        if (password.length < 6) {
            showToast('Password must be at least 6 characters', 'error');
            return;
        }
        
        const response = await fetch('?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ fullname, email, password, confirm_password: confirm, phone, matric_no })
        });
        const data = await response.json();
        if (data.success) {
            showToast('Registration successful! Please login.');
            showAuth('login');
        } else {
            showToast(data.message, 'error');
        }
    }
    
    // ========== Dashboard Functions ==========
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }
    
    let notificationsVisible = false;
    function toggleNotifications() {
        const dropdown = document.getElementById('notificationsDropdown');
        notificationsVisible = !notificationsVisible;
        dropdown.classList.toggle('hidden', !notificationsVisible);
    }
    
    function showPage(page) {
        document.querySelectorAll('.page-content').forEach(p => p.classList.add('hidden'));
        document.getElementById(`${page}-page`).classList.remove('hidden');
        
        const titles = {
            dashboard: 'Dashboard',
            courses: 'My Courses',
            grades: 'Grades & CGPA',
            payments: 'Payments',
            messages: 'Messages',
            profile: 'My Profile',
            settings: 'Settings'
        };
        document.getElementById('pageTitle').innerText = titles[page] || 'Dashboard';
        
        if (window.innerWidth < 768) toggleSidebar();
    }
    
    async function updateProfile() {
        const fullname = document.getElementById('profileName').value;
        const phone = document.getElementById('profilePhone').value;
        const address = document.getElementById('profileAddress').value;
        
        const response = await fetch('?action=update_profile', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ fullname, phone, address })
        });
        const data = await response.json();
        if (data.success) {
            showToast(data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    }
    
    async function changePassword() {
        const current = document.getElementById('currentPass').value;
        const newPass = document.getElementById('newPass').value;
        const confirm = document.getElementById('confirmPass').value;
        
        if (!current || !newPass) {
            showToast('Please fill all fields', 'error');
            return;
        }
        if (newPass !== confirm) {
            showToast('New passwords do not match', 'error');
            return;
        }
        if (newPass.length < 6) {
            showToast('Password must be at least 6 characters', 'error');
            return;
        }
        
        const response = await fetch('?action=change_password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ current_password: current, new_password: newPass, confirm_password: confirm })
        });
        const data = await response.json();
        if (data.success) {
            showToast(data.message);
            document.getElementById('currentPass').value = '';
            document.getElementById('newPass').value = '';
            document.getElementById('confirmPass').value = '';
        } else {
            showToast(data.message, 'error');
        }
    }
    
    async function sendMessage() {
        const receiver_email = document.getElementById('msgReceiver').value;
        const subject = document.getElementById('msgSubject').value;
        const message = document.getElementById('msgBody').value;
        
        if (!receiver_email || !subject || !message) {
            showToast('Please fill all fields', 'error');
            return;
        }
        
        const response = await fetch('?action=send_message', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ receiver_email, subject, message })
        });
        const data = await response.json();
        if (data.success) {
            showToast(data.message);
            document.getElementById('msgReceiver').value = '';
            document.getElementById('msgSubject').value = '';
            document.getElementById('msgBody').value = '';
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message, 'error');
        }
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('notificationsDropdown');
        const bell = document.querySelector('.fa-bell');
        if (dropdown && bell && !bell.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
            notificationsVisible = false;
        }
    });
    
    // Close sidebar on window resize if needed
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            document.getElementById('sidebar').classList.remove('active');
        }
    });
</script>

</body>
</html>
