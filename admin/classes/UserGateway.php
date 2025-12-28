<?php
class UserGateway
{
    private $db;
    private $query;

    public function __construct()
    {
        $this->db = (new dbConnector())->getConnection();
        $this->query = new QueryBuilder($this->db);
    }

    /**
     * The "Master" function. 
     * Validates, Hashes, and Saves internally.
     */
    public function createUser(array $data)
    {
        $passManager = new passwordManager();
        $errorsList = [];

        // 1. Validate Username Format
        if (!$this->validateUsername($data['username'])) {
            $errorsList[] = "Username can only contain lowercase letters, numbers, and underscores.";
        }

        // 2. Internal Validation (Class calling another class)
        if (!$passManager->ValidatePass($data['password'])) {
            // Merge password errors into our main errors list
            $errorsList = array_merge($errorsList, $passManager->getErrors());
        }

        // 3. If any validation failed, show all errors at once
        if (!empty($errorsList)) {
            errors::showError($errorsList, "add-user.php");
        }

        // 4. Check if Username already exists in DB
        $check = $this->query->select("user", "username", "username = ?", $data['username']);
        if ($check->num_rows > 0) {
            errors::showError("Username already exists.", "add-user.php");
        }

        // 5. Success Flow: Hash and Save
        $securePass = $passManager->hash($data['password']);

        // 3. Transform Data (Set the secure password)
        $userEntity = new User();
        $userEntity->setFirstName($data['first_name']);
        $userEntity->setLastName($data['last_name']);
        $userEntity->setUsername($data['username']);
        $userEntity->setPassword($securePass);
        $userEntity->setRole($data['role']);


        // Prepare final array for DB
        $finalData = [
            "first_name" => $userEntity->getFirstName(),
            "last_name" => $userEntity->getLastName(),
            "username" => $userEntity->getUsername(),
            "password" => $userEntity->getPassword(),
            "role" => $userEntity->getRole()
        ];

        // 4. Execution
        return $this->query->insert('user', $finalData);
    }
    /**
     * Internal validator for username format
     */
    private function validateUsername(string $username): bool
    {
        return (bool) preg_match("/^[a-z0-9_]+$/", $username);
    }
}