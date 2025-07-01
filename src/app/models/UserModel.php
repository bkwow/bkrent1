<?php

class UserModel {
	
	private $pdo;

	public function __construct() {
		$this->pdo = Database::getConnection();
	}
	
	/**
	 * MÉTODO CON LA CORRECCIÓN DEFINITIVA
	 */
	public function findByIdentifier(string $identifier) {
        $sql = "SELECT u.*, up.nombre FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.email = :email OR u.username = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $identifier, 'username' => $identifier]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function verifyPassword(string $identifier, string $password): ?array {
        $user = $this->findByIdentifier($identifier);
        // CORRECCIÓN: Usamos 'activated' = 1 para verificar que el usuario puede iniciar sesión
        if ($user && $user['activated'] == 1 && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return null;
    }

	// --- El resto de los métodos se mantienen igual ---

	public function createPasswordResetToken(string $email) {
		$sql = "SELECT * FROM users WHERE email = :email";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(['email' => $email]);
		$user = $stmt->fetch();
		
		if (!$user) {
			return false;
		}

		$token = bin2hex(random_bytes(32)); 
		$expires = date('Y-m-d H:i:s', time() + 3600); 

		$updateSql = "UPDATE users SET reset_token = :token, reset_token_expires_at = :expires WHERE email = :email";
		$updateStmt = $this->pdo->prepare($updateSql);
		
		if ($updateStmt->execute(['token' => $token, 'expires' => $expires, 'email' => $email])) {
			return ['token' => $token, 'user' => $user];
		}
		
		return false;
	}

	public function getProfile(int $userId) {
		$sql = "SELECT * FROM user_profiles WHERE user_id = :user_id";
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute(['user_id' => $userId]);
		return $stmt->fetch();
	}

		/**
	 * Registra un nuevo usuario (generalmente un cliente).
	 * @return int|false El ID del nuevo usuario o false si falla.
	 */
	public function registerClient(string $email, string $username, string $password) {
		if ($this->findByIdentifier($email) || $this->findByIdentifier($username)) {
			// El email o username ya existen
			return false;
		}

		$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
		
		$sql = "INSERT INTO users (email, username, password, role) VALUES (?, ?, ?, 'client')";
		$stmt = $this->pdo->prepare($sql);
		
		if ($stmt->execute([$email, $username, $hashedPassword])) {
			return $this->pdo->lastInsertId();
		}
		return false;
	}


	// --- NUEVOS MÉTODOS PARA EL MÓDULO DE GESTIÓN DE USUARIOS ---

    public function getUsersForDataTable(array $request) {
        $baseSql = "FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id";
        $selectColumns = "u.id, up.nombre, u.username, u.email, u.role, u.activated";
        $bindings = [];
        $where = " WHERE 1=1"; 

        if (!empty($request['search']['value'])) {
            $searchValue = '%' . $request['search']['value'] . '%';
            $where .= " AND (up.nombre LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.role LIKE ?)";
            array_push($bindings, $searchValue, $searchValue, $searchValue, $searchValue);
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(u.id) " . $baseSql . $where);
        $stmt->execute($bindings);
        $totalRecords = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("SELECT " . $selectColumns . " " . $baseSql . $where . " ORDER BY u.id DESC LIMIT ?, ?");
        array_push($bindings, (int)$request['start'], (int)$request['length']);
        foreach ($bindings as $key => $value) { $stmt->bindValue($key + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR); }
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ["draw" => intval($request['draw']), "recordsTotal" => intval($totalRecords), "recordsFiltered" => intval($totalRecords), "data" => $results];
    }

    public function getUserById(int $id) {
        $stmt = $this->pdo->prepare("SELECT u.id, u.username, u.email, u.role, u.activated, up.nombre FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE u.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function isUsernameOrEmailTaken(string $username, string $email, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM users WHERE (username = :username OR email = :email)";
        $bindings = [':username' => $username, ':email' => $email];
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $bindings[':id'] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchColumn() > 0;
    }

    public function createUser(array $data) {
        if ($this->isUsernameOrEmailTaken($data['username'], $data['email'])) {
            throw new Exception("El nombre de usuario o el email ya están en uso.");
        }
        $this->pdo->beginTransaction();
        try {
            $sqlUser = "INSERT INTO users (username, email, password, role, activated) VALUES (?, ?, ?, ?, ?)";
            $stmtUser = $this->pdo->prepare($sqlUser);
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmtUser->execute([$data['username'], $data['email'], $passwordHash, $data['role'], $data['activated']]);
            $userId = $this->pdo->lastInsertId();

            $sqlProfile = "INSERT INTO user_profiles (user_id, nombre) VALUES (?, ?)";
            $stmtProfile = $this->pdo->prepare($sqlProfile);
            $stmtProfile->execute([$userId, $data['nombre']]);

            $this->pdo->commit();
            return $userId;
        } catch (Exception $e) { $this->pdo->rollBack(); throw $e; }
    }

    public function updateUser(int $id, array $data) {
        if ($this->isUsernameOrEmailTaken($data['username'], $data['email'], $id)) {
            throw new Exception("El nombre de usuario o el email ya están en uso por otro usuario.");
        }
        $this->pdo->beginTransaction();
        try {
            if (!empty($data['password'])) {
                $sqlUser = "UPDATE users SET username=?, email=?, password=?, role=?, activated=? WHERE id=?";
                $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
                $paramsUser = [$data['username'], $data['email'], $passwordHash, $data['role'], $data['activated'], $id];
            } else {
                $sqlUser = "UPDATE users SET username=?, email=?, role=?, activated=? WHERE id=?";
                $paramsUser = [$data['username'], $data['email'], $data['role'], $data['activated'], $id];
            }
            $stmtUser = $this->pdo->prepare($sqlUser);
            $stmtUser->execute($paramsUser);

            $sqlProfile = "UPDATE user_profiles SET nombre=? WHERE user_id=?";
            $stmtProfile = $this->pdo->prepare($sqlProfile);
            $stmtProfile->execute([$data['nombre'], $id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) { $this->pdo->rollBack(); throw $e; }
    }

    public function deleteUser(int $id) {
        $stmt = $this->pdo->prepare("UPDATE users SET activated = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}