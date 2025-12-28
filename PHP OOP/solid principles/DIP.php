<?php 
/*
Dependency Inversion Principle (DIP)
"Depend upon abstractions, not concretions."

High-level modules should not depend on low-level modules; 
both should depend on abstractions (interfaces). 
This "decouples" your code.

Example: A User class shouldn't directly create a MySQLDatabase object inside it. Instead, it should ask for a DatabaseInterface. This allows you to swap MySQL for MongoDB later without touching the User class.
*/

// Define the abstraction (interface)
interface DatabaseInterface
{
    public function connect(): void;
    public function query(string $sql): array;
    public function disconnect(): void;
}

// Low-level module: MySQL implementation
class MySQLDatabase implements DatabaseInterface
{
    public function connect(): void
    {
        echo "Connecting to MySQL database...\n";
    }

    public function query(string $sql): array
    {
        echo "Executing MySQL query: $sql\n";
        return ["MySQL result for: $sql"];
    }

    public function disconnect(): void
    {
        echo "Disconnecting from MySQL database...\n";
    }
}

// Low-level module: MongoDB implementation
class MongoDBDatabase implements DatabaseInterface
{
    public function connect(): void
    {
        echo "Connecting to MongoDB database...\n";
    }

    public function query(string $sql): array
    {
        echo "Executing MongoDB query: $sql\n";
        return ["MongoDB result for: $sql"];
    }

    public function disconnect(): void
    {
        echo "Disconnecting from MongoDB database...\n";
    }
}

// High-level module: User class depends on abstraction
class User
{
    private DatabaseInterface $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function getUserData(int $userId): array
    {
        $this->database->connect();
        $result = $this->database->query("SELECT * FROM users WHERE id = $userId");
        $this->database->disconnect();
        return $result;
    }
}

// Usage: Dependency injection allows swapping databases
echo "Using MySQL:\n";
$userWithMySQL = new User(new MySQLDatabase());
$userWithMySQL->getUserData(1);

echo "\nUsing MongoDB:\n";
$userWithMongo = new User(new MongoDBDatabase());
$userWithMongo->getUserData(1);

// Violation of DIP: High-level module directly depends on low-level module
class UserBad
{
    private $database;

    public function __construct()
    {
        $this->database = new MySQLDatabase(); // Wrong: Direct instantiation of concrete class, violates DIP
    }

    public function getUserData(int $userId): array
    {
        $this->database->connect(); // Wrong: Tightly coupled, can't swap database without modifying UserBad
        $result = $this->database->query("SELECT * FROM users WHERE id = $userId"); // Wrong: Depends on specific implementation
        $this->database->disconnect();
        return $result;
    }
}

// Usage: Can't easily swap databases
echo "\nViolation Example - Only MySQL:\n";
$userBad = new UserBad();
$userBad->getUserData(1);
// To use MongoDB, you'd have to modify UserBad class, breaking OCP and DIP