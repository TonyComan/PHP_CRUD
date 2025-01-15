<?php
session_start();
include('db.php');


if (isset($_SESSION['user_name'])) {
    header('Location: todo.php'); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

 
    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray();

    if ($user && password_verify($password, $user['password'])) {
       
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
      
        header('Location: todo.php');
        exit;
    } else {
        echo "Email sau parola incorecte!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Autentificare</title>
    <link rel="php" href="todo.php">
</head>
<body>
    <h1>Autentificare</h1>
    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Parolă:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Autentificare</button>
        <a href="todo.php"> autentificare
    </form>
</body>
</html>
