<?php
// Connexion BDD (identique à index.php)
$host = '10.96.16.82';
$db   = 'tripadvisor';
$user = 'colin';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit;
}

// Récupération des restaurants avec leurs avis
$sql = "
    SELECT r.nom AS nom_restaurant, a.date, a.commentaire, c.nom AS nom_client, c.prenom
    FROM avis a
    JOIN clients c ON a.id_client = c.id_client
    JOIN restaurants r ON a.id_restaurant = r.id_restaurant
    ORDER BY r.nom, a.date DESC
";
$stmt = $pdo->query($sql);
$avis = $stmt->fetchAll();

// Organisation des avis par restaurant
$avis_par_restaurant = [];
foreach ($avis as $ligne) {
    $resto = $ligne['nom_restaurant'];
    if (!isset($avis_par_restaurant[$resto])) {
        $avis_par_restaurant[$resto] = [];
    }
    $avis_par_restaurant[$resto][] = $ligne;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Avis des Restaurants</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 2rem;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .restaurant {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .restaurant h2 {
            color: #4CAF50;
            margin-bottom: 1rem;
        }
        .avis {
            margin-left: 1rem;
        }
        .avis p {
            margin: 0.5rem 0;
        }
        .avis small {
            color: #777;
        }
        a {
            display: inline-block;
            margin-top: 2rem;
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Avis des clients par restaurant</h1>

<?php foreach ($avis_par_restaurant as $restaurant => $avis_list): ?>
    <div class="restaurant">
        <h2>🍽️ <?= htmlspecialchars($restaurant) ?></h2>
        <div class="avis">
            <?php foreach ($avis_list as $avis): ?>
                <p>
                    <?= htmlspecialchars($avis['prenom']) ?> <?= htmlspecialchars($avis['nom_client']) ?>
                    (<?= htmlspecialchars($avis['date']) ?>) :
                    <br><strong>"<?= htmlspecialchars($avis['commentaire']) ?>"</strong>
                </p>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<a href="index.php">← Retour à l'accueil</a>

</body>
</html>
