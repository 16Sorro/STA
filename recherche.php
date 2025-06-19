<?php
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
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit;
}

// Traitement de la recherche
$recherche = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limite = 12;
$offset = ($page - 1) * $limite;
$resultats = [];
$total_resultats = 0;

if (!empty($recherche)) {
    $sql_search = "
        SELECT 
            r.id_restaurant AS id,
            r.nom,
            COUNT(a.id_avis) AS nombre_avis,
            o.pays AS origine,
            GROUP_CONCAT(DISTINCT t.intitule SEPARATOR ', ') AS type_cuisine,
            r.nationalite
        FROM restaurants r
        LEFT JOIN avis a ON r.id_restaurant = a.id_restaurant
        LEFT JOIN origines o ON r.id_origine = o.id_origine
        LEFT JOIN types_restaurants tr ON r.id_restaurant = tr.id_restaurant
        LEFT JOIN types t ON tr.id_type = t.id_type
        WHERE r.nom LIKE :recherche
        GROUP BY r.id_restaurant, r.nom, o.pays, r.nationalite
        ORDER BY r.nom ASC
        LIMIT :limite OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql_search);
    $terme_recherche = '%' . $recherche . '%';
    $stmt->bindParam(':recherche', $terme_recherche, PDO::PARAM_STR);
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $resultats = $stmt->fetchAll();

    // Total pour pagination
    $stmt_count = $pdo->prepare("SELECT COUNT(*) AS total FROM restaurants r WHERE r.nom LIKE :recherche");
    $stmt_count->bindParam(':recherche', $terme_recherche, PDO::PARAM_STR);
    $stmt_count->execute();
    $total_resultats = $stmt_count->fetch()['total'];
}

$total_pages = ceil($total_resultats / $limite);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche - RestAdvisor</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 2rem; }
        h1 { text-align: center; }
        form { text-align: center; margin-bottom: 2rem; }
        input[type="text"] { padding: 10px; width: 300px; border-radius: 5px; border: 1px solid #ccc; }
        button { padding: 10px 20px; background: #2e8b57; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); cursor: pointer; transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .card h2 { margin: 0 0 10px; color: #333; }
        .meta { color: #555; font-size: 0.9rem; }
        .pagination { text-align: center; margin-top: 2rem; }
        .pagination a, .pagination span {
            margin: 0 5px;
            padding: 8px 12px;
            border-radius: 5px;
            background: #fff;
            border: 1px solid #ccc;
            text-decoration: none;
            color: #333;
        }
        .pagination .current { background: #2e8b57; color: white; border-color: #2e8b57; }
    </style>
</head>
<body>

<h1>Recherche de restaurants</h1>

<form action="recherche.php" method="get">
    <input type="text" name="q" placeholder="Nom du restaurant..." value="<?= htmlspecialchars($recherche) ?>" required>
    <button type="submit">Rechercher</button>
</form>

<?php if (!empty($recherche)): ?>
    <p style="text-align:center;"><?= $total_resultats ?> résultat<?= $total_resultats > 1 ? 's' : '' ?> trouvé<?= $total_resultats > 1 ? 's' : '' ?> pour "<strong><?= htmlspecialchars($recherche) ?></strong>"</p>

    <?php if ($resultats): ?>
        <div class="grid">
            <?php foreach ($resultats as $restaurant): ?>
                <div class="card" onclick="window.location.href='restaurant.php?id=<?= $restaurant['id'] ?>'">
                    <h2><?= htmlspecialchars($restaurant['nom']) ?></h2>
                    <?php if (!empty($restaurant['type_cuisine'])): ?>
                        <div class="meta"><strong>Type :</strong> <?= htmlspecialchars($restaurant['type_cuisine']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['origine'])): ?>
                        <div class="meta"><strong>Pays :</strong> <?= htmlspecialchars($restaurant['origine']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['nationalite'])): ?>
                        <div class="meta"><strong>Nationalité :</strong> <?= htmlspecialchars($restaurant['nationalite']) ?></div>
                    <?php endif; ?>
                    <div class="meta"><strong>Avis :</strong> <?= $restaurant['nombre_avis'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?q=<?= urlencode($recherche) ?>&page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p style="text-align:center; margin-top:2rem;">Aucun restaurant trouvé.</p>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
