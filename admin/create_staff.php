<?php
session_start();
require_once '../config/connection.php';

// Simple admin session check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.html');
    exit;
}

$errors = [];
$successMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $position = trim($_POST['position'] ?? '');
    $status = ($_POST['status'] ?? 'inactive') === 'active' ? 1 : 0;

    // Basic validation
    if ($fullName === '') { $errors[] = 'Full Name is required.'; }
    if ($username === '') { $errors[] = 'Username is required.'; }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'A valid Email Address is required.'; }
    if (strlen($password) < 6) { $errors[] = 'Password must be at least 6 characters.'; }
    if ($password !== $confirmPassword) { $errors[] = 'Passwords do not match.'; }

    if (empty($errors)) {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            // Ensure position column exists (add if missing) - safe, idempotent check
            $colCheck = $conn->prepare("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'admin_users' AND COLUMN_NAME = 'position'");
            $colCheck->bindValue(':db', DB_NAME);
            $colCheck->execute();
            $colExists = $colCheck->fetchColumn();
            if (!$colExists) {
                $conn->exec("ALTER TABLE admin_users ADD COLUMN position VARCHAR(100) DEFAULT NULL AFTER full_name");
            }

            // Check username/email uniqueness in admin_users
            $stmt = $conn->prepare("SELECT admin_id FROM admin_users WHERE username = :username OR email = :email LIMIT 1");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $errors[] = 'Username or email already exists for an admin/staff account.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $insertSql = "INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active, position, created_by) VALUES (:username, :email, :password_hash, :full_name, 'staff', :is_active, :position, :created_by)";
                $ins = $conn->prepare($insertSql);
                $ins->bindParam(':username', $username);
                $ins->bindParam(':email', $email);
                $ins->bindParam(':password_hash', $passwordHash);
                $ins->bindParam(':full_name', $fullName);
                $ins->bindParam(':is_active', $status);
                $ins->bindParam(':position', $position);
                $createdBy = $_SESSION['admin_id'] ?? null;
                $ins->bindParam(':created_by', $createdBy);
                $ins->execute();

                // Prepare redirect with flash message to avoid form resubmission
                $_SESSION['flash_success'] = '✅ Staff account created successfully! The new staff member can now log in using their credentials. You can manage or update their access anytime in Manage Staff Members.';
                // Optionally include created username
                $_SESSION['flash_staff_username'] = $username;
                // Redirect back to dashboard staff section
                header('Location: dashboard.php#staff');
                exit;
            }

            $db->closeConnection();

        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Staff Account - Admin</title>
        <link rel="stylesheet" href="../admin-styles.css">
        <style>
            /* Small overrides to match dashboard spacing */
            body { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); }
            .form-card { margin-top: 40px; }
            label { font-weight: 600; color: #334155; display:block; margin-bottom:6px }
            input[type="text"], input[type="email"], input[type="password"], select {
                width: 100%;
                padding: 0.85rem 1rem;
                border-radius: 10px;
                border: 1px solid rgba(102,126,234,0.12);
                background: rgba(255,255,255,0.98);
                font-size: 0.95rem;
            }
            .help-text { color: #556; font-size: 0.95rem; margin-bottom: 12px }
        </style>
</head>
<body>

        <div class="form-card">
            <h2>Create New Staff Account</h2>
            <p class="help-text">Fill out the form below to register a new staff member. Staff accounts have limited access and cannot manage other staff accounts.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>

            <form method="post" id="createStaffForm" novalidate>
                <div class="form-grid">
                    <div class="full">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required placeholder="Enter full name">
                    </div>

                    <div>
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required placeholder="Unique username">
                    </div>

                    <div>
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="staff@example.com">
                    </div>

                    <div>
                        <label for="phone">Contact Number (optional)</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="e.g. 09171234567">
                    </div>

                    <div>
                        <label for="position">Role / Position</label>
                        <select id="position" name="position">
                            <?php
                                $positions = ['Receptionist','Reservation Assistant','Front Desk Officer','Housekeeping Supervisor','Concierge'];
                                $sel = $_POST['position'] ?? '';
                                foreach ($positions as $p) {
                                        $s = ($sel === $p) ? 'selected' : '';
                                        echo "<option value=\"" . htmlspecialchars($p) . "\" $s>" . htmlspecialchars($p) . "</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Temporary password (staff can change later)">
                    </div>

                    <div>
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter password">
                    </div>

                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active" <?php echo (($_POST['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (($_POST['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="actions">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">❌ Cancel</button>
                    <button type="submit" class="btn btn-primary">✅ Create Account</button>
                </div>
            </form>
        </div>

<script>
document.getElementById('createStaffForm').addEventListener('submit', function(e){
    var pw = document.getElementById('password').value;
    var cpw = document.getElementById('confirm_password').value;
    if (pw.length < 6){
        alert('Password must be at least 6 characters.');
        e.preventDefault();
        return;
    }
    if (pw !== cpw){
        alert('Passwords do not match.');
        e.preventDefault();
    }
});
</script>

</body>
</html>
