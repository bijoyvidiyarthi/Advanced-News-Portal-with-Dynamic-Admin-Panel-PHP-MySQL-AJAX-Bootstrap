<?php

class Database
{
    // Private properties to store database connection details
    private $db_host = "localhost";
    private $db_user = "root";
    private $db_pass = "";
    private $db_name = "test1";

    public $mysqli = null;
    private $resulterr = array();
    private $conn2 = false;

    // Method to establish and return a database connection
    public function __construct()
    {
        if (!$this->conn2) {
            $this->mysqli = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
            $this->conn2 = true;

            if ($this->mysqli->connect_error) {
                array_push($this->resulterr, $this->mysqli->connect_error);
                $this->conn2 = false;

                throw new Exception("Connection failed: " . $this->mysqli->connect_error);
            } else {
                $this->conn2 = true;
            }
        }
    }

    public function insert(string $table, $params = array())
    {
        if ($this->isTableExist($table)) {

            $table_columns = implode(', ', array_keys($params));
            $table_values = implode("', '", array_values($params));

            $sql = "INSERT INTO $table($table_columns) VALUES ('$table_values')";

            if ($this->mysqli->query($sql)) {
                array_push($this->resulterr, "Successfully Inserted Data");
                return true;
            } else {
                throw new Exception("Data Not Inserted ||| " . $this->mysqli->error);
            }

        } else {
            throw new Exception("Table '$table' does not exist in the database.");
        }
    }
    public function select(string $table, string $column = "*", ?string $where = null, $params = [])
    {
        //check if table is exist in DB
        if ($this->isTableExist($table)) {

            $sql = "SELECT $column FROM $table";

            //if Where is not null, append it in SQL command
            if ($where != null) {
                $sql .= " WHERE $where";
            }

            if ($this->mysqli->query($sql)) {
                return true;
            } else {
                throw new Exception("Data Not Found ||| " . $this->mysqli->error);
            }

        } else {
            throw new Exception("Table '$table' does not exist in the database.");
        }
    }
    public function update(string $table, ?string $where = null, $params = [])
    {
        //check if table is exist in DB
        if ($this->isTableExist($table)) {
            $data = array();
            foreach ($params as $key => $value) {
                $data[] = "$key = '$value'";
            }

            //Run Update SQL
            $sql = "UPDATE $table SET " . implode(', ', $data);

            //if Where is not null, append it in SQL command
            if ($where != null) {
                $sql .= " WHERE $where";
            }

            if ($this->mysqli->query($sql)) {
                array_push($this->resulterr, $this->mysqli->affected_rows);
                return true;
            } else {
                throw new Exception("Data Not Inserted ||| " . $this->mysqli->error);
            }

        } else {
            throw new Exception("Table '$table' does not exist in the database.");

        }

    }
    public function delete(string $table, string $column = "*", ?string $where = null)
    {
        //check if table is exist in DB
        if ($this->isTableExist($table)) {
            $sql = "DELETE $column FROM $table";

            //if Where is not null, append it in SQL command
            if ($where != null) {
                $sql .= " WHERE $where";
            }

            if ($this->mysqli->query($sql)) {
                array_push($this->resulterr, $this->mysqli->affected_rows);
                return true;
            } else {
                throw new Exception("Data Cannot be Delete ||| " . $this->mysqli->error);
            }

        } else {
            throw new Exception("Table '$table' does not exist in the database.");
        }

    }

    public function isTableExist(string $table)
    {
        //check connection first
        if (!$this->conn2)
            return false;

        $checkSql = "SHOW TABLES FROM $this->db_name LIKE '$table'";
        $checkResult = $this->mysqli->query($checkSql); // in Raw PHP: $conn->query($sql)

        //check if table exist or not
        if ($checkResult && $checkResult->num_rows > 0) {
            return true;
        } else {
            array_push($this->resulterr, $table . "Doesn't Exist");
            return false;
        }
    }

    public function getResult()
    {
        $val = $this->resulterr;
        $this->resulterr = array();
        return $val;
    }

    //escape data
    public function escapeString($data)
    {
        return $this->mysqli->real_escape_string($data);
    }

    public function __destruct()
    {
        if ($this->conn2) {
            if ($this->mysqli->close()) {
                $this->conn2 = false;
            }

        }
    }

}