<?php
session_start();

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

// Traitement du formulaire
if (isset($_POST['register'])) {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);

    // Vérifie si l'e-mail est déjà utilisé
    $stmt = $pdo->prepare("SELECT id_client FROM clients WHERE mail = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $message = "❌ Cet email est déjà utilisé.";
    } else {
        // Insertion du nouvel utilisateur
        $stmt = $pdo->prepare("INSERT INTO clients (nom, prenom, mail) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email]);

        // Récupération de l'utilisateur pour la session
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE mail = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Connexion automatique
        $_SESSION['user'] = [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'mail' => $user['mail']
        ];

        // Redirection
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 2rem; }
        .container { max-width: 400px; margin: auto; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        input, button { width: 100%; padding: 0.8rem; margin-bottom: 1rem; font-size: 1rem; }
        button { background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .message { text-align: center; margin-bottom: 1rem; font-weight: bold; color: red; }
        a { display: block; text-align: center; margin-top: 1rem; color: #333; }
    </style>
</head>
<body>

<div class="container">
    <h2>Créer un compte</h2>
    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="email" name="email" placeholder="Adresse e-mail" required>
        <button type="submit" name="register">S'inscrire</button>
    </form>
    <a href="login.php">Déjà un compte ? Connectez-vous</a>
</div>

</body>
</html>
