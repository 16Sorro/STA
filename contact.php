<?php
// Connexion à la base de données MySQL
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
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit;
}

// Traitement du formulaire
$message_confirmation = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    if (!empty($nom) && !empty($email) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (nom, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $email, $message]);
        $message_confirmation = "✅ Votre message a bien été envoyé. Merci !";
    } else {
        $message_confirmation = "❌ Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contact - Tripadvisor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 2rem;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        form {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
        }
        input, textarea {
            width: 100%;
            margin-bottom: 1rem;
            padding: 0.8rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        .message {
            text-align: center;
            margin-bottom: 1rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Contactez-nous</h1>

<?php if ($message_confirmation): ?>
    <div class="message"><?= $message_confirmation ?></div>
<?php endif; ?>

<form method="post" action="contact.php">
    <label for="nom">Nom :</label>
    <input type="text" name="nom" id="nom" required>

    <label for="email">Email :</label>
    <input type="email" name="email" id="email" required>

    <label for="message">Message :</label>
    <textarea name="message" id="message" rows="6" required></textarea>

    <button type="submit">Envoyer</button>
</form>

</body>
</html>
