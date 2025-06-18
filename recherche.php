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

// Récupération et nettoyage de la recherche
$recherche = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limite = 12; // Nombre de résultats par page
$offset = ($page - 1) * $limite;

$resultats = [];
$total_resultats = 0;
echo $recherche;
if (!empty($recherche)) {
    try {
        // Requête de recherche adaptée à votre vraie structure de base de données
        $sql_search = "
SELECT r.id_restaurant AS id,
       r.nom,
       COUNT(a.id_avis) AS nombre_avis
FROM restaurants r
LEFT JOIN avis a ON r.id_restaurant = a.id_restaurant
WHERE r.nom LIKE :recherche
GROUP BY r.id_restaurant, r.nom
ORDER BY r.nom ASC
LIMIT :limite OFFSET :offset;


        ";
        
        $stmt = $pdo->prepare($sql_search);
        $terme_recherche = '%' . $recherche . '%';
        $stmt->bindParam(':recherche', $terme_recherche, PDO::PARAM_STR);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $resultats = $stmt->fetchAll();
        
        // Compter le total pour la pagination (recherche seulement sur restaurant.nom)
        $sql_count = "
            SELECT COUNT(*) as total
            FROM restaurants r
            WHERE r.nom LIKE :recherche
        ";
        
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->bindParam(':recherche', $terme_recherche, PDO::PARAM_STR);
        $stmt_count->execute();
        $total_resultats = $stmt_count->fetch()['total'];

        
        
    } catch (\PDOException $e) {
        $error_message = "Erreur lors de la recherche : " . $e->getMessage();
        echo $error_message;
    }
}

$total_pages = ceil($total_resultats / $limite);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= !empty($recherche) ? 'Résultats pour "' . htmlspecialchars($recherche) . '"' : 'Recherche' ?> - RestAdvisor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f8fafc; 
            line-height: 1.6;
        }
        
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            background: #fff;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            height: 70px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #2e8b57;
            letter-spacing: 1px;
            text-decoration: none;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        
        .header-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .header-link:hover {
            background: #e0f2ef;
            transform: translateY(-1px);
        }
        
        .burger {
            width: 32px;
            height: 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            cursor: pointer;
        }
        
        .burger span {
            height: 4px;
            background: #2e8b57;
            margin: 4px 0;
            border-radius: 2px;
            transition: 0.3s;
        }
        
        .search-header {
            background: linear-gradient(135deg, #2e8b57 0%, #20b2aa 50%, #48cae4 100%);
            padding: 40px 32px;
            color: white;
        }
        
        .search-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .search-form {
            display: flex;
            background: #fff;
            border-radius: 50px;
            padding: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            margin-bottom: 20px;
        }
        
        .search-input {
            flex: 1;
            border: none;
            padding: 16px 24px;
            font-size: 1.1rem;
            border-radius: 50px;
            outline: none;
            background: transparent;
        }
        
        .search-btn {
            background: linear-gradient(135deg, #2e8b57, #20b2aa);
            color: white;
            border: none;
            padding: 16px 32px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            transform: translateX(-2px);
            box-shadow: 0 5px 15px rgba(46, 139, 87, 0.4);
        }
        
        .search-info {
            text-align: center;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .search-suggestions {
            text-align: center;
            margin-top: 16px;
            opacity: 0.8;
        }
        
        .suggestion-tag {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            margin: 4px;
            border-radius: 20px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .suggestion-tag:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }
        
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 32px;
        }
        
        .results-header {
            margin-bottom: 32px;
        }
        
        .results-title {
            font-size: 2rem;
            color: #2d3748;
            margin-bottom: 8px;
        }
        
        .results-count {
            color: #718096;
            font-size: 1.1rem;
        }
        
        .filters {
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 32px;
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-label {
            font-weight: 600;
            color: #2d3748;
        }
        
        .filter-select {
            padding: 8px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            color: #2d3748;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s ease;
        }
        
        .filter-select:focus {
            border-color: #2e8b57;
        }
        
        .restaurants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 32px;
            margin-bottom: 40px;
        }
        
        .restaurant-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            border: 1px solid #e2e8f0;
            cursor: pointer;
        }
        
        .restaurant-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        
        .restaurant-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            position: relative;
        }
        
        .restaurant-image::after {
            content: '🍽️';
            position: absolute;
        }
        
        .restaurant-info {
            padding: 24px;
        }
        
        .restaurant-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }
        
        .restaurant-cuisine {
            color: #2e8b57;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .restaurant-origine {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 12px;
            font-style: italic;
        }
        
        .restaurant-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        
        .rating {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stars {
            display: flex;
            gap: 2px;
        }
        
        .star {
            color: #ffc107;
            font-size: 1.2rem;
        }
        
        .star.empty {
            color: #e2e8f0;
        }
        
        .rating-value {
            font-weight: 600;
            color: #2d3748;
            margin-left: 4px;
        }
        
        .review-count {
            color: #718096;
            font-size: 0.9rem;
        }
        
        .no-results {
            text-align: center;
            padding: 80px 20px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .no-results-icon {
            font-size: 4rem;
            margin-bottom: 24px;
            opacity: 0.5;
        }
        
        .no-results-title {
            font-size: 1.5rem;
            color: #2d3748;
            margin-bottom: 16px;
        }
        
        .no-results-text {
            color: #718096;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 40px;
        }
        
        .pagination a, .pagination span {
            padding: 12px 16px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .pagination a {
            color: #2e8b57;
            background: #fff;
            border: 2px solid #e2e8f0;
        }
        
        .pagination a:hover {
            background: #e0f2ef;
            border-color: #2e8b57;
            transform: translateY(-1px);
        }
        
        .pagination .current {
            background: #2e8b57;
            color: white;
            border: 2px solid #2e8b57;
        }
        
        .pagination .disabled {
            color: #cbd5e0;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            header { 
                flex-direction: column; 
                height: auto; 
                padding: 16px; 
            }
            
            .header-right { 
                gap: 12px; 
            }
            
            .search-header {
                padding: 30px 20px;
            }
            
            .search-form {
                flex-direction: column;
                border-radius: 16px;
                padding: 16px;
            }
            
            .search-input {
                margin-bottom: 12px;
                border-radius: 12px;
            }
            
            .search-btn {
                border-radius: 12px;
            }
            
            .main-content {
                padding: 30px 20px;
            }
            
            .restaurants-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .pagination {
                flex-wrap: wrap;
                gap: 4px;
            }
            
            .pagination a, .pagination span {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
        }
        
        /* Menu burger styles */
        #burger-menu {
            display: none;
            position: fixed;
            top: 70px;
            right: 0;
            background: #fff;
            box-shadow: -2px 0 20px rgba(0,0,0,0.1);
            width: 250px;
            z-index: 1000;
            border-radius: 0 0 0 16px;
        }
        
        #burger-menu ul {
            list-style: none;
            margin: 0;
            padding: 20px;
        }
        
        #burger-menu li {
            margin-bottom: 8px;
        }
        
        #burger-menu .header-link {
            display: block;
            padding: 12px 16px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo">RestAdvisor</a>
        <div class="header-right">
            <a href="register.php" class="header-link">Inscription</a>
            <a href="login.php" class="header-link">Connexion</a>
            <div class="burger" onclick="toggleMenu()">
                <span style="width:100%"></span>
                <span style="width:100%"></span>
                <span style="width:100%"></span>
            </div>
        </div>
    </header>
    
    <!-- Menu burger -->
    <nav id="burger-menu">
        <ul>
            <li><a href="index.php" class="header-link">Accueil</a></li>
            <li><a href="restaurants.php" class="header-link">Restaurants</a></li>
            <li><a href="contact.php" class="header-link">Contact</a></li>
        </ul>
    </nav>
    
    <section class="search-header">
        <div class="search-container">
            <form class="search-form" action="recherche.php" method="GET">
                <input type="text" 
                       class="search-input" 
                       name="q" 
                       value="<?= htmlspecialchars($recherche) ?>"
                       placeholder="Rechercher un restaurant, une cuisine, une nationalité..."
                       required>
                <button type="submit" class="search-btn">Rechercher</button>
            </form>
            <?php if (!empty($recherche)): ?>
                <div class="search-info">
                    Résultats de recherche pour "<?= htmlspecialchars($recherche) ?>"
                </div>
            <?php else: ?>
                <div class="search-suggestions">
                    <div style="margin-bottom: 8px;">Suggestions de recherche :</div>
                    <span class="suggestion-tag" onclick="rechercher('sushi')">Sushi</span>
                    <span class="suggestion-tag" onclick="rechercher('pizza')">Pizza</span>
                    <span class="suggestion-tag" onclick="rechercher('français')">Français</span>
                    <span class="suggestion-tag" onclick="rechercher('italien')">Italien</span>
                    <span class="suggestion-tag" onclick="rechercher('gastronomique')">Gastronomique</span>
                    <span class="suggestion-tag" onclick="rechercher('bistrot')">Bistrot</span>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <main class="main-content">
        <?php if (!empty($recherche)): ?>
            <div class="results-header">
                <h1 class="results-title">Résultats de recherche</h1>
                <p class="results-count">
                    <?= $total_resultats ?> restaurant<?= $total_resultats > 1 ? 's' : '' ?> trouvé<?= $total_resultats > 1 ? 's' : '' ?>
                    <?php if ($total_pages > 1): ?>
                        - Page <?= $page ?> sur <?= $total_pages ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if (!empty($resultats)): ?>
                <div class="filters">
                    <span class="filter-label">Trier par :</span>
                    <select class="filter-select" onchange="changeSort(this.value)">
                        <option value="note">Note (plus haute d'abord)</option>
                        <option value="avis">Nombre d'avis</option>
                        <option value="nom">Nom (A-Z)</option>
                    </select>
                </div>
                
                <div class="restaurants-grid">
                    <?php foreach ($resultats as $restaurant): ?>
                        <div class="restaurant-card" onclick="voirRestaurant(<?= $restaurant['id'] ?>)">
                            <div class="restaurant-image"></div>
                            <div class="restaurant-info">
                                <h3 class="restaurant-name"><?= htmlspecialchars($restaurant['nom']) ?></h3>
                                <?php if (!empty($restaurant['type_cuisine'])): ?>
                                    <div class="restaurant-cuisine"><?= htmlspecialchars($restaurant['type_cuisine']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($restaurant['origine']) || !empty($restaurant['nationalite'])): ?>
                                    <div class="restaurant-origine">
                                        <?= htmlspecialchars($restaurant['origine'] ?? '') ?>
                                        <?php if (!empty($restaurant['nationalite'])): ?>
                                            (<?= htmlspecialchars($restaurant['nationalite']) ?>)
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="restaurant-meta">
                                    <span class="review-count"><?= $restaurant['nombre_avis'] ?> avis</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?q=<?= urlencode($recherche) ?>&page=<?= $page - 1 ?>">‹ Précédent</a>
                        <?php else: ?>
                            <span class="disabled">‹ Précédent</span>
                        <?php endif; ?>
                        
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        if ($start > 1): ?>
                            <a href="?q=<?= urlencode($recherche) ?>&page=1">1</a>
                            <?php if ($start > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?q=<?= urlencode($recherche) ?>&page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($end < $total_pages): ?>
                            <?php if ($end < $total_pages - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="?q=<?= urlencode($recherche) ?>&page=<?= $total_pages ?>"><?= $total_pages ?></a>
                        <?php endif; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?q=<?= urlencode($recherche) ?>&page=<?= $page + 1 ?>">Suivant ›</a>
                        <?php else: ?>
                            <span class="disabled">Suivant ›</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">🔍</div>
                    <h2 class="no-results-title">Aucun résultat trouvé</h2>
                    <p class="no-results-text">
                        Nous n'avons trouvé aucun restaurant correspondant à votre recherche "<strong><?= htmlspecialchars($recherche) ?></strong>".<br>
                        Essayez avec d'autres mots-clés comme le nom du restaurant, le type de cuisine, ou la nationalité.
                    </p>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">🍽️</div>
                <h2 class="no-results-title">Découvrez nos restaurants</h2>
                <p class="no-results-text">
                    Utilisez la barre de recherche ci-dessus pour trouver des restaurants par nom, type de cuisine, nationalité ou origine.<br>
                    Vous pouvez rechercher par exemple : "sushi", "italien", "gastronomique", "bistrot"...
                </p>
            </div>
        <?php endif; ?>
    </main>
    
    <script>
        function toggleMenu() {
            var menu = document.getElementById('burger-menu');
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }
        
        // Fermer le menu si on clique ailleurs
        document.addEventListener('click', function(e) {
            var menu = document.getElementById('burger-menu');
            var burger = document.querySelector('.burger');
            if (!menu.contains(e.target) && !burger.contains(e.target)) {
                menu.style.display = 'none';
            }
        });
        
        function voirRestaurant(id) {
            // Redirection vers la page de détail du restaurant
            window.location.href = 'restaurant.php?id=' + id;
        }
        
        function changeSort(sortBy) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('sort', sortBy);
            urlParams.set('page', '1'); // Reset à la première page
            window.location.href = '?' + urlParams.toString();
        }
        
        function rechercher(terme) {
            document.querySelector('.search-input').value = terme;
            document.querySelector('.search-form').submit();
        }
        
        // Animation des cartes au scroll
        function animateCards() {
            const cards = document.querySelectorAll('.restaurant-card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        }
        
        // Lancer l'animation au chargement
        document.addEventListener('DOMContentLoaded', animateCards);
        
        // Focus automatique sur la barre de recherche si vide
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            if (!searchInput.value.trim()) {
                searchInput.focus();
            }
        });
    </script>
</body>
</html>