<?php

class base
{
    public static $name = "Bijoy";
    public static function showName()
    {
        echo self::$name;
    }
}

echo base::$name;

class User
{
    protected static $total_User = 0;
    protected static $userByType = [
        'User' => 0,
        'Admin' => 0,
        'Customer' => 0,
        'Guest' => 0
    ];

    public function __construct()
    {
        static::$total_User++;
        static::$userByType[static::class]++; //count by classes 
        // e.g: if we create-> new User(), 
        // it will change-> 'User' => 1;
    }
    public static function getTotalUsers(): int
    {
        return static::$total_User;
    }
    public static function getCountByType(string $type): int
    {
        return static::$userByType[$type] ?? 0;
    }
    public static function getALLCounts(): array
    {
        return static::$userByType;
    }

}

class Admin extends User
{
    //use static Properties automatically
}
class Customer extends User
{
    // Can override static properties if needed
    protected static $someOtherStatic = 0;
}
class Guest extends User
{
    // No extra code needed - inherits everything
}

// ===== TEST THE SYSTEM =====

echo "=== User Counter System (Static Inheritance) ===\n\n";

new User();
new Admin();
new Customer();
new Guest();

new Admin();     // 2nd Admin
new Customer();  // 2nd Customer

// Access counts WITHOUT creating objects (✅ Static allows this)
echo "Total Users: " . User::getTotalUsers() . "\n";
// Output: Total Users: 6

echo "\nCounts by type:\n";
foreach (User::getAllCounts() as $type => $count) {
    echo "- $type: $count\n";
}
// Output:
// - User: 1
// - Admin: 2
// - Customer: 2
// - Guest: 1

// Can also get specific type count
echo "\nAdmin count: " . User::getCountByType('Admin') . "\n";
// Output: Admin count: 2


abstract class Report
{
    const Report_TYPE = 'Base';

    private static function fromDate($date)
    {
        return date('Y-m-d', strtotime($date));
    }

    public static function generate()
    {
        return [
            'type' => static::Report_TYPE,
            'date' => self::fromDate('today'),
            'data' => static::fetchData()
        ];
    }
    // Child must implement this
    abstract protected static function fetchData();
}

class salesReport extends Report
{
    const Report_TYPE = 'Sales'; // Overrides constant
    protected static function fetchData()
    {
        return ['sales' => 1000]; //implements abstract
    }
}

echo salesReport::generate()['type'];

?>