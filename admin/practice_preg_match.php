<?php 
//preg_match is used to check pattern matching for username validation

$username = "user_123";
$username_invalid = "user@123";
$username_invalid2 = "user 123";
$username_invalid3 = "USER is invalid123";
$var = " Hello I'm a Trillionaire! ";

//define pattern
$pattern = "/^[a-z0-9_]+$/"; //this pattern allows letters, numbers, and underscores only
                                //here ^ indicates start of string, $ indicates end of string
                                // + indicates one or more occurrences of the preceding element example (a/b)+ means one or more occurrences of a or b like ab, aab, baa, aaa, bbb, abab, etc.
                                //[] indicates a character class eg. [a-z] means any lowercase letter from a to z, here a-z0-9_ means lowercase letters, numbers and underscore

$pattern2 = "/Trillionaire/i"; //this pattern checks for 'php' case insensitive (php/PHP/PhP etc.)   
//check valid username
if (preg_match($pattern, $username)) {
    echo "$username is a valid username.\n";
} else {
    echo "$username is not a valid username.\n";
}     

//check invalid username
if (preg_match($pattern, $username_invalid3)) {
    echo "$username_invalid3 is a valid username.\n";
} else {
    echo "$username_invalid3 is not a valid username.\n";
}
//output: USER is not a valid username.


//check patter2 
if (preg_match($pattern2, $var)) {
    echo "Pattern matched in the string.\n";
} else {
    echo "Pattern not matched in the string.\n";
} 

//with match
$str = "The total cost is $45.99 today.";
$pattern3 = "/\$(\d+\.\d{2})/";     //pattern to match dollar amount like $45.99
                                    //\d+ means one or more digits(48 or 4848484...), \. means literal dot (48.), \d{2} (48.99, here 99) means exactly two digits

                                    //matches is an array that will hold the matched results
                                    //it will contain the full match at index 0 and the captured group (price only) at index 1
                                    // $ is escaped with \ to indicate it's a literal dollar sign
if (preg_match($pattern3, $str, $matches)) {
    echo "Full match: " . $matches[0] . "\n";
    echo "Price only: " . $matches[1];
}

/* Output:
Full match: $45.99
Price only: 45.99
*/                                 
?>