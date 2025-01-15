<?php
session_start();


if (!isset($_SESSION['user_name'])) {
    header('Location: login.php'); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task'])) {
    
    $_SESSION['tasks'][] = $_POST['task'];
}


$tasks = isset($_SESSION['tasks']) ? $_SESSION['tasks'] : [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista To-Do</title>
</head>
<body>
    <h1>Lista To-Do</h1>
    <p>Bine ai venit, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
    <a href="logout.php">Deconectare</a>
    
    <h2>Adaugă un task</h2>
    <form method="POST">
        <input type="text" name="task" required>
        <button type="submit">Adaugă Task</button>
    </form>
    
    <h3>Task-uri:</h3>
    <ul>
        <?php foreach ($tasks as $task) : ?>
            <li><?php echo htmlspecialchars($task); ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
