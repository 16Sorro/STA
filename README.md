<?php
session_start();

$host = '10.96.16.82';
$db   = 'tripadvisor';
$user = 'colin';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}

$message = '';

if (isset($_POST['login'])) {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, nom FROM mail WHERE mail = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: contact.php');
        exit;
    } else {
        $message = "❌ Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 2rem; }
        .container { max-width: 400px; margin: auto; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        input, button { width: 100%; padding: 0.8rem; margin-bottom: 1rem; font-size: 1rem; }
        button { background-color: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .message { color: red; text-align: center; margin-bottom: 1rem; }
        a { display: block; text-align: center; margin-top: 1rem; }
    </style>
</head>
<body>

<div class="container">
    <h2>Connexion</h2>
    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit" name="login">Se connecter</button>
    </form>
    <a href="register.php">Pas encore de compte ? Inscrivez-vous</a>
</div>

</body>
</html>
