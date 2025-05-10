<?php
/**
 * Database connection for SariSari Stories
 */

require_once 'config.php';

/**
 * Connect to the database
 * 
 * @return PDO PDO instance
 * @throws PDOException
 */
function db_connect() {
    static $pdo;

    if (!isset($pdo)) {
        try {
            // Different connection strings for MySQL vs PostgreSQL
            if (DB_TYPE === 'mysql') {
                $dsn = DB_TYPE . ':host=' . DB_SERVER . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            } else if (DB_TYPE === 'pgsql') {
                // For PostgreSQL with SSL
                $dsn = DB_TYPE . ':host=' . DB_SERVER . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';sslmode=' . DB_SSL_MODE;
            } else {
                throw new PDOException("Unsupported database type: " . DB_TYPE);
            }
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
            
            // Set schema for PostgreSQL
            if (DB_TYPE === 'pgsql') {
                $pdo->exec("SET search_path TO public");
            }
        } catch (PDOException $e) {
            // Log the error but don't expose details to users
            error_log("Connection failed: " . $e->getMessage());
            throw new PDOException("Database connection failed. Please try again later.");
        }
    }

    return $pdo;
}

/**
 * Execute a query and return a single row result
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind to placeholders
 * @return array|false Associative array of result or false if no results
 */
function fetch_one($sql, $params = []) {
    try {
        $stmt = db_connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Query failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Execute a query and return all rows
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind to placeholders
 * @return array Array of associative arrays containing results
 */
function fetch_all($sql, $params = []) {
    try {
        $stmt = db_connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Query failed: " . $e->getMessage());
        return [];
    }
}

/**
 * Insert data into a table
 * 
 * @param string $table Table name
 * @param array $data Associative array of column => value pairs
 * @return int|false Last inserted ID or false on failure
 */
function insert($table, $data) {
    try {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = db_connect()->prepare($sql);
        $stmt->execute(array_values($data));
        
        return db_connect()->lastInsertId();
    } catch (PDOException $e) {
        error_log("Insert failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Update records in a table
 * 
 * @param string $table Table name
 * @param array $data Associative array of column => value pairs to update
 * @param string $where WHERE clause of the SQL statement
 * @param array $params Parameters for WHERE clause placeholders
 * @return int|false Number of affected rows or false on failure
 */
function update($table, $data, $where, $params = []) {
    try {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";
        
        $stmt = db_connect()->prepare($sql);
        $stmt->execute(array_merge(array_values($data), $params));
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Update failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete records from a table
 * 
 * @param string $table Table name
 * @param string $where WHERE clause
 * @param array $params Parameters for WHERE clause placeholders
 * @return int|false Number of affected rows or false on failure
 */
function delete($table, $where, $params = []) {
    try {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        $stmt = db_connect()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Delete failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Execute a custom SQL query
 * 
 * @param string $sql SQL query
 * @param array $params Parameters to bind
 * @return PDOStatement|false Statement object or false on failure
 */
function execute_query($sql, $params = []) {
    try {
        $stmt = db_connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query execution failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Create tables if they don't exist (initialization)
 */
function init_database() {
    try {
        $db = db_connect();
        
        // Users table
        if (DB_TYPE === 'mysql') {
            // MySQL version
            $db->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                bio TEXT,
                profile_image VARCHAR(255) DEFAULT 'images/default-avatar.jpg',
                is_admin TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else {
            // PostgreSQL version
            $db->exec("CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                bio TEXT,
                profile_image VARCHAR(255) DEFAULT 'images/default-avatar.jpg',
                is_admin BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // First create the timestamp function if it doesn't exist
            $db->exec("
                CREATE OR REPLACE FUNCTION update_timestamp()
                RETURNS TRIGGER AS $$
                BEGIN
                    NEW.updated_at = NOW();
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ");
            
            // Then try to create the trigger
            try {
                $db->exec("
                    CREATE TRIGGER users_update_timestamp
                    BEFORE UPDATE ON users
                    FOR EACH ROW
                    EXECUTE FUNCTION update_timestamp();
                ");
            } catch (PDOException $e) {
                // Trigger might already exist, which is fine
                error_log("Note: " . $e->getMessage());
            }
        }
        
        // Categories table
        if (DB_TYPE === 'mysql') {
            $db->exec("CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                description TEXT,
                icon VARCHAR(50),
                slug VARCHAR(50) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else {
            $db->exec("CREATE TABLE IF NOT EXISTS categories (
                id SERIAL PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                description TEXT,
                icon VARCHAR(50),
                slug VARCHAR(50) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        }
        
        // Stories table
        if (DB_TYPE === 'mysql') {
            $db->exec("CREATE TABLE IF NOT EXISTS stories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                featured_image VARCHAR(255),
                user_id INT NOT NULL,
                category_id INT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'published',
                featured BOOLEAN DEFAULT FALSE,
                views INT DEFAULT 0,
                slug VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else {
            $db->exec("CREATE TABLE IF NOT EXISTS stories (
                id SERIAL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                featured_image VARCHAR(255),
                user_id INT NOT NULL,
                category_id INT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'published',
                featured BOOLEAN DEFAULT FALSE,
                views INT DEFAULT 0,
                slug VARCHAR(255) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
            )");
            
            // Add trigger for updated_at column
            try {
                $db->exec("
                    CREATE TRIGGER stories_update_timestamp
                    BEFORE UPDATE ON stories
                    FOR EACH ROW
                    EXECUTE FUNCTION update_timestamp();
                ");
            } catch (PDOException $e) {
                // Trigger might already exist, which is fine
                error_log("Note: " . $e->getMessage());
            }
        }
        
        // Comments table
        if (DB_TYPE === 'mysql') {
            $db->exec("CREATE TABLE IF NOT EXISTS comments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                story_id INT NOT NULL,
                user_id INT NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else {
            $db->exec("CREATE TABLE IF NOT EXISTS comments (
                id SERIAL PRIMARY KEY,
                story_id INT NOT NULL,
                user_id INT NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )");
        }
        
        // Likes table
        if (DB_TYPE === 'mysql') {
            $db->exec("CREATE TABLE IF NOT EXISTS likes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                story_id INT NOT NULL,
                user_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (story_id, user_id),
                FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else {
            $db->exec("CREATE TABLE IF NOT EXISTS likes (
                id SERIAL PRIMARY KEY,
                story_id INT NOT NULL,
                user_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (story_id, user_id),
                FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )");
        }
        
        // Subscribers table
        if (DB_TYPE === 'mysql') {
            $db->exec("CREATE TABLE IF NOT EXISTS subscribers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL UNIQUE,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else {
            $db->exec("CREATE TABLE IF NOT EXISTS subscribers (
                id SERIAL PRIMARY KEY,
                email VARCHAR(100) NOT NULL UNIQUE,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        }
        
        // Insert default categories if none exist
        $categoryCount = fetch_one("SELECT COUNT(*) as count FROM categories");
        
        if ($categoryCount && $categoryCount['count'] == 0) {
            $defaultCategories = [
                ['name' => 'Local Eats', 'description' => 'Discover hidden culinary gems and traditional recipes', 'icon' => 'fas fa-utensils', 'slug' => 'local-eats'],
                ['name' => 'Personal Stories', 'description' => 'Share meaningful experiences and memories from your community', 'icon' => 'fas fa-book-open', 'slug' => 'personal-stories'],
                ['name' => 'Hidden Spots', 'description' => 'Explore lesser-known places with cultural and historical significance', 'icon' => 'fas fa-map-marker-alt', 'slug' => 'hidden-spots'],
                ['name' => 'Visual Tales', 'description' => 'Photo essays that capture the spirit of Filipino culture', 'icon' => 'fas fa-camera', 'slug' => 'visual-tales'],
                ['name' => 'Local Events', 'description' => 'Upcoming fiestas, markets, and community gatherings', 'icon' => 'fas fa-calendar-alt', 'slug' => 'local-events'],
                ['name' => 'Community Heroes', 'description' => 'Celebrating individuals making a difference', 'icon' => 'fas fa-users', 'slug' => 'community-heroes']
            ];
            
            foreach ($defaultCategories as $category) {
                insert('categories', $category);
            }
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Database initialization failed: " . $e->getMessage());
        return false;
    }
}

// Initialize the database
init_database();
?>
