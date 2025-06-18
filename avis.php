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

$message = '';

// Traitement du formulaire d'ajout d'avis
if ($_POST && isset($_POST['ajouter_avis'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $restaurant_id = (int)$_POST['restaurant_id'];
    $commentaire = trim($_POST['commentaire']);
    
    if (!empty($nom) && !empty($prenom) && !empty($commentaire) && $restaurant_id > 0) {
        try {
            // Vérifier si le client existe déjà
            $sql_client = "SELECT id_client FROM clients WHERE nom = ? AND prenom = ?";
            $stmt_client = $pdo->prepare($sql_client);
            $stmt_client->execute([$nom, $prenom]);
            $client = $stmt_client->fetch();
            
            if ($client) {
                $client_id = $client['id_client'];
            } else {
                // Créer un nouveau client
                $sql_insert_client = "INSERT INTO clients (nom, prenom) VALUES (?, ?)";
                $stmt_insert_client = $pdo->prepare($sql_insert_client);
                $stmt_insert_client->execute([$nom, $prenom]);
                $client_id = $pdo->lastInsertId();
            }
            
            // Insérer l'avis
            $sql_insert_avis = "INSERT INTO avis (id_client, id_restaurant, commentaire, date) VALUES (?, ?, ?, NOW())";
            $stmt_insert_avis = $pdo->prepare($sql_insert_avis);
            $stmt_insert_avis->execute([$client_id, $restaurant_id, $commentaire]);
            
            $message = '<div class="message success">✅ Votre avis a été ajouté avec succès !</div>';
            
        } catch (\PDOException $e) {
            $message = '<div class="message error">❌ Erreur lors de l\'ajout de l\'avis : ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="message error">❌ Veuillez remplir tous les champs obligatoires.</div>';
    }
}

// Récupération de la liste des restaurants pour le formulaire
$sql_restaurants = "SELECT id_restaurant, nom FROM restaurants ORDER BY nom";
$stmt_restaurants = $pdo->query($sql_restaurants);
$restaurants = $stmt_restaurants->fetchAll();

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
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-container {
            background: #fff;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            color: #4CAF50;
            margin-bottom: 1rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .form-row {
            display: flex;
            gap: 1rem;
        }
        .form-row .form-group {
            flex: 1;
        }
        .btn {
            background: #4CAF50;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .btn:hover {
            background: #45a049;
        }
        .message {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            padding: 0.5rem;
            background: #f8f9fa;
            border-left: 3px solid #4CAF50;
            border-radius: 4px;
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
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    


<h1>Avis des clients par restaurant</h1>

<?php echo $message; ?>

<!-- Formulaire d'ajout d'avis -->
<div class="form-container">
    <h2>✍️ Laisser un avis</h2>
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="restaurant_id">Restaurant *</label>
            <select id="restaurant_id" name="restaurant_id" required>
                <option value="">-- Choisissez un restaurant --</option>
                <?php foreach ($restaurants as $restaurant): ?>
                    <option value="<?= $restaurant['id_restaurant'] ?>">
                        <?= htmlspecialchars($restaurant['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="commentaire">Votre avis *</label>
            <textarea id="commentaire" name="commentaire" placeholder="Partagez votre expérience..." required></textarea>
        </div>
        
        <button type="submit" name="ajouter_avis" class="btn">Publier mon avis</button>
    </form>
</div>

<!-- Affichage des avis existants -->
<?php if (empty($avis_par_restaurant)): ?>
    <div class="restaurant">
        <p>Aucun avis n'a encore été publié. Soyez le premier à laisser un avis !</p>
    </div>
<?php else: ?> 
    <?php foreach ($avis_par_restaurant as $restaurant => $avis_list): ?>
        <div class="restaurant">
            <h2>🍽️ <?= htmlspecialchars($restaurant) ?></h2>
            <div class="avis">
                <?php foreach ($avis_list as $avis): ?>
                    <p>
                        <strong><?= htmlspecialchars($avis['prenom']) ?> <?= htmlspecialchars($avis['nom_client']) ?></strong>
                        <small>(<?= date('d/m/Y', strtotime($avis['date'])) ?>)</small>
                        <br>"<?= htmlspecialchars($avis['commentaire']) ?>"
                    </p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<a href="index.php">← Retour à l'accueil</a>
    


</body>
</html>
