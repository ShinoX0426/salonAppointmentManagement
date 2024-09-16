<?php
include_once 'php/php.scripts/db.class.php';
include_once 'php/php.scripts/user.class.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$message = '';

// Start session and generate CSRF token
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the form has been submitted with a CSRF token
    if (!isset($_POST['csrf_token'])) {
        $message = "<div style='color: red;'>Error: CSRF token missing. Please try again.</div>";
    } else {
        // Verify CSRF token
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $message = "<div style='color: red;'>Error: CSRF token validation failed. Please try again.</div>";
        } else {
            // CSRF validation passed, process the form
            $user->Username = $_POST['username'];
            $user->PasswordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $user->Email = $_POST['email'];
            $user->UserType = $_POST['usertype'];
            $user->IsActive = $_POST['isactive'];

            if ($user->add()) {
                $message = "<div style='color: green;'>User created successfully.</div>";
            } else {
                $message = "<div style='color: red;'>Unable to create user.</div>";
            }
        }
    }
}

// Fetch existing users
$users = $user->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        form {
            display: grid;
            gap: 10px;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 5px;
            box-sizing: border-box;
        }

        button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>User Management</h1>

    <?php echo $message; ?>

    <h2>Add New User</h2>
    <form action="" method="post" onsubmit="return validateForm()">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="usertype">User Type:</label>
        <select id="usertype" name="usertype" required>
            <option value="Customer">Customer</option>
            <option value="Admin">Admin</option>
            <option value="Staff">Staff</option>
        </select>

        <label for="isactive">Is Active:</label>
        <select id="isactive" name="isactive" required>
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>

        <button type="submit">Add User</button>
    </form>

    <h2>Existing Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>User Type</th>
            <th>Is Active</th>
        </tr>
        <?php while ($row = $users->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['UserID']); ?></td>
                <td><?php echo htmlspecialchars($row['Username']); ?></td>
                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                <td><?php echo htmlspecialchars($row['UserType']); ?></td>
                <td><?php echo $row['IsActive'] ? 'Yes' : 'No'; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <script>
        function validateForm() {
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;
            var email = document.getElementById('email').value;

            if (username.length < 3) {
                alert('Username must be at least 3 characters long');
                return false;
            }

            if (password.length < 8) {
                alert('Password must be at least 8 characters long');
                return false;
            }

            if (!/\S+@\S+\.\S+/.test(email)) {
                alert('Please enter a valid email address');
                return false;
            }

            return true;
        }
    </script>
</body>

</html>