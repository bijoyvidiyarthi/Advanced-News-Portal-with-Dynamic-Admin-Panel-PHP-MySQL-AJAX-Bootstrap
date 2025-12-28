<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> 404 Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }

        h1 {
            font-size: 48px;
            color: #ff0000;
        }

        p {
            font-size: 24px;
        }
    </style>
</head>

<body>
    <!--- redirect this page while find any error for page loading --->
    <h1>404 Error - Page Not Found</h1>
    <?php
    session_start();
    if (isset($_SESSION['error'])) {
        $error_message = htmlspecialchars($_SESSION['error']);
        echo "<p>$error_message</p>";
        unset($_SESSION['error']);
    }
    ?>
</body>

</html>