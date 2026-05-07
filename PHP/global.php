<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<?php
    $x=20;
    static $a=5;
    $a=10;
    echo $a;
     define ("PI", 3.1416);
     echo PI;
    
    function test(){
        global $x;
        
        echo $x;
    }
    test();
    function add(){
        global $x;
        $y=80;
        echo  $x+$y;
    }
    add();

    echo $_GET['user_name']; 

    echo $_POST['user_name'];

    echo $_REQUEST['user_name']; 
?>

    <form method="POST" action="#">
        <input type="text" name="user_name">
        <input type="submit" value="Submit">
    </form>
</body>
</html>
