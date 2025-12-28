<?php

// --- 1. Connection Layer ---
class dbConnector
{
    // Private properties to store database connection details
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $name = "news-site1";

    // Method to establish and return a database connection
    public function getConnection(): mysqli
    {
        // Create a new mysqli object with the stored connection details
        $mysqli = new mysqli($this->host, $this->user, $this->pass, $this->name);

        // Check if the connection failed
        if ($mysqli->connect_error) {
            // Instead of storing errors in an array, we throw an Exception
            throw new Exception("Connection Failed: " . $mysqli->connect_error);
        }

        // Return the successful connection object
        return $mysqli;
    }
}


// --- 2. Data Persistence Layer (Query Builder) ---
class QueryBuilder
{
    // Private property to hold the mysqli connection object
    private $mysqli;

    // Constructor to initialize the QueryBuilder with a database connection
    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }


    public function select(string $table, string $columns = "*", ?string $where = null, $params = [])
    {
        // Build the base SQL
        $sql = "SELECT $columns FROM $table";

        // If a WHERE clause is provided, append it
        if ($where !== null) {
            $sql .= " WHERE $where";
        }

        // Prepare the statement
        $stmt = $this->mysqli->prepare($sql);

        if (!$stmt) {
            // Change: Don't append $this->mysqli->error here
            // We will log the real error to a file instead
            error_log("Prepare Error: " . $this->mysqli->error);
            throw new Exception("Internal Server Error: Could not prepare the request.");
        }

        // --- NEW LOGIC: Normalize parameters ---
        // If user sends a single string/int, wrap it in an array.
        // If they send nothing, it stays an empty array.
        if (!is_array($params))
            $params = [$params];

        if (!empty($params)) {
            $types = "";
            // 3. Dynamically bind parameters
            // We need to determine the types (s = string, i = integer, etc.)
            foreach ($params as $val) {
                $types .= is_int($val) ? "i" : (is_double($val) ? "d" : "s");
            }
            $values = array_values($params);
            $stmt->bind_param($types, ...$values);
        }

        // 4. Execute
        if ($stmt->execute()) {
            // GET THE DATA HERE
            $result = $stmt->get_result();
            $stmt->close();
            return $result; // Return the actual data object
        } else {
            error_log("Select Execution Error: " . $stmt->error);
            $stmt->close();
            throw new Exception("Error fetching data from the system.");
        }
    }

    // Function to insert data into a specified table
    public function insert(string $table, $params = array())
    {
        // Check if the table exists before attempting to insert
        // Change: Instead of just returning false, we let the exception bubble up
        if (!$this->tableExist($table)) {
            // We keep this specific because it's a developer setup error
            throw new Exception("System Error: Table configuration is incorrect.");
        }

        // Extract column names from the params array and join them with commas
        $table_columns = implode(', ', array_keys($params));
        // Extract values from the params array, wrap them in quotes, and join with commas
        //  $table_values = implode("', '", array_values($params));
        // 1. Create placeholders (?) for the values
        /*
        When using ? placeholders, the SQL engine expects them without quotes.
        If you put quotes around them, the database will think you are trying to insert a literal question 
        mark character instead of a placeholder.

        Correct: implode(', ', ...)
        Resulting SQL: VALUES (?, ?, ?)

        Incorrect: implode("', '", ...)
        Resulting SQL: VALUES ('?', '?', '?') <— This will fail or insert "?" as text.

        */
        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        // Build the SQL INSERT query string
        $sql = "INSERT INTO $table($table_columns) VALUES ( $placeholders)";

        // 2. Prepare the statement
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            // Change: Don't append $this->mysqli->error here
            // We will log the real error to a file instead
            error_log("Prepare Error: " . $this->mysqli->error);
            throw new Exception("Internal Server Error: Could not prepare the request.");
        }
        // 3. Dynamically bind parameters
        // We need to determine the types (s = string, i = integer, etc.)
        $types = "";
        foreach ($params as $val) {
            $types .= is_int($val) ? "i" : (is_double($val) ? "d" : "s");
        }

        $values = array_values($params);
        $stmt->bind_param($types, ...$values);
        // 4. Execute
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            // Log the technical error for the developer
            error_log("Insert Execution Error: " . $stmt->error);
            $stmt->close(); // <-- Always close even on failure
            // Throw a friendly error for the user
            throw new Exception("We encountered an issue saving your data. Please try again.");
        }
    }

    // Private method to check if a table exists in the database
    private function tableExist(string $table): bool
    {
        // SQL query to check for tables matching the given name
        $sql = "SHOW TABLES LIKE '$table'";
        // Execute the query
        $result = $this->mysqli->query($sql);

        // Check if the query was successful and returned exactly one row (table exists)
        if ($result && $result->num_rows == 1) {
            return true;
        }

        // Return false if table does not exist
        return false;
    }

    // Destructor to close the database connection when the object is destroyed
    // This addresses connection closing issues
    public function __destruct()
    {
        // Check if the mysqli connection exists
        if ($this->mysqli) {
            // Close the database connection
            $this->mysqli->close();
        }
    }
}


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
        $userEntity->setUsername($data['username']);
        $userEntity->setPassword($securePass);


        // Prepare final array for DB
        $finalData = [
            "first_name" => $data['first_name'],
            "last_name" => $data['last_name'],
            "username" => $userEntity->getUsername(),
            "password" => $userEntity->getPassword(),
            "role" => $data['role']
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

// --- 3. Entity Layer (Data Holder) ---
class User
{
    private $data = [];


    // --- Setters ---
    public function setFirstName($val)
    {
        $this->data['first_name'] = $val;
    }
    public function setLastName($val)
    {
        $this->data['last_name'] = $val;
    }
    public function setUsername($val)
    {
        $this->data['username'] = $val;
    }
    public function setPassword($val)
    {
        $this->data['password'] = $val;
    }
    public function setRole($val)
    {
        $this->data['role'] = $val;
    }


    // --- Getters (The parts you requested) ---
    public function getUsername(): ?string
    {
        return $this->data['username'] ?? null;
    }

    public function getPassword(): ?string
    {
        return $this->data['password'] ?? null;
    }
    public function getAllData(): array
    {
        return $this->data;
    }
    // Setters (Data goes IN)
}


// --- 3. Security Service (Password Manager) ---
class passwordManager
{
    private array $errors = [];

    /**
     * Validates password strength
     */
    public function ValidatePass(string $password)
    {
        if (strlen($password) < 8)
            $this->errors[] = "Password must be at least 8 characters.";
        if (!preg_match('/\d/', $password))
            $this->errors[] = "Include at least one number.";
        if (!preg_match('/[A-Z]/', $password))
            $this->errors[] = "Include at least one uppercase letter.";
        if (!preg_match('/[\W_]/', $password))
            $this->errors[] = "Include at least one special character.";
        return empty($this->errors);
    }

    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    public function getErrors(): array
    {
        return $this->errors;
    }
}



// --- 4. Utility Layer (Error Handler) ---
class errors
{
    /**
     * Handles error storage in sessions and redirects.
     * Use: errors::show($myErrors, "page.php");
     */
    public static function showError($errs, string $location)
    {
        $_SESSION['error'] = is_array($errs) ? implode("|||", $errs) : $errs;
        header("Location: $location");
        exit();
    }
}


// --- 5. Security Guard (Auth Layer) ---
class Auth
{
    /**
     * Shortcut to restrict access to Admins only.
     * Usage: Auth::adminOnly();
     */

    public static function adminAccess(string $redirect = "post.php")
    {
        // Check if session is started (safety check)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
            errors::showError("🚫 **Access Denied:** Admin permission required.", $redirect);
        }
    }
    /**
     * Optional: Shortcut to check if any user is logged in
     */
    public static function checkLogin(string $redirect = "index.php")
    {
        if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
            errors::showError("Please login to access this page.", $redirect);
        }
    }


}



