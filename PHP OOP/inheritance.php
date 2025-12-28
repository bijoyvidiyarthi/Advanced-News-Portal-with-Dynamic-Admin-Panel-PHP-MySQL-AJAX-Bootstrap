<?php
//parent class
class Animal
{
    public string $name;
    public string $age;

    function __construct($name, $age)
    {
        $this->name = $name;
        $this->age = $age;
    }
    public function eat()
    {
        return "{$this->name} is eating";
    }
    public function sleep()
    {
        return "{$this->name} is sleeping";
    }

}
//child classs (Specific)
class cat extends Animal
{ //extends meand inharit from Animal class
    public $breed;

    public function meow()
    {
        return "{$this->name} says Meow!!";
    }
    public function purr()
    {
        return "{$this->name} is purring ....!!";
    }
}

//creating object
$genericAnimal = new Animal("Sheshu", "3");
$myCat = new cat("Sheshika", "1");


//using methods
echo $genericAnimal->eat() . "\n";  // "Generic Animal is eating"
//inherited methods
echo $myCat->eat() . "\n";          // "Whiskers is eating" (Inherited!)
echo $myCat->sleep() . "\n";        // "Whiskers is sleeping" (Inherited!)
echo "Cat's age is: ". $myCat->age. "\n"; 

//own methods
echo $myCat->meow() . "\n";         // "Whiskers says: Meow!" (Cat's own method)
echo $myCat->purr() . "\n";         // "Whiskers is purring..." (Cat's own method)

?>