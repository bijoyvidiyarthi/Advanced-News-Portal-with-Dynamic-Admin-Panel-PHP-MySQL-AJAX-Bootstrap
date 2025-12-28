<?php
/*
Program: Zoo Animals System with Abstract Class
Problem Statement
Create a simple zoo system where:

1. All Animals make sounds, but each Animals type makes a different sound
2. All Animals can eat, but some eat differently
3. Each Animals must report its species and habitat

*/

abstract class Animals
{
    //properties available to call classes
    protected $name;
    protected $species;

    //constructor (Sets up basic Info)
    public function __construct($name, $species)
    {
        $this->name = $name;
        $this->species = $species;
    }

    //Concrete Method (already implemented, can be used by childrens)
    public function eat()
    {
        return $this->name . "is eating Food \n";
    }

    //abstract function (must be implemented by child classes)
    abstract public function makeSound();
    //another abstract function (must be implemented by child classes)
    abstract public function getHabitat();

    // Method to get Animals info (uses both concrete and abstract methods)
    public function getInfo()
    {
        return "Name: {$this->name} \n" .
            "Species: {$this->species}\n" .
            "Habitat: {$this->getHabitat()}\n" .
            "Sound: {$this->makeSound()} \n";
    }

}

class Lion extends Animals
{
    public function makeSound()
    {
        return "ROAR! 🦁 \n";
    }
    public function getHabitat()
    {
        return "Savanna \n";
    }

    // Can also override the eat() method if needed
    public function eat()
    {
        return parent::eat() . " (Lions eat meat!) \n";
    }
}

// Elephant class
class Elephant extends Animals
{
    public function makeSound()
    {
        return "TRUMPET! 🐘 \n";
    }

    public function getHabitat()
    {
        return "Forest \n";
    }

    // Adding a special method only for elephants
    public function useTrunk()
    {
        return "{$this->name} is using its trunk to grab food.\n";
    }
}

// Dolphin class
class Dolphin extends Animals
{
    public function makeSound()
    {
        return "CLICK-SQUEAK! 🐬\n";
    }

    public function getHabitat()
    {
        return "Ocean \n";
    }

    // Override eat with different behavior
    public function eat()
    {
        return "{$this->name} is catching fish in the ocean! \n";
    }
}


//create our zoo Animals

$Animals = [
    new Lion("Simba", "African Lion"),
    new Elephant("Dumbo", "African Elephant"),
    new Dolphin("Flipper", "Bottlenose Dolphin"),
];

//Display info about each animal
foreach ($Animals as $animal) {
    echo "{$animal->getInfo()} \n";
    echo "{$animal->eat()} \n";

    // Check for special methods
    if ($animal instanceof Elephant) {
        echo "\n {$animal->useTrunk()} \n";
    }

    echo "\n";
}


abstract class BaseNotifier
{
    //public function 
    abstract public function send(string $message): string;

    //protected method (for child class)
    protected function log(string $message)
    {
        echo "Logging: " . $message . "\n";
    }
    private function validate(string $message): bool
    {

        return !empty($message) ? is_string($message) : false;

    }

    //public action using private helper
    public function proccess(string $message): string
    {
        if ($this->validate($message)) {
            $this->log("Proccessing: $message");
            return $this->send($message);
        }
        return "Invalid Message";
    }
}

class EmailNotifier extends BaseNotifier
{
    public function send(string $message): string
    {
        // Can use parent's protected log() method
        $this->log("Sending email");
        return "Email: $message";
    }
    // CANNOT access parent's private validate() directly
}

$notifier = new EmailNotifier();
echo "\n\n";
$message = $notifier->proccess("I love You !!!!!!!");
echo $message;
echo "\n\n";
?>