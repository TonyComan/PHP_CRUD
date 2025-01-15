<?php
session_start();
$db = new SQLite3('todo.db');

// Se creaza tabelele daca nu exista
$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT, email TEXT UNIQUE, password TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS tasks (id INTEGER PRIMARY KEY, user_id INTEGER, task TEXT, FOREIGN KEY(user_id) REFERENCES users(id))");

// Functia pentru redirectionare
function redirect($url) {
    header("Location: $url");
    exit;
}

// Gestiionam inregistrarea
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Email invalid!";
        exit;
    }

    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password)) {
        echo "Parola trebuie sa aiba cel putin 8 caractere , o litera mare si un numar!";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);

    try {
        $stmt->execute();
        echo "Te-ai inregistrat cu succes! Te poti autentifica!";
    } catch (Exception $e) {
        echo "Eroare: " . $e->getMessage();
    }
}

// Gestionam autentificarea
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        redirect('index.php');
    } else {
        echo "Email sau parola incorecte!";
    }
}

//  Deconectarea
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('index.php');
}

//  Adaugarea de sarcini
if (isset($_POST['add_task']) && isset($_SESSION['user_id'])) {
    $task = trim($_POST['task']);

    $stmt = $db->prepare("INSERT INTO tasks (user_id, task) VALUES (:user_id, :task)");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $stmt->bindValue(':task', $task, SQLITE3_TEXT);
    $stmt->execute();
}

// Gestionam stergerea sarcinilor
if (isset($_GET['delete_task']) && isset($_SESSION['user_id'])) {
    $task_id = (int)$_GET['delete_task'];

    $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
    $stmt->bindValue(':id', $task_id, SQLITE3_INTEGER);
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $stmt->execute();
}

// Preluam sarcinile utilizatorului curent
$tasks = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("SELECT * FROM tasks WHERE user_id = :user_id");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $result = $stmt->execute();

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $tasks[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>To-Do List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background: #fff;
        }
        h1, h2 {
            text-align: center;
            color: #444;
        }
        form {
            margin: 20px 0;
            display: flex;
            flex-direction: column;
        }
        form input, form button {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            background-color: #5cb85c;
            color: white;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background-color: #4cae4c;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            background: #f9f9f9;
            margin: 5px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
        }
        ul li a {
            color: #d9534f;
            text-decoration: none;
        }
        ul li a:hover {
            text-decoration: underline;
        }
        .logout {
            text-align: center;
        }
        .logout a {
            color: #d9534f;
        }
    </style>
</head>
<body>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <h1>Autentificare</h1>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Parola" required>
            <button type="submit" name="login">Autentificare</button>
        </form>

        <hr>

        <h1>Inregistrare</h1>
        <form method="POST">
            <input type="text" name="name" placeholder="Nume" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Parola" required>
            <button type="submit" name="register">Inregistrare</button>
        </form>
    <?php else: ?>
        <h1>Lista To-Do</h1>
        <p>Bine ai venit, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! <span class="logout"><a href="?logout=true">Deconectare</a></span></p>

        <h2>Adauga o sarcina</h2>
        <form method="POST">
            <input type="text" name="task" placeholder="Introduceti sarcina" required>
            <button type="submit" name="add_task">Adauga</button>
        </form>

        <h2>Sarcinile tale</h2>
        <ul>
            <?php foreach ($tasks as $task): ?>
                <li>
                    <?php echo htmlspecialchars($task['task']); ?>
                    <a href="?delete_task=<?php echo $task['id']; ?>">Sterge</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
