<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POST</title>
</head>
<body>
    <form method="POST" action="">
        <input type="text" name="user_name">
        <button type="submit" value="Submit">Submit</button>
    </form>

    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            
            $name = $_POST['user_name'];

            if (!empty($name)) {
                echo "<h1>Hello, " . htmlspecialchars($name) . "</h1>";
            } else {
                echo "<h1>Please  Enter Your Name</h1>";
            }
        }
        ?>
</body>
</html>