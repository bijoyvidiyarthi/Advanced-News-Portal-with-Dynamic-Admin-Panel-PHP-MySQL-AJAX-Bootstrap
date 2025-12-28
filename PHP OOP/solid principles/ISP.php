<?php 
/*
. Interface Segregation Principle (ISP)
"Clients should not be forced to depend on methods they do not use."

It is better to have many small, specific interfaces than one large, "fat" interface. If an interface has 20 methods but a class only needs 3, that class is forced to implement 17 "empty" methods.
Example: Instead of a Worker interface with work() and eat(), split them into Workable and Eatable interfaces. A robot class would implement Workable but ignore Eatable.
*/
?>