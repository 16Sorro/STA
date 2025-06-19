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

// Récupère l'ID depuis l'URL
$id_restaurant = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Requête pour un seul restaurant (celui de l'ID)
$sql = "
   SELECT r.id_restaurant AS id,
       r.nom AS nom_restaurant, 
       o.pays,
       GROUP_CONCAT(t.intitule SEPARATOR ', ') AS types
FROM restaurants r
JOIN origines o ON r.id_origine = o.id_origine
LEFT JOIN types_restaurants tr ON r.id_restaurant = tr.id_restaurant
LEFT JOIN types t ON tr.id_type = t.id_type
WHERE r.id_restaurant = :id_restaurant
GROUP BY r.id_restaurant
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id_restaurant' => $id_restaurant]);
$restaurant = $stmt->fetch();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= isset($restaurant) ? htmlspecialchars($restaurant['nom_restaurant']) : 'Restaurant' ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f7f7; padding: 2rem; }
        .restaurant { background: white; margin-bottom: 2rem; padding: 1rem 2rem; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; }
        .avis { margin-top: 1rem; padding-left: 1rem; border-left: 3px solid #2196F3; }
        .types { font-style: italic; color: gray; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #2196F3; text-decoration: none; }
    </style>
</head>
<body>

<a href="recherche.php" class="back-link">← Retour à la liste</a>

<?php if ($restaurant): ?>
    <div class="restaurant">
        <h2><?= htmlspecialchars($restaurant['nom_restaurant']) ?></h2>
        <p><strong>Pays :</strong> <?= htmlspecialchars($restaurant['pays']) ?></p>
        <p class="types"><strong>Types :</strong> <?= htmlspecialchars($restaurant['types']) ?></p>

        <?php
        // Avis pour ce restaurant
        $stmt = $pdo->prepare("
            SELECT a.commentaire, a.date, c.prenom, c.nom
            FROM avis a
            JOIN clients c ON a.id_client = c.id_client
            WHERE a.id_restaurant = ?
            ORDER BY a.date DESC
        ");
        $stmt->execute([$restaurant['id']]);
        $avis = $stmt->fetchAll();
        ?>

        <?php if ($avis): ?>
            <div class="avis">
                <h4>Avis :</h4>
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
<?php else: ?>
    <div class="restaurant">
        <h2>Restaurant introuvable</h2>
        <p>Aucun restaurant ne correspond à cet identifiant.</p>
    </div>
<?php endif; ?>

</body>
</html>