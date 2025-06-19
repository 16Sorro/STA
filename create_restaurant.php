<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO('mysql:host=10.96.16.82;dbname=tripadvisor;charset=utf8', 'colin', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['ajouter'])) {
        $nom = $_POST['nom'] ?? '';
        $id_origine = $_POST['id_origine'] ?? '';

        if (!empty($nom) && is_numeric($id_origine)) {
            $stmt = $pdo->prepare("INSERT INTO restaurants (nom, id_origine) VALUES (:nom, :id_origine)");
            $stmt->execute([
                ':nom' => $nom,
                ':id_origine' => $id_origine
            ]);
            $message = "✅ Restaurant ajouté !";
        } else {
            $message = "❗ Remplis tous les champs.";
        }
    } elseif (isset($_POST['supprimer'])) {
        $id_restaurant = $_POST['id_restaurant'] ?? '';
        if (is_numeric($id_restaurant)) {
            $stmt = $pdo->prepare("DELETE FROM restaurants WHERE id_restaurant = :id");
            $stmt->execute([':id' => $id_restaurant]);
            $message = "🗑️ Restaurant supprimé.";
        }
    }
}

$origines = $pdo->query("SELECT id_origine, pays FROM origines ORDER BY pays")->fetchAll();

$restaurants = $pdo->query("
    SELECT r.id_restaurant, r.nom, o.pays 
    FROM restaurants r
    LEFT JOIN origines o ON r.id_origine = o.id_origine
    ORDER BY r.nom
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Restaurants</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            padding: 20px;
            color: #333;
        }

        .container {
            background: #fff;
            padding: 30px;
            max-width: 800px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }

        form {
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #3498db;
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f0f0f0;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🍽 Ajouter un restaurant</h1>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, '✅') !== false || strpos($message, '🗑️') !== false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="ajouter" value="1">

        <label for="nom">Nom du restaurant :</label>
        <input type="text" id="nom" name="nom" required>

        <label for="id_origine">Origine :</label>
        <select id="id_origine" name="id_origine" required>
            <option value="">-- Choisissez un pays --</option>
            <?php foreach ($origines as $origine): ?>
                <option value="<?= $origine['id_origine'] ?>">
                    <?= htmlspecialchars($origine['pays']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Ajouter</button>
    </form>

    <h2>📋 Restaurants existants</h2>
    <?php if ($restaurants): ?>
        <table>
            <thead>
            <tr>
                <th>Nom</th>
                <th>Origine</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($restaurants as $resto): ?>
                <tr>
                    <td><?= htmlspecialchars($resto['nom']) ?></td>
                    <td><?= htmlspecialchars($resto['pays'] ?? 'Inconnu') ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Supprimer ce restaurant ?');" style="display:inline;">
                            <input type="hidden" name="supprimer" value="1" />
                            <input type="hidden" name="id_restaurant" value="<?= $resto['id_restaurant'] ?>" />
                            <button type="submit" class="delete-btn">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun restaurant pour l’instant.</p>
    <?php endif; ?>
</div>
</body>
</html>
