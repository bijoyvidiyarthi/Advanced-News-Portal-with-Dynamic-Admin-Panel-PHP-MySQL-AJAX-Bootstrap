<?php
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
            // We will log the real error to a file instead
            error_log("Prepare Error: " . $this->mysqli->error);
            throw new Exception("Internal Server Error: Could not prepare the request.");
        }
        // 3. Dynamically bind parameters
        // We need to determine the types (s = string, i = integer, etc.)

        if (!empty($params)) {
            $types = "";
            foreach ($params as $val) {
                $types .= is_int($val) ? "i" : (is_double($val) ? "d" : "s");
            }

            $values = array_values($params);
            $stmt->bind_param($types, ...$values);
        }
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

