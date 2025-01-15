<?php
session_start();
include('db.php');  


if (isset($_SESSION['user_name'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT); 

    
    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    if ($result->fetchArray()) {
        die('Email-ul este deja folosit!');
    }

    
    $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);
    $stmt->execute();

    echo "Înregistrare reușită! Te poți autentifica acum.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Înregistrare</title>
</head>
<body>
    <h1>Înregistrare</h1>
    <form method="POST">
        <label>Nume:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Parolă:</label><br>
        <input type="password" name="password" required minlength="6"><br><br>

        <button type="submit">Înregistrează-te</button>
    </form>
</body>
</html>
