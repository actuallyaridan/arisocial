<?php
try {
    $db = new PDO('sqlite:../chirp.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate inputs
        $code = $_POST['code'];
        $name = $_POST['name'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['pword'];
        $passwordConfirm = $_POST['pwordConfirm'];

        // Fetch invite details including reservedFor
        $stmt = $db->prepare("SELECT id, reservedFor FROM invites WHERE invite = :code");
        $stmt->execute(['code' => $code]);
        $invite = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if invite code exists
        if (!$invite) {
            echo json_encode(['error' => 'Invalid invite code']);
            exit;
        }

        // Check if the invite is reserved for a specific username
        if ($invite['reservedFor'] !== null && $invite['reservedFor'] !== $username) {
            echo json_encode(['error' => 'Invite not reserved for this username']);
            exit;
        }

        // Check if username is in the reservedFor column for any invite
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM invites WHERE reservedFor = :username");
        $stmt->execute(['username' => $username]);
        $reservedForCount = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reservedForCount['count'] > 0 && $invite['reservedFor'] !== $username) {
            echo json_encode(['error' => 'This username is reserved.']);
            exit;
        }

        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode(['error' => 'Username already in use']);
            exit;
        }

        // Validate password match
        if ($password !== $passwordConfirm) {
            echo json_encode(['error' => 'Passwords do not match']);
            exit;
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert into users table
        $stmt = $db->prepare("INSERT INTO users (username, name, email, password_hash, usedInvite) VALUES (:username, :name, :email, :password, :usedInvite)");
        $stmt->execute(['username' => $username, 'name' => $name, 'email' => $email, 'password' => $passwordHash, 'usedInvite' => $code]);

        echo json_encode(['success' => true]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>