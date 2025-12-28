<?php
// BAD: User class doing too many things
class BadUser
{
    private $name;
    private $email;

    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    // Responsibility 1: User data management
    public function getName()
    {
        return $this->name;
    }

    // Responsibility 2: Email validation (should be separate)
    public function validateEmail()
    {
        return filter_var($this->email, FILTER_VALIDATE_EMAIL);
    }

    // Responsibility 3: Database operations (should be separate)
    public function saveToDatabase()
    {
        echo "Saving {$this->name} to database...\n";
        // Database code here
    }

    // Responsibility 4: Email sending (should be separate)
    public function sendWelcomeEmail()
    {
        echo "Sending welcome email to {$this->email}...\n";
        // Email sending code here
    }
}

// Test the bad example
echo "=== BAD EXAMPLE ===\n";
$user = new BadUser("John Doe", "john@example.com");
echo "Name: " . $user->getName() . "\n";
echo "Email valid: " . ($user->validateEmail() ? "Yes" : "No") . "\n";
$user->saveToDatabase();
$user->sendWelcomeEmail();
echo "\n";

// GOOD: Each class has one responsibility

// Responsibility 1: User data only
class User
{
    private $name;
    private $email;

    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }
}

// Responsibility 2: Email validation
class EmailValidator
{
    public function validate($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function getDomain($email)
    {
        $parts = explode('@', $email);
        return $parts[1] ?? null;
    }
}

// Responsibility 3: Database operations
class UserRepository
{
    public function save(User $user)
    {
        echo "Saving {$user->getName()} to database...\n";
        // Database logic here
        return true;
    }

    public function findById($id)
    {
        // Find user logic
        return new User("Jane Doe", "jane@example.com");
    }
}

// Responsibility 4: Email sending
class EmailService
{
    public function sendWelcome($email)
    {
        echo "Sending welcome email to {$email}...\n";
        // Email sending logic here
        return true;
    }

    public function sendNotification($email, $message)
    {
        echo "Sending notification to {$email}: {$message}\n";
        return true;
    }
}

// Test the good example
echo "=== GOOD EXAMPLE ===\n";

// Create user
$user = new User("John Doe", "john@example.com");

// Validate email
$validator = new EmailValidator();
$isValid = $validator->validate(email: $user->getEmail());
echo "Email valid: " . ($isValid ? "Yes" : "No") . "\n";
echo "Email domain: " . $validator->getDomain($user->getEmail()) . "\n";

// Save to database
$repository = new UserRepository();
$repository->save($user);

// Send email
$emailService = new EmailService();
$emailService->sendWelcome($user->getEmail());

// Reuse EmailService for another purpose
$emailService->sendNotification($user->getEmail(), "Your account was created!");

echo "\n=== REAL-WORLD EXAMPLE ===\n";

// Real-world scenario: Order processing
class Order
{
    private $id;
    private $total;
    private $items;

    public function __construct($id, $total, $items)
    {
        $this->id = $id;
        $this->total = $total;
        $this->items = $items;
    }

    public function getId()
    {
        return $this->id;
    }
    public function getTotal()
    {
        return $this->total;
    }
    public function getItems()
    {
        return $this->items;
    }
}

class OrderCalculator
{
    public function calculateTax(Order $order, $taxRate)
    {
        return $order->getTotal() * $taxRate;
    }

    public function applyDiscount(Order $order, $discountPercent)
    {
        return $order->getTotal() * (1 - $discountPercent / 100);
    }
}

class OrderPrinter
{
    public function printReceipt(Order $order)
    {
        echo "\n=== RECEIPT ===\n";
        echo "Order ID: {$order->getId()}\n";
        echo "Items: " . implode(', ', $order->getItems()) . "\n";
        echo "Total: $" . number_format($order->getTotal(), 2) . "\n";
    }

    public function printInvoice(Order $order, $tax)
    {
        $this->printReceipt($order);
        echo "Tax: $" . number_format($tax, 2) . "\n";
        echo "Grand Total: $" . number_format($order->getTotal() + $tax, 2) . "\n";
    }
}

// Usage
$order = new Order(123, 99.99, ["Book", "Pen", "Notebook"]);
$calculator = new OrderCalculator();
$printer = new OrderPrinter();

$tax = $calculator->calculateTax($order, 0.08);
$printer->printInvoice($order, $tax);

echo "\nAfter 10% discount: $" .
    number_format($calculator->applyDiscount($order, 10), 2) . "\n";
?>