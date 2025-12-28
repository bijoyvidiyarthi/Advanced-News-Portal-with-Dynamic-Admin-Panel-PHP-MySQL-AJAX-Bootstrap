<?php
// 1. INTERFACE - The Contract
interface Notifier
{
    public function send(string $message): string;
}

// 2. CONCRETE CLASSES - Implement the Interface
class EmailNotifier implements Notifier
{
    public function send(string $message): string
    {
        return "📧 Email sent: " . $message;
    }
}

class SMSNotifier implements Notifier
{
    public function send(string $message): string
    {
        return "📱 SMS sent: " . $message;
    }
}

class SlackNotifier implements Notifier
{
    public function send(string $message): string
    {
        return "💬 Slack message: " . $message;
    }
}

// 3. NOTIFICATION MANAGER - Uses Interface, NOT Concrete Classes
class NotificationManager
{
    private $notifiers = [];

    // Can add ANY notifier that follows the Notifier contract
    public function addNotifier(Notifier $notifier): void
    {
        $this->notifiers[] = $notifier;
    }

    // Send to ALL registered notifiers
    public function broadcast(string $message): array
    {
        $results = [];
        foreach ($this->notifiers as $notifier) {
            $results[] = $notifier->send($message);
        }
        return $results;
    }

    // Get all registered notifiers
    public function getNotifiers(): array
    {
        return $this->notifiers;
    }

    // Send to ONE specific type (polymorphism)
    public function sendVia(string $type, string $message): string
    {
        foreach ($this->notifiers as $notifier) {
            /**
             * Check if the notifier object is an instance of the specified type
             * This validates that the notifier implements or extends the required interface/class
             * 
             * @param object $notifier The object to check
             * @param string $type The class or interface name to validate against
             * @return bool True if $notifier is an instance of $type, false otherwise
             */
            // Verify that the $notifier object implements the expected $type interface or extends the class
            if ($notifier instanceof $type) {
                return $notifier->send($message);
            }
        }
        return "No $type notifier found!";
    }
}

// 4. RUN THE PROGRAM
echo "=== Notification System Demo ===\n\n";

// Setup
$manager = new NotificationManager();

// Add different notifiers
$manager->addNotifier(new EmailNotifier());
$manager->addNotifier(new SMSNotifier());
$manager->addNotifier(new SlackNotifier());

// Test 1: Broadcast to all
echo "Test 1: Broadcast to ALL channels\n";
echo "--------------------------------\n";
$results = $manager->broadcast("Server is back online!");
foreach ($results as $result) {
    echo "- $result\n";
}

// Test 2: Send via specific channel
echo "\nTest 2: Send via SMS only\n";
echo "-------------------------\n";
echo $manager->sendVia('SMSNotifier', "Your OTP is 9876") . "\n";

// Test 3: Add new channel WITHOUT changing existing code
echo "\nTest 3: Adding WhatsAppNotifier dynamically\n";
echo "------------------------------------------\n";

// New channel (added later in development)
class WhatsAppNotifier implements Notifier
{
    public function send(string $message): string
    {
        return "📲 WhatsApp: " . $message;
    }
}

// Simply add it - no other changes needed!
$manager->addNotifier(new WhatsAppNotifier());

// Test the new channel
echo $manager->sendVia('WhatsAppNotifier', "Meeting at 3 PM") . "\n";

// Bonus: Show all available notifiers
echo "\n=== All Available Notifiers ===\n\n";
$no = 1;
foreach ($manager->getNotifiers() as $notifier) {
    echo $no . ". " . get_class($notifier) . "," . "\n";
    $no++;
}
echo "\n\n";
?>