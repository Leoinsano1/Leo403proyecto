<?php
// Configuración de la base de datos
class Database {
    private $host = 'localhost';
    private $dbname = 'portal_web';
    private $username = 'root';
    private $password = '';
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// Clase para manejar mensajes del chat
class ChatManager {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->createTables();
    }
    
    private function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS chat_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message TEXT NOT NULL,
            is_bot BOOLEAN DEFAULT FALSE,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            session_id VARCHAR(255),
            INDEX idx_session (session_id),
            INDEX idx_timestamp (timestamp)
        )";
        
        $this->db->exec($sql);
        
        // Tabla para estadísticas de juegos
        $sql2 = "CREATE TABLE IF NOT EXISTS game_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            game_type VARCHAR(50) NOT NULL,
            score INT DEFAULT 0,
            moves INT DEFAULT 0,
            completed BOOLEAN DEFAULT FALSE,
            completion_time INT DEFAULT 0,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            session_id VARCHAR(255),
            INDEX idx_game_type (game_type),
            INDEX idx_session (session_id)
        )";
        
        $this->db->exec($sql2);
    }
    
    public function saveMessage($message, $isBot = false, $sessionId = null) {
        $sql = "INSERT INTO chat_messages (message, is_bot, session_id) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$message, $isBot, $sessionId]);
    }
    
    public function getMessages($sessionId = null, $limit = 50) {
        if ($sessionId) {
            $sql = "SELECT * FROM chat_messages WHERE session_id = ? ORDER BY timestamp DESC LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$sessionId, $limit]);
        } else {
            $sql = "SELECT * FROM chat_messages ORDER BY timestamp DESC LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
        }
        
        return array_reverse($stmt->fetchAll());
    }
    
    public function saveGameStats($gameType, $score = 0, $moves = 0, $completed = false, $completionTime = 0, $sessionId = null) {
        $sql = "INSERT INTO game_stats (game_type, score, moves, completed, completion_time, session_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$gameType, $score, $moves, $completed, $completionTime, $sessionId]);
    }
    
    public function getGameStats($gameType = null) {
        if ($gameType) {
            $sql = "SELECT 
                        COUNT(*) as total_games,
                        AVG(score) as avg_score,
                        AVG(moves) as avg_moves,
                        AVG(completion_time) as avg_time,
                        SUM(completed) as completed_games
                    FROM game_stats 
                    WHERE game_type = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$gameType]);
        } else {
            $sql = "SELECT 
                        game_type,
                        COUNT(*) as total_games,
                        AVG(score) as avg_score,
                        AVG(moves) as avg_moves,
                        AVG(completion_time) as avg_time,
                        SUM(completed) as completed_games
                    FROM game_stats 
                    GROUP BY game_type";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        
        return $stmt->fetchAll();
    }
}

// Clase para manejar proyectos externos
class ProjectManager {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->createTables();
    }
    
    private function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS external_projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            url VARCHAR(500) NOT NULL,
            description TEXT,
            category VARCHAR(100),
            is_active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_active (is_active)
        )";
        
        $this->db->exec($sql);
        
        // Insertar algunos proyectos de ejemplo si la tabla está vacía
        $count = $this->db->query("SELECT COUNT(*) FROM external_projects")->fetchColumn();
        if ($count == 0) {
            $this->insertSampleProjects();
        }
    }
    
    private function insertSampleProjects() {
        $projects = [
            [
                'name' => 'Mi Portfolio Personal',
                'url' => 'https://mi-portfolio.com',
                'description' => 'Mi sitio web personal con mis proyectos y experiencia',
                'category' => 'Personal'
            ],
            [
                'name' => 'Proyecto React',
                'url' => 'https://mi-app-react.com',
                'description' => 'Aplicación web desarrollada con React y Node.js',
                'category' => 'Desarrollo Web'
            ],
            [
                'name' => 'GitHub Profile',
                'url' => 'https://github.com/mi-usuario',
                'description' => 'Mi perfil de GitHub con todos mis repositorios',
                'category' => 'Código'
            ]
        ];
        
        $sql = "INSERT INTO external_projects (name, url, description, category) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($projects as $project) {
            $stmt->execute([
                $project['name'],
                $project['url'],
                $project['description'],
                $project['category']
            ]);
        }
    }
    
    public function addProject($name, $url, $description = '', $category = 'General') {
        $sql = "INSERT INTO external_projects (name, url, description, category) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $url, $description, $category]);
    }
    
    public function getProjects($category = null, $activeOnly = true) {
        $sql = "SELECT * FROM external_projects WHERE 1=1";
        $params = [];
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function updateProject($id, $name, $url, $description = '', $category = 'General') {
        $sql = "UPDATE external_projects 
                SET name = ?, url = ?, description = ?, category = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$name, $url, $description, $category, $id]);
    }
    
    public function deleteProject($id) {
        $sql = "DELETE FROM external_projects WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function toggleProject($id) {
        $sql = "UPDATE external_projects SET is_active = NOT is_active WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}

// API endpoints
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_projects':
            $projectManager = new ProjectManager();
            $category = $_GET['category'] ?? null;
            $projects = $projectManager->getProjects($category);
            echo json_encode($projects);
            break;
            
        case 'add_project':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $projectManager = new ProjectManager();
                $result = $projectManager->addProject(
                    $input['name'],
                    $input['url'],
                    $input['description'] ?? '',
                    $input['category'] ?? 'General'
                );
                echo json_encode(['success' => $result]);
            }
            break;
            
        case 'get_game_stats':
            $chatManager = new ChatManager();
            $gameType = $_GET['game_type'] ?? null;
            $stats = $chatManager->getGameStats($gameType);
            echo json_encode($stats);
            break;
            
        case 'save_game_result':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $chatManager = new ChatManager();
                $result = $chatManager->saveGameStats(
                    $input['game_type'],
                    $input['score'] ?? 0,
                    $input['moves'] ?? 0,
                    $input['completed'] ?? false,
                    $input['completion_time'] ?? 0,
                    $input['session_id'] ?? null
                );
                echo json_encode(['success' => $result]);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Acción no encontrada']);
    }
}
?>