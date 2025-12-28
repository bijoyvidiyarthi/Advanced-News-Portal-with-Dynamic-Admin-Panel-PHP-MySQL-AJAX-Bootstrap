<?Php
class Person
{
    public $name;
    function __construct($n)
    {
        $this->name = $n;
    }
    function showName()
    {
        echo "Your Name is: " . $this->name;
    }
}

//The constructor function called automatically and assigned the name value while creating the object
$person1 = new Person("Aryaan Dhar");

$person1->showName();
echo "\n\n";


class Car
{
    public $model;
    public $color;

    function __construct($model, $color)
    {
        $this->model = $model;
        $this->color = $color;
    }

    function details()
    {
        echo "Car Model is: " . $this->model;
        echo "\n";
        echo "Car Color is: " . $this->color;
        echo "\n";
    }
    function start()
    {
        echo "The car Started \n";
    }
}

$car1 = new Car("Volvo", "Red");

$car1->details();

//Create Product Class at first 
class Product
{
    public int $id;
    public string $name;
    public float $price;
    public int $quantity;
    public string $color;
    public string $total_price;
    function __construct($id, $name, $price, $quantity, $color)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->color = $color;
        $this->total_price = $price * $quantity;
    }
    function productDetails()
    {
        echo " Product Id : " . $this->id . "\n";
        echo " Product Title : " . $this->name . "\n";
        echo " Product Price : " . $this->price . "\n";
        echo " Quantity : " . $this->quantity . "\n";
        echo " Product Color : " . $this->color . "\n";
        echo " Total Price :" . $this->total_price . "\n";
    }
}

class Cart
{
    private array $items = [];
    public function addItems(Product ...$products)
    {
        foreach ($products as $product) {
            $this->items[] = $product;
        }
    }

    public function getTotals(): float
    {
        return array_sum(array_column($this->items, "total_price"));
    }

    public function showAllProducts()
    {
        $no = 1;
        foreach ($this->items as $product) {
            echo "Product No: " . $no;
            echo "\n";
            echo "------------------------------\n";
            //I want to call here the productDetails() function from Product class
            $product->productDetails();
            echo "\n";
            $no++;
        }

    }
}

$laptop = new Product("35772", "Laptop", "45000", "2", "silver");
$mobile = new Product("35773", "Mobile", "20000", "1", "black");
$Hoodie = new Product("35774", "Hoodie", "300", "5", "black");

$cart1 = new Cart();

$cart1->addItems($laptop, $mobile, $Hoodie);

$total_price = $cart1->getTotals();
echo "\n";
echo "All Added Products: \n";
$cart1->showAllProducts();
echo "\n\n";
echo "Total Price: " . $total_price;


?>