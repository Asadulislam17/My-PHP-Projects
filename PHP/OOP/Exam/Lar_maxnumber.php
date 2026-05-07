<form method="post">
    First Number: <input type="number" name="num1">
    Second Number: <input type="number" name="num2">
    Third Number: <input type="number" name="num3"> 
    <input type="submit" name="sub">
</form>

<?php
    if (isset($_POST['sub'])){
        $a = $_POST['num1'];
        $b = $_POST['num2'];
        $c = $_POST['num3'];

        $result = ($a > $b) ? (($a > $c) ? $a : $c) : (($b > $c) ? $b : $c);
        
        echo "Largest Number is: " . $result;
    }
?>
