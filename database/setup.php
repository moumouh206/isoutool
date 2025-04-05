<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a log file
$logFile = __DIR__ . '/setup.log';
file_put_contents($logFile, "Starting setup process at " . date('Y-m-d H:i:s') . "\n");

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo $message . "<br>\n";
}

/**
 * Database setup script
 * Run this script to create the necessary tables for the RS Components clone website
 */

try {
    logMessage("Including database connection file...");
    require_once __DIR__ . '/db_connect.php';

    logMessage("Creating database connection...");
    $database = new Database();
    $db = $database->connect();
    logMessage("Database connection established successfully.");

    logMessage("Enabling foreign key constraints...");
    // For MariaDB/MySQL, foreign key constraints are enabled by default
    // We just need to ensure the storage engine supports foreign keys
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');
    logMessage("Foreign key constraints enabled.");

    // Disable autocommit
    $db->setAttribute(PDO::ATTR_AUTOCOMMIT, 0);
    
    logMessage("Starting transaction...");
    $db->beginTransaction();
    logMessage("Transaction started successfully.");

    // Create users table
    logMessage("Creating users table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            company VARCHAR(255),
            phone VARCHAR(50),
            address_line_1 VARCHAR(255),
            address_line_2 VARCHAR(255),
            city VARCHAR(100),
            postal_code VARCHAR(20),
            country VARCHAR(100),
            account_type VARCHAR(20) DEFAULT "customer",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ');
    logMessage("Users table created successfully.");

    // Create categories table
    logMessage("Creating categories table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            parent_id INT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            image VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ');
    logMessage("Categories table created successfully.");

    // Create brands table
    logMessage("Creating brands table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS brands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            slug VARCHAR(100) NOT NULL UNIQUE,
            logo VARCHAR(255),
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ');
    logMessage("Brands table created successfully.");

    // Create products table
    logMessage("Creating products table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reference VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            brand_id INT,
            price DECIMAL(10, 2) NOT NULL,
            discount_price DECIMAL(10, 2),
            stock INT DEFAULT 0,
            weight DECIMAL(10, 2),
            dimensions VARCHAR(100),
            featured BOOLEAN DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ');
    logMessage("Products table created successfully.");

    // Create product_category table
    logMessage("Creating product_category table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS product_category (
            product_id INT,
            category_id INT,
            PRIMARY KEY (product_id, category_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ');
    logMessage("Product_category table created successfully.");

    // Create product_specifications table
    logMessage("Creating product_specifications table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS product_specifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT,
            name VARCHAR(100) NOT NULL,
            value TEXT NOT NULL,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ');
    logMessage("Product_specifications table created successfully.");

    // Create product_images table
    logMessage("Creating product_images table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT,
            image_path VARCHAR(255) NOT NULL,
            is_primary BOOLEAN DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ');
    logMessage("Product_images table created successfully.");

    // Create orders table
    logMessage("Creating orders table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            order_number VARCHAR(50) NOT NULL UNIQUE,
            status VARCHAR(50) NOT NULL DEFAULT "pending",
            subtotal DECIMAL(10, 2) NOT NULL,
            shipping DECIMAL(10, 2) NOT NULL,
            tax DECIMAL(10, 2) NOT NULL,
            total DECIMAL(10, 2) NOT NULL,
            shipping_address_line_1 VARCHAR(255),
            shipping_address_line_2 VARCHAR(255),
            shipping_city VARCHAR(100),
            shipping_postal_code VARCHAR(20),
            shipping_country VARCHAR(100),
            billing_address_line_1 VARCHAR(255),
            billing_address_line_2 VARCHAR(255),
            billing_city VARCHAR(100),
            billing_postal_code VARCHAR(20),
            billing_country VARCHAR(100),
            payment_method VARCHAR(50),
            payment_status VARCHAR(50) DEFAULT "pending",
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ');
    logMessage("Orders table created successfully.");

    // Create order_items table
    logMessage("Creating order_items table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            product_id INT,
            quantity INT NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            total DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ');
    logMessage("Order_items table created successfully.");

    // Create reviews table
    logMessage("Creating reviews table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT,
            user_id INT,
            rating INT NOT NULL,
            title VARCHAR(255),
            comment TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ');
    logMessage("Reviews table created successfully.");

    // Create wishlist table
    logMessage("Creating wishlist table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            product_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ');
    logMessage("Wishlist table created successfully.");

    // Create cart table
    logMessage("Creating cart table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            session_id VARCHAR(255),
            product_id INT,
            quantity INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ');
    logMessage("Cart table created successfully.");

    // Create news table
    logMessage("Creating news table...");
    $db->exec('
        CREATE TABLE IF NOT EXISTS news (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content TEXT NOT NULL,
            image VARCHAR(255),
            published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ');
    logMessage("News table created successfully.");

    // Create favorites table
    logMessage("Creating favorites table...");
    $db->exec("
        CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_favorite (user_id, product_id)
        ) ENGINE=InnoDB
    ");
    logMessage("Favorites table created successfully.");

    // Insert sample data - Categories
    logMessage("Inserting sample categories...");
    $categories = [
        ['name' => 'Composants électroniques', 'slug' => 'composants-electroniques', 'description' => 'Composants électroniques divers pour vos projets'],
        ['name' => 'Automatismes industriels', 'slug' => 'automatismes-industriels', 'description' => 'Équipements d\'automatisation pour l\'industrie'],
        ['name' => 'Outillage', 'slug' => 'outillage', 'description' => 'Outils et équipements pour professionnels'],
        ['name' => 'Électricité', 'slug' => 'electricite', 'description' => 'Matériel électrique et accessoires'],
        ['name' => 'Connectique', 'slug' => 'connectique', 'description' => 'Connecteurs et câbles pour tous vos besoins'],
        ['name' => 'Mécanique', 'slug' => 'mecanique', 'description' => 'Composants mécaniques et accessoires']
    ];

    $stmt = $db->prepare('INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)');
    foreach ($categories as $category) {
        $stmt->bindParam(':name', $category['name']);
        $stmt->bindParam(':slug', $category['slug']);
        $stmt->bindParam(':description', $category['description']);
        $stmt->execute();
        logMessage("Inserted category: " . $category['name']);
    }

    // Insert sample data - Brands
    logMessage("Inserting sample brands...");
    $brands = [
        ['name' => 'Schneider Electric', 'slug' => 'schneider-electric', 'description' => 'Leader mondial dans la gestion de l\'énergie et l\'automatisation'],
        ['name' => 'ABB', 'slug' => 'abb', 'description' => 'Entreprise spécialisée dans les technologies de l\'énergie et de l\'automatisation'],
        ['name' => 'Siemens', 'slug' => 'siemens', 'description' => 'Conglomérat allemand spécialisé dans les secteurs de l\'industrie, de l\'énergie et de la santé'],
        ['name' => 'Arduino', 'slug' => 'arduino', 'description' => 'Entreprise open-source créant des microcontrôleurs pour la construction d\'objets interactifs'],
        ['name' => 'Texas Instruments', 'slug' => 'texas-instruments', 'description' => 'Entreprise américaine spécialisée dans les semi-conducteurs'],
        ['name' => 'STMicroelectronics', 'slug' => 'stmicroelectronics', 'description' => 'Fabricant franco-italien de composants électroniques'],
        ['name' => 'RS PRO', 'slug' => 'rs-pro', 'description' => 'Marque propre de RS Components offrant une large gamme de produits de qualité']
    ];

    $stmt = $db->prepare('INSERT INTO brands (name, slug, description) VALUES (:name, :slug, :description)');
    foreach ($brands as $brand) {
        $stmt->bindParam(':name', $brand['name']);
        $stmt->bindParam(':slug', $brand['slug']);
        $stmt->bindParam(':description', $brand['description']);
        $stmt->execute();
        logMessage("Inserted brand: " . $brand['name']);
    }

    // Insert sample data - Products
    logMessage("Inserting sample products...");
    $products = [
        [
            'reference' => 'RS-123456',
            'name' => 'Microcontrôleur STM32F411RE Nucleo-64',
            'slug' => 'microcontroleur-stm32f411re-nucleo-64',
            'description' => 'Carte de développement STM32 Nucleo-64 avec microcontrôleur STM32F411RE ARM Cortex-M4. Compatible Arduino, cette carte est idéale pour le prototypage rapide et le développement d\'applications embarquées.',
            'brand_id' => 6, // STMicroelectronics
            'price' => 12.95,
            'stock' => 156,
            'featured' => 1
        ],
        [
            'reference' => 'RS-789012',
            'name' => 'Kit Arduino Uno Rev3',
            'slug' => 'kit-arduino-uno-rev3',
            'description' => 'Kit de démarrage Arduino Uno Rev3 avec microcontrôleur ATmega328P. Idéal pour débuter dans l\'électronique et la programmation.',
            'brand_id' => 4, // Arduino
            'price' => 24.95,
            'stock' => 84,
            'featured' => 1
        ],
        [
            'reference' => 'RS-345678',
            'name' => 'Variateur de fréquence 2.2kW',
            'slug' => 'variateur-de-frequence-2-2kw',
            'description' => 'Variateur de fréquence ABB 2.2kW pour le contrôle de moteurs industriels. Facile à installer et à configurer.',
            'brand_id' => 2, // ABB
            'price' => 349.50,
            'stock' => 28,
            'featured' => 0
        ],
        [
            'reference' => 'RS-901234',
            'name' => 'Multimètre numérique RS PRO',
            'slug' => 'multimetre-numerique-rs-pro',
            'description' => 'Multimètre numérique RS PRO avec écran LCD, mesure de tension AC/DC, courant, résistance, capacité, fréquence et température.',
            'brand_id' => 7, // RS PRO
            'price' => 79.99,
            'stock' => 112,
            'featured' => 1
        ],
        [
            'reference' => 'RS-567890',
            'name' => 'Kit de condensateurs céramiques 100 pcs',
            'slug' => 'kit-de-condensateurs-ceramiques-100-pcs',
            'description' => 'Kit de 100 condensateurs céramiques de différentes valeurs, de 1pF à 10µF. Parfait pour le prototypage et la réparation électronique.',
            'brand_id' => 7, // RS PRO
            'price' => 14.50,
            'stock' => 65,
            'featured' => 0
        ],
        [
            'reference' => 'RS-678901',
            'name' => 'Transistor NPN BC547 - Lot de 50',
            'slug' => 'transistor-npn-bc547-lot-de-50',
            'description' => 'Lot de 50 transistors NPN BC547. Conçus pour des applications d\'amplification et de commutation à basse puissance.',
            'brand_id' => 5, // Texas Instruments
            'price' => 7.99,
            'stock' => 93,
            'featured' => 0
        ]
    ];

    $stmt = $db->prepare('
        INSERT INTO products (reference, name, slug, description, brand_id, price, stock, featured)
        VALUES (:reference, :name, :slug, :description, :brand_id, :price, :stock, :featured)
    ');
    foreach ($products as $product) {
        $stmt->bindParam(':reference', $product['reference']);
        $stmt->bindParam(':name', $product['name']);
        $stmt->bindParam(':slug', $product['slug']);
        $stmt->bindParam(':description', $product['description']);
        $stmt->bindParam(':brand_id', $product['brand_id']);
        $stmt->bindParam(':price', $product['price']);
        $stmt->bindParam(':stock', $product['stock']);
        $stmt->bindParam(':featured', $product['featured']);
        $stmt->execute();
        logMessage("Inserted product: " . $product['name']);
    }

    // Associate products with categories
    logMessage("Associating products with categories...");
    $productCategories = [
        ['product_id' => 1, 'category_id' => 1], // STM32 in Composants électroniques
        ['product_id' => 2, 'category_id' => 1], // Arduino in Composants électroniques
        ['product_id' => 3, 'category_id' => 2], // Variateur in Automatismes industriels
        ['product_id' => 4, 'category_id' => 3], // Multimètre in Outillage
        ['product_id' => 5, 'category_id' => 1], // Condensateurs in Composants électroniques
        ['product_id' => 6, 'category_id' => 1]  // Transistors in Composants électroniques
    ];

    $stmt = $db->prepare('INSERT INTO product_category (product_id, category_id) VALUES (:product_id, :category_id)');
    foreach ($productCategories as $pc) {
        $stmt->bindParam(':product_id', $pc['product_id']);
        $stmt->bindParam(':category_id', $pc['category_id']);
        $stmt->execute();
        logMessage("Associated product ID {$pc['product_id']} with category ID {$pc['category_id']}");
    }

    // Insert product specifications for STM32
    logMessage("Inserting product specifications...");
    $specifications = [
        ['product_id' => 1, 'name' => 'Microcontrôleur', 'value' => 'STM32F411RE'],
        ['product_id' => 1, 'name' => 'Architecture', 'value' => 'ARM Cortex-M4'],
        ['product_id' => 1, 'name' => 'Fréquence', 'value' => '100 MHz'],
        ['product_id' => 1, 'name' => 'Mémoire Flash', 'value' => '512 KB'],
        ['product_id' => 1, 'name' => 'Mémoire RAM', 'value' => '128 KB'],
        ['product_id' => 1, 'name' => 'Tension d\'alimentation', 'value' => '3.3V / 5V'],
        ['product_id' => 1, 'name' => 'GPIO', 'value' => '50 pins'],
        ['product_id' => 1, 'name' => 'Interfaces', 'value' => 'USB, SPI, I2C, UART, ADC, PWM'],
        ['product_id' => 1, 'name' => 'Dimensions', 'value' => '70 x 82 mm']
    ];

    $stmt = $db->prepare('INSERT INTO product_specifications (product_id, name, value) VALUES (:product_id, :name, :value)');
    foreach ($specifications as $spec) {
        $stmt->bindParam(':product_id', $spec['product_id']);
        $stmt->bindParam(':name', $spec['name']);
        $stmt->bindParam(':value', $spec['value']);
        $stmt->execute();
        logMessage("Added specification '{$spec['name']}' for product ID {$spec['product_id']}");
    }

    // Insert sample news
    logMessage("Inserting sample news...");
    $news = [
        [
            'title' => 'Nouvelle gamme de composants électroniques',
            'slug' => 'nouvelle-gamme-composants-electroniques',
            'content' => 'Découvrez notre nouvelle sélection de composants électroniques de haute qualité pour vos projets. Une gamme complète de microcontrôleurs, capteurs et modules de communication.',
            'image' => 'news1.jpg',
            'published_at' => '2024-03-12'
        ],
        [
            'title' => 'Promotions du printemps',
            'slug' => 'promotions-printemps',
            'content' => 'Profitez de nos offres spéciales sur une large sélection de produits jusqu\'à -30%. Des réductions exceptionnelles sur les composants électroniques, l\'outillage et l\'automatisation.',
            'image' => 'news2.jpg',
            'published_at' => '2024-03-05'
        ],
        [
            'title' => 'Webinaire : Automatisation industrielle',
            'slug' => 'webinaire-automatisation-industrielle',
            'content' => 'Rejoignez-nous pour un webinaire sur les dernières tendances en automatisation industrielle. Nos experts partageront leurs connaissances sur l\'industrie 4.0 et les solutions innovantes.',
            'image' => 'news3.jpg',
            'published_at' => '2024-02-28'
        ],
        [
            'title' => 'Nouveau catalogue 2024',
            'slug' => 'nouveau-catalogue-2024',
            'content' => 'Notre nouveau catalogue 2024 est maintenant disponible ! Découvrez plus de 50 000 références de produits, avec des fiches techniques détaillées et des conseils d\'experts.',
            'image' => 'news4.jpg',
            'published_at' => '2024-02-15'
        ],
        [
            'title' => 'Formation Arduino : Session de printemps',
            'slug' => 'formation-arduino-printemps',
            'content' => 'Inscrivez-vous à notre prochaine session de formation Arduino. Apprenez à maîtriser la programmation et le développement de projets électroniques avec nos experts.',
            'image' => 'news5.jpg',
            'published_at' => '2024-02-10'
        ],
        [
            'title' => 'Lancement de notre nouvelle application mobile',
            'slug' => 'lancement-application-mobile',
            'content' => 'Commandez vos composants électroniques en toute simplicité avec notre nouvelle application mobile. Disponible sur iOS et Android, avec des fonctionnalités exclusives.',
            'image' => 'news6.jpg',
            'published_at' => '2024-01-25'
        ],
        [
            'title' => 'Nouveau centre de distribution',
            'slug' => 'nouveau-centre-distribution',
            'content' => 'Nous avons le plaisir d\'annoncer l\'ouverture de notre nouveau centre de distribution à Lyon. Des délais de livraison encore plus courts pour nos clients de la région.',
            'image' => 'news7.jpg',
            'published_at' => '2024-01-15'
        ],
        [
            'title' => 'Partenariat avec Raspberry Pi',
            'slug' => 'partenariat-raspberry-pi',
            'content' => 'Nous sommes fiers d\'annoncer notre nouveau partenariat avec Raspberry Pi. Une gamme complète de produits et d\'accessoires sera bientôt disponible.',
            'image' => 'news8.jpg',
            'published_at' => '2024-01-05'
        ],
        [
            'title' => 'Fêtez Noël avec nos offres spéciales',
            'slug' => 'offres-speciales-noel',
            'content' => 'Profitez de nos offres spéciales de fin d\'année sur une sélection de produits. Des cadeaux parfaits pour les passionnés d\'électronique et de DIY.',
            'image' => 'news9.jpg',
            'published_at' => '2023-12-15'
        ],
        [
            'title' => 'Nouvelle gamme de capteurs IoT',
            'slug' => 'nouvelle-gamme-capteurs-iot',
            'content' => 'Découvrez notre nouvelle gamme de capteurs IoT pour vos projets connectés. Des solutions innovantes pour la domotique et l\'industrie 4.0.',
            'image' => 'news10.jpg',
            'published_at' => '2023-12-01'
        ]
    ];

    $stmt = $db->prepare('
        INSERT INTO news (title, slug, content, image, published_at)
        VALUES (:title, :slug, :content, :image, :published_at)
    ');

    foreach ($news as $item) {
        $stmt->bindParam(':title', $item['title']);
        $stmt->bindParam(':slug', $item['slug']);
        $stmt->bindParam(':content', $item['content']);
        $stmt->bindParam(':image', $item['image']);
        $stmt->bindParam(':published_at', $item['published_at']);
        $stmt->execute();
        logMessage("Inserted news: " . $item['title']);
    }

    logMessage("Committing transaction...");
    $db->commit();
    logMessage("Transaction committed successfully.");

    // Re-enable autocommit
    $db->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);

    logMessage("Database setup completed successfully.");
    echo "<h1>Database Setup Completed Successfully</h1>";
    echo "<p>Check the setup.log file for detailed information about the setup process.</p>";

} catch (Exception $e) {
    logMessage("Error occurred: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    
    if ($db->inTransaction()) {
        logMessage("Rolling back transaction...");
    $db->rollBack();
        logMessage("Transaction rolled back.");
    }
    
    // Re-enable autocommit in case of error
    $db->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
    
    echo "<h1>Error During Database Setup</h1>";
    echo "<p>An error occurred during the database setup. Please check the setup.log file for details.</p>";
    echo "<p>Error message: " . htmlspecialchars($e->getMessage()) . "</p>";
}
