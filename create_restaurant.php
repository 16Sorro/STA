<?php
// Activer les erreurs (dev uniquement)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=10.96.16.82;dbname=tripadvisor;charset=utf8', 'colin', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement du formulaire
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST['nom'] ?? '';
    $id_origine = $_POST['id_origine'] ?? '';

    if (!empty($nom) && is_numeric($id_origine)) {
        $stmt = $pdo->prepare("INSERT INTO restaurants (nom, id_origine) VALUES (:nom, :id_origine)");
        $stmt->execute([
            ':nom' => $nom,
            ':id_origine' => $id_origine
        ]);
        $message = "✅ Restaurant ajouté avec succès !";
    } else {
        $message = "❗ Veuillez remplir tous les champs.";
    }
}

// Récupération des origines pour le menu déroulant
$origines = $pdo->query("SELECT id_origine, pays FROM origines ORDER BY pays")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un restaurant</title>
</head>
<body>
    <h1>Ajouter un restaurant</h1>

    <?php if ($message): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nom du restaurant :<br>
            <input type="text" name="nom" required>
        </label><br><br>

        <label>Origine :<br>
            <select name="id_origine" required>
                <option value="">-- Choisissez un pays --</option>
                <?php foreach ($origines as $origine): ?>
                    <option value="<?= $origine['id_origine'] ?>">
                        <?= htmlspecialchars($origine['pays']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <button type="submit">Ajouter</button>
    </form>
</body>
</html>
