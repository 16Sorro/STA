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


// Récupération des restaurants les mieux notés (coups de cœur)
try {
    $stmt = $pdo->prepare("
        SELECT r.*, 
               AVG(a.note) as note_moyenne,
               COUNT(a.id) as nombre_avis
        FROM restaurants r 
        LEFT JOIN avis a ON r.id = a.restaurant_id 
        GROUP BY r.id 
        HAVING note_moyenne >= 4.0 
        ORDER BY note_moyenne DESC, nombre_avis DESC 
        LIMIT 6
    ");
    $stmt->execute();
    $coups_de_coeur = $stmt->fetchAll();
} catch (\PDOException $e) {
    $coups_de_coeur = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>RestAdvisor - Découvrez les meilleurs restaurants</title>
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
        
        .hero {
            background: linear-gradient(135deg, #2e8b57 0%, #20b2aa 50%, #48cae4 100%);
            color: #fff;
            padding: 80px 32px 120px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="1" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="1" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: -1px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 40px;
            font-weight: 300;
        }
        
        .search-container {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
            transform: translateY(50px);
        }
        
        .search-form {
            display: flex;
            background: #fff;
            border-radius: 50px;
            padding: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }
        
        .search-form:hover {
            box-shadow: 0 15px 50px rgba(0,0,0,0.2);
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
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }
        
        .section-subtitle {
            text-align: center;
            font-size: 1.1rem;
            color: #718096;
            margin-bottom: 60px;
        }
        
        .coups-de-coeur {
            padding: 120px 32px 80px 32px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .restaurants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 32px;
            margin-top: 40px;
        }
        
        .restaurant-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            border: 1px solid #e2e8f0;
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
            margin-bottom: 12px;
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
        
        .restaurant-address {
            color: #718096;
            font-size: 0.95rem;
            line-height: 1.4;
        }
        
        .no-restaurants {
            text-align: center;
            color: #718096;
            font-size: 1.1rem;
            padding: 60px 20px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
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
            
            .hero {
                padding: 60px 20px 100px 20px;
            }
            
            .hero-title { 
                font-size: 2.5rem; 
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
            
            .coups-de-coeur {
                padding: 100px 20px 60px 20px;
            }
            
            .restaurants-grid {
                grid-template-columns: 1fr;
                gap: 24px;
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
            <li><a href="restaurant.php" class="header-link">Restaurants</a></li>
            <li><a href="contact.php" class="header-link">Contact</a></li>
            <li><a href="avis.php" class="header-link">Avis</a></li>
            <li><a href="employes_par_restaurant.php" class="header-link">Employés par restaurant</a></li>
        </ul>
    </nav>
    
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">Découvrez les saveurs qui vous entourent</h1>
            <p class="hero-subtitle">Explorez les meilleurs restaurants de votre région grâce aux avis de notre communauté</p>
            
            <div class="search-container">
                <form class="search-form" action="recherche.php" method="GET">
                    <input type="text" 
                           class="search-input" 
                           name="q" 
                           placeholder="Rechercher un restaurant, une cuisine, une ville..."
                           required>
                    <button type="submit" class="search-btn">Rechercher</button>
                </form>
            </div>
        </div>
    </section>
    
    <section class="coups-de-coeur">
        <h2 class="section-title">Nos Coups de Cœur</h2>
        <p class="section-subtitle">Les restaurants les mieux notés par notre communauté</p>
        
        <div class="restaurants-grid">
            <?php if (!empty($coups_de_coeur)): ?>
                <?php foreach ($coups_de_coeur as $restaurant): ?>
                    <div class="restaurant-card">
                        <div class="restaurant-image"></div>
                        <div class="restaurant-info">
                            <h3 class="restaurant-name"><?= htmlspecialchars($restaurant['nom']) ?></h3>
                            <div class="restaurant-meta">
                                <div class="rating">
                                    <div class="stars">
                                        <?php 
                                        $note = round($restaurant['note_moyenne'], 1);
                                        $etoiles_pleines = floor($note);
                                        $etoile_demi = ($note - $etoiles_pleines) >= 0.5 ? 1 : 0;
                                        $etoiles_vides = 5 - $etoiles_pleines - $etoile_demi;
                                        
                                        // Étoiles pleines
                                        for ($i = 0; $i < $etoiles_pleines; $i++): ?>
                                            <span class="star">★</span>
                                        <?php endfor; ?>
                                        
                                        <!-- Demi-étoile -->
                                        <?php if ($etoile_demi): ?>
                                            <span class="star">★</span>
                                        <?php endif; ?>
                                        
                                        <!-- Étoiles vides -->
                                        <?php for ($i = 0; $i < $etoiles_vides; $i++): ?>
                                            <span class="star empty">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-value"><?= number_format($note, 1) ?></span>
                                </div>
                                <span class="review-count"><?= $restaurant['nombre_avis'] ?> avis</span>
                            </div>
                            <p class="restaurant-address">
                                <?= htmlspecialchars($restaurant['adresse'] ?? 'Adresse non renseignée') ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-restaurants">
                    <p>Aucun restaurant trouvé pour le moment. Soyez le premier à ajouter un avis !</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
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
        
        // Animation smooth pour les cartes au scroll
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
    </script>
</body>
</html>
