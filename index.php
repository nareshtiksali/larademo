<?php
 echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet' integrity='sha384-...' crossorigin='anonymous'>";
function renderForm($name = '', $email = '', $remember = false) {
    // include Bootstrap via CDN
   
    echo "<div class='container mt-5'>";
    echo "<div class='card mx-auto' style='max-width: 400px;'>";
    echo "<div class='card-body'>";
    echo "<h1 class='card-title text-center mb-4'>Please submit your information</h1>";
    echo "<form method='POST' action='' onsubmit=\"return validateForm();\">";
    echo "<div class='mb-3'><label for='name' class='form-label'>Name</label><input type='text' class='form-control' id='name' name='name' value='" . htmlspecialchars($name) . "' required></div>";
    echo "<div class='mb-3'><label for='email' class='form-label'>Email</label><input type='email' class='form-control' id='email' name='email' value='" . htmlspecialchars($email) . "' required></div>";
    echo "<div class='mb-3 form-check'><input type='checkbox' class='form-check-input' id='remember' name='remember'" . ($remember ? " checked" : "") . "><label class='form-check-label' for='remember'>Remember password</label></div>";
    echo "<button type='submit' class='btn btn-primary w-100'>Submit</button>";
    echo "</form>";
    echo "</div></div></div>";
    echo "<hr>";
    // after the form, show list of users
    displayUsers();
    // client-side script
    echo "<script>\nfunction validateForm() {\n    var n = document.getElementById('name').value.trim();\n    var e = document.getElementById('email').value.trim();\n    if (!n) { alert('Name is required'); return false; }\n    var re = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;\n    if (!re.test(e)) { alert('Enter a valid email'); return false; }\n    return true;\n}\n</script>";
}

// show list of existing users from database
function displayUsers() {
    $dbHost = 'localhost';
    $dbName = 'larademo';
    $dbUser = 'root';
    $dbPass = '';
    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $stmt = $pdo->query('SELECT id, name, email, remember, created_at FROM users ORDER BY id DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            echo "<h2 class='mt-4'>Registered Users</h2>";
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Remember</th><th>Created</th></tr></thead>";
            echo "<tbody>";
            foreach ($rows as $row) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . ($row['remember'] ? 'Yes' : 'No') . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p class='mt-4'><em>No users found.</em></p>";
        }
    } catch (PDOException $e) {
        echo "<p class='text-danger'>Could not retrieve users: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $remember = isset($_POST['remember']);
    $errors = [];
    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if (!empty($errors)) {
        echo "<h2>Validation errors:</h2>";
        echo "<ul>";
        foreach ($errors as $err) {
            echo "<li>" . htmlspecialchars($err) . "</li>";
        }
        echo "</ul>";
        renderForm($name, $email, $remember);
    } else {
        // attempt to save into database
        $dbHost = 'localhost';
        $dbName = 'larademo';        // change to your database name
        $dbUser = 'root';
        $dbPass = '';
        try {
            $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, remember, created_at) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $remember ? 1 : 0, date('Y-m-d H:i:s')]  );
            echo "<p class='text-success'>Data saved to database successfully.</p>";
        } catch (PDOException $e) {
            echo "<p class='text-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        echo "<h1>POST Data Dump</h1>";
        echo "<table border='1'>";
        echo "<tr><th>Key</th><th>Value</th></tr>";
        foreach ($_POST as $key => $value) {
            echo "<tr><td>" . htmlspecialchars($key) . "</td><td>" . htmlspecialchars($value) . "</td></tr>";
        }
        echo "</table>";
        // show current users below dump
        displayUsers();
    }
} else {
    renderForm();
}
?>
