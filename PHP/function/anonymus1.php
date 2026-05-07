<form method="post">
    <input type="number" name="n1" placeholder="Enter Number 1" required>
    <input type="number" name="n2" placeholder="Enter Number 2" required>
    <input type="text" name="op" placeholder="Enter +,-,*,/ symbol" required>
    <button type="submit">Submit</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $num1 = $_POST['n1'];
    $num2 = $_POST['n2'];
    $operator = $_POST['op'];


    $calculate = function($n1, $n2, $op) {
        switch ($op) {
            case "+":
                return $n1 + $n2;
            case "-":
                return $n1 - $n2;
            case "*":
                return $n1 * $n2;
            case "/":
                if ($n2 == 0) return "Cannot divide by zero!";
                return $n1 / $n2;
            default:
                return "Please use +,-,*,/ symbols.";
        }
    };

    $result = $calculate($num1, $num2, $operator);

    echo "<h3>Result: " . $result . "</h3>";
}
?>
