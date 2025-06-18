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

// Requête : tous les employés classés par restaurant
$sql = "
    SELECT r.nom AS nom_restaurant, e.prenom, e.nom AS nom_employe, p.intitule AS poste
    FROM restaurants r
    JOIN emplois em ON r.id_restaurant = em.id_restaurant
    JOIN employes e ON em.id_employe = e.id_employe
    JOIN postes p ON e.id_poste = p.id_poste
    ORDER BY r.nom, e.nom
";

$employes = $pdo->query($sql)->fetchAll();

// Regrouper les employés par restaurant
$groupes = [];
foreach ($employes as $emp) {
    $groupes[$emp['nom_restaurant']][] = $emp;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Employés par Restaurant</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 2rem; }
        .restaurant { background: white; margin-bottom: 2rem; padding: 1.5rem; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #2196F3; }
        ul { list-style: none; padding: 0; }
        li { margin: 0.5rem 0; }
    </style>
</head>
<body>

<h1>👨‍🍳 Employés par Restaurant</h1>

<?php foreach ($groupes as $nom_resto => $liste_employes): ?>
    <div class="restaurant">
        <h2><?= htmlspecialchars($nom_resto) ?></h2>
        <ul>
            <?php foreach ($liste_employes as $emp): ?>
                <li>
                    <?= htmlspecialchars($emp['prenom']) ?> <?= htmlspecialchars($emp['nom_employe']) ?>
                    — <em><?= htmlspecialchars($emp['poste']) ?></em>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endforeach; ?>

</body>
</html>
