<?php
// --- 1. Base Class: LibraryItem ---
/**
 * Base class for all items in the library.
 * This class introduces common properties and the polymorphic method.
 */

class LibraryItem
{
    protected $title;
    protected int $item_id;

    public function __construct($title, $item_id)
    {
        $this->title = $title;
        $this->item_id = $item_id;
    }

    //the polymorphic method
    public function displayInfo(): void
    {
        echo "--Item ID: {$this->item_id} ----\n";
        echo "Title: {$this->title}\n";

    }
}
// --- 2. Derived Class: Book ---
/**
 * Represents a Book item, inheriting from LibraryItem.
 */

class Book extends LibraryItem
{
    private string $author;
    private string $isbn; // International Standard Book Number

    public function __construct(string $title, int $item_id, string $author, string $isbn)
    {
        parent::__construct($title, $item_id); // Call the parent constructor to initialize common properties
        $this->author = $author;
        $this->isbn = $isbn;
    }
    // Overriding the base method (Polymorphism)
    public function displayInfo(): void
    {
        parent::displayInfo(); // Call the base method to display common info
        echo "Type: Book\n";
        echo "Author: {$this->author}\n";
        echo "ISBN: {$this->isbn}\n";
    }

}

// --- 3. Derived Class: DVD ---
/**
 * Represents a DVD item, inheriting from LibraryItem.
 */
class DVD extends LibraryItem
{
    private string $director;
    private int $duration; // Duration in minutes

    public function __construct(string $title, int $item_id, string $director, int $duration)
    {
        parent::__construct($title, $item_id); // Call the parent constructor to initialize common properties
        $this->director = $director;
        $this->duration = $duration;
    }
    // Overriding the base method (Polymorphism)
    public function displayInfo(): void
    {
        parent::displayInfo(); // Call the base method to display common info
        echo "Type: DVD\n";
        echo "Director: {$this->director}\n";
        echo "Duration: {$this->duration} minutes\n";
    }
}

// --- 4. Catalog Class ---
/**
 * Manages the collection of LibraryItem objects.
 */
class Catalog
{
    /** @var LibraryItem[] */
    private array $items = [];

    //use type hinting (LibraryItem) to ensure only valid items are added
    //here LibaryItem $item is written to accept only any derived class object (Book, DVD, etc)
    public function addItem(LibraryItem $item): void {
        $this->items[] = $item;
        echo "Added  '{$item->displayInfo()}' to the Catalog. \n"; // call the polymorphic method to display item info
    }

    public function displayCatalog(): void {
        echo "---- Library Catalog ----\n";
        if(empty ($this->items)) {
            echo "The catalog is empty.\n";
            return;
        }
        foreach ($this->items as $item){
            $item->displayInfo();
            echo "------------------------\n";
        }
    }
}

// --- Usage Example ---

// 1. Create the Catalog manager
$catalog = new Catalog();

// 2. Create instances of the derived classes
$book1 = new Book(
    "The Hitchhiker's Guide to the Galaxy", 
    101, 
    "Douglas Adams", 
    "978-0345391803"
);

$dvd1 = new DVD(
    "Inception", 
    205, 
    "Christopher Nolan", 
    148
);

$book2 = new Book(
    "Pride and Prejudice", 
    102, 
    "Jane Austen", 
    "978-0141439518"
);

// 3. Add them to the catalog
$catalog->addItem($book1);
$catalog->addItem($dvd1);
$catalog->addItem($book2);

// 4. Display the entire catalog
$catalog->displayCatalog();

?>