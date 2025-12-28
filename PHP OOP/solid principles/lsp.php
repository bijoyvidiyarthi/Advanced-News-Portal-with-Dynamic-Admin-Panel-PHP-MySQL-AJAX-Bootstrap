<?php

abstract class Shape
{
    abstract public function getArea(): float;
}

abstract class Quadrilateral extends Shape
{
    protected float $width;
    protected float $height;

    public function __construct(float $width, float $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function getArea(): float
    {
        return $this->height * $this->width;
    }
}

class Rectangle extends Quadrilateral
{
    // Inherits everything from Quadrilateral
}

class Rhombus extends Quadrilateral
{
    public function __construct(float $side, float $height)
    {
        parent::__construct($side, $height);
    }
}

class Triangle extends Shape
{
    private float $base;
    private float $height;

    public function __construct(float $base, float $height)
    {
        $this->base = $base;
        $this->height = $height;
    }

    public function getArea(): float
    {
        return 0.5 * $this->base * $this->height;
    }
}

class RightTriangle extends Triangle
{
    public function __construct(float $leg1, float $leg2)
    {
        parent::__construct($leg1, $leg2);
    }
}

class EquilateralTriangle extends Triangle
{
    public function __construct(float $side)
    {
        $height = (sqrt(3) / 2) * $side;
        parent::__construct($side, $height);
    }
}

class Circle extends Shape
{
    private float $radius;

    public function __construct(float $radius)
    {
        $this->radius = $radius;
    }

    public function getArea(): float
    {
        return pi() * $this->radius * $this->radius;
    }
}

/**
 * Square inherits from Rectangle.
 * To respect LSP, we ensure that the Square doesn't provide
 * methods that would break its internal consistency.
 */
class Square extends Rectangle
{
    public function __construct(float $side)
    {
        // We pass the same value to both parent parameters.
        // A square is a rectangle where width == height.
        parent::__construct($side, $side );
    }
}

/**
 * ColoredRectangle inherits from Rectangle and adds a color property.
 * This demonstrates extending the base class without violating LSP.
 */
class ColoredRectangle extends Rectangle
{
    private string $color;

    public function __construct(float $width, float $height, string $color)
    {
        parent::__construct($width, $height);
        $this->color = $color;
    }

    public function getColor(): string
    {
        return $this->color;
    }
}

/**
 * A client function that expects a Shape.
 * This function will work for all shapes because they implement getArea().
 */
function calculateArea(Shape $shape){
    return $shape->getArea();

}


// class Rectangle
// {
//     protected float $width;
//     protected float $height;

//     public function __construct(float $width, float $height)
//     {
//         $this->width = $width;
//         $this->height = $height;
//     }

//     public function getWidth(): float
//     {
//         return $this->width;
//     }

//     public function getHeight(): float
//     {
//         return $this->height;
//     }

//     public function getArea(): float
//     {
//         return $this->width * $this->height;
//     }
// }


// /**
//  * Square inherits from Rectangle.
//  * To respect LSP, we ensure that the Square doesn't provide
//  * methods that would break its internal consistency.
//  */
// class Square extends Rectangle
// {
//     public function __construct(float $side)
//     {
//         // We pass the same value to both parent parameters.
//         // A square is a rectangle where width == height.
//         parent::__construct($side, $side);
//     }
// }

// /**
//  * A client function that expects a Rectangle.
//  * This function will work for both Rectangle and Square because
//  * Square does not change the behavior of width/height logic.
//  */
// function calculateArea(Rectangle $rect): float
// {
//     return $rect->getArea();
// }

// Usage
$rect = new Rectangle(10, 5);
echo "Rectangle Area: " . calculateArea($rect) . "\n"; // 50

$square = new Square(5);
echo "Square Area: " . calculateArea($square) . "\n";   // 25

$coloredRect = new ColoredRectangle(8, 4, 'red');
echo "Colored Rectangle Area: " . calculateArea($coloredRect) . "\n"; // 32
echo "Colored Rectangle Color: " . $coloredRect->getColor() . "\n"; // red

$rhombus = new Rhombus(5, 3);
echo "Rhombus Area: " . calculateArea($rhombus) . "\n"; // 15

$triangle = new Triangle(10, 6);
echo "Triangle Area: " . calculateArea($triangle) . "\n"; // 30

$rightTriangle = new RightTriangle(8, 6);
echo "Right Triangle Area: " . calculateArea($rightTriangle) . "\n"; // 24

$equilateralTriangle = new EquilateralTriangle(10);
echo "Equilateral Triangle Area: " . calculateArea($equilateralTriangle) . "\n"; // ~43.3

$circle = new Circle(7);
echo "Circle Area: " . calculateArea($circle) . "\n"; // ~153.94