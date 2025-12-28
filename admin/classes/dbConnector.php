<?php 
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
