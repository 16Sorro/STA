<?php
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

// Récupère tous les restaurants avec leur origine
$sql = "
    SELECT r.id_restaurant, r.nom AS nom_restaurant, o.pays,
           GROUP_CONCAT(t.intitule SEPARATOR ', ') AS types
    FROM restaurants r
    JOIN origines o ON r.id_origine = o.id_origine
    LEFT JOIN types_restaurants tr ON r.id_restaurant = tr.id_restaurant
    LEFT JOIN types t ON tr.id_type = t.id_type
    GROUP BY r.id_restaurant
    ORDER BY r.nom ASC
";

$restaurants = $pdo->query($sql)->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Restaurants</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 2rem; }
        .restaurant { background: white; margin-bottom: 2rem; padding: 1rem 2rem; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; }
        .avis { margin-top: 1rem; padding-left: 1rem; border-left: 3px solid #2196F3; }
        .types { font-style: italic; color: gray; }
    </style>
</head>
<body>

<h1>🍽️ Liste des Restaurants</h1>

<?php foreach ($restaurants as $resto): ?>
    <div class="restaurant">
        <h2><?= htmlspecialchars($resto['nom_restaurant']) ?></h2>
        <p><strong>Pays :</strong> <?= htmlspecialchars($resto['pays']) ?></p>
        <p class="types"><strong>Types :</strong> <?= htmlspecialchars($resto['types']) ?></p>

        <?php
        // Avis pour ce restaurant
        $stmt = $pdo->prepare("
            SELECT a.commentaire, a.date, c.prenom, c.nom
            FROM avis a
            JOIN clients c ON a.id_client = c.id_client
            WHERE a.id_restaurant = ?
            ORDER BY a.date DESC
            LIMIT 3
        ");
        $stmt->execute([$resto['id_restaurant']]);
        $avis = $stmt->fetchAll();
        ?>

        <?php if ($avis): ?>
            <div class="avis">
                <h4>Derniers avis :</h4>
                <ul>
                    <?php foreach ($avis as $a): ?>
                        <li>
                            <strong><?= htmlspecialchars($a['prenom']) ?> <?= htmlspecialchars($a['nom']) ?>:</strong>
                            <?= htmlspecialchars($a['commentaire']) ?>
                            <em>(<?= htmlspecialchars($a['date']) ?>)</em>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <p>Aucun avis disponible.</p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

</body>
</html>
