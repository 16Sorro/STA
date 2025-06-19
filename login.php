<?php
session_start();

// Déconnexion si ?logout est présent
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Connexion à la base de données
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

// Traitement du formulaire de connexion
if (isset($_POST['login'])) {
    $email = htmlspecialchars($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE mail = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'mail' => $user['mail']
        ];
        header("Location: login.php");
        exit;
    } else {
        $message = "❌ Aucune inscription trouvée avec cet e-mail.";
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
        .message { text-align: center; margin-bottom: 1rem; font-weight: bold; color: red; }
        a { display: block; text-align: center; margin-top: 1rem; color: #333; }
    </style>
</head>
<body>

<div class="container">
    <?php if (isset($_SESSION['user'])): ?>
        <h2>Bienvenue, <?= htmlspecialchars($_SESSION['user']['prenom']) ?> <?= htmlspecialchars($_SESSION['user']['nom']) ?> !</h2>
        <p>Email : <?= htmlspecialchars($_SESSION['user']['mail']) ?></p>
        <a href="?logout">Se déconnecter</a>
    <?php else: ?>
        <h2>Connexion</h2>
        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Adresse e-mail" required>
            <button type="submit" name="login">Se connecter</button>
        </form>
        <a href="register.php">Pas encore inscrit ? Crée un compte</a>
    <?php endif; ?>
</div>

</body>
</html>
