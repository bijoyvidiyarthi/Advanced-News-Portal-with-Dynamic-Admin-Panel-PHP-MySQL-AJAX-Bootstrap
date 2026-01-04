<?php

class Database
{
    // Private properties to store database connection details
    private $db_host = "localhost";
    private $db_user = "root";
    private $db_pass = "";
    private $db_name = "test1";

    //Properties for Pagination
    public $mysqli = null;
    private $resulterr = array();
    private $conn2 = false;

    //Properties for Pagination
    private $limit;
    private $offset;
    private $page;

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

    //method to set pagination
    public function setPagination($limit)
    {
        $this->limit = (int) $limit;
        $this->page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        if ($this->page < 1)
            $this->page = 1;

        //Calculate Offset
        $this->offset = ($this->page - 1) * $this->limit;

        //return the offset
        return $this->offset;

    }

    //select method
    public function select(string $table, string $columns = "*", $join = null, ?string $where = null, $order = null, $usePagination = false)
    {
        //check if table is exist in DB
        if ($this->isTableExist($table)) {

            $sql = "SELECT $columns FROM $table";

            if ($join != null) {
                $sql .= " $join";
            }

            //if Where is not null, append it in SQL command
            if ($where != null) {
                $sql .= " WHERE $where";
            }

            if ($order != null) {
                $sql .= " ORDER BY  $order";
            }

            if ($usePagination == true && isset($this->limit)) {
                $sql .= " LIMIT $this->limit OFFSET $this->offset";
            }
            return $this->show($sql);
        } else {
            return false;
        }
    }


    //pagination method 
    public function pagination(string $table, $join = null, ?string $where = null)
    {
        if (!$this->isTableExist($table) || !isset($this->limit))
            return false;

        //---Count Total Students---
        $sql = "SELECT COUNT(id) AS total_students FROM $table";
        if ($join != null) {
            $sql .= " $join";
        }
        if ($where != null) {
            $sql .= " WHERE $where";
        }

        //---Count Total Pages----
        $query = $this->mysqli->query($sql);


        // Check if query actually succeeded before fetching
        if (!$query) {
            throw new Exception("Pagination Count Query Failed: " . $this->mysqli->error);
        }
        $Countresult = $query->fetch_assoc();
        $TotalStudents = $Countresult['total_students'];
        $total_Pages = ceil($TotalStudents / $this->limit);



        //pagination View
        if ($total_Pages > 1):
            //Previous Page
            if ($this->page > 1):
                echo '<li class="prev"><a href=" ' . $_SERVER['PHP_SELF'] . '?page=' . ($this->page - 1) . ' " >Prev</a></li>';
            endif;

            //Show Page numbers
            $range = 2;
            //eg. if current page is 3, start will 3-2 = 1 (pagination: ..12 3 45..)
            $start = max(1, $this->page - $range);
            $end = min($total_Pages, $this->page + $range);

            // Start Ellipsis
            if ($start > 1):
                echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?page=1">1</a></li>';
                if ($start > 2)
                    echo '<li><span>...</span></li>';
            endif;

            // Truncation logic (first page and ellipsis)
            for ($i = $start; $i <= $end; $i++):
                $active = ($i == $this->page) ? "active" : "";
                echo '<li class=" ' . $active . ' "><a href=" ' . $_SERVER['PHP_SELF'] . '?page=' . $i . ' " >' . $i . '</a></li>';
            endfor;


            // End Ellipsis
            if ($end < $total_Pages):
                if ($end < $total_Pages - 1)
                    echo '<li><span>...</span></li>';
                echo '<li><a href="' . $_SERVER['PHP_SELF'] . '?page=' . $total_Pages . '">' . $total_Pages . '</a></li>';
            endif;

            //next page
            if (1 < $total_Pages && $this->page < $total_Pages):
                echo '<li class="next"><a href=" ' . $_SERVER['PHP_SELF'] . '?page=' . ($this->page + 1) . ' " >Next</a></li>';
            endif;
        else:
            echo "<p> All Records Are Shown</p>";
        endif;
    }

    public function show($sql)
    {
        $query = $this->mysqli->query($sql);

        if ($query) {
            $this->resulterr = $query->fetch_all(MYSQLI_ASSOC);
            return true;
        }
        return false;
    }


    public function insert(string $table, $params = array())
    {
        if (!$this->isTableExist($table))
            return false;

        $table_columns = implode(', ', array_keys($params));
        $table_values = implode("', '", array_values($params));

        $sql = "INSERT INTO $table($table_columns) VALUES ('$table_values')";

        if ($this->mysqli->query($sql)) {
            array_push($this->resulterr, "Successfully Inserted Data");
            return true;
        } else {
            throw new Exception("Data Not Inserted ||| " . $this->mysqli->error);
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
    public function delete(string $table, ?string $where = null)
    {
        //check if table is exist in DB
        if ($this->isTableExist($table)) {
            $sql = "DELETE FROM $table";

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
        return ($checkResult && $checkResult->num_rows > 0);
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