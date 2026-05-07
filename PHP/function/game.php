<?php
session_start(); 

$min = 1;
$max = 10;
$message = "";

if (!isset($_SESSION['randomNumber'])) {
    $_SESSION['randomNumber'] = rand($min, $max);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['guess'])) {
        $userNumber = (int)$_POST['n1'];
        $correctNumber = $_SESSION['randomNumber'];

        if ($userNumber == $correctNumber) {
            $message = "<b style='color:green;'>Congratulations!</b>";
            session_destroy();
        } else {
            $message = "<b style='color:red;'>Not Correct! Try again.</b>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<body>
    <h2>Math Magic Game</h2>
    <p>Guess the number between <?php echo "$min and $max"; ?></p>

    <form method="post">
        <?php if (isset($_SESSION['randomNumber'])): ?>
            <input type="number" name="n1" placeholder="Enter your Number" required>
            <button type="submit" name="guess">Submit</button>
        <?php else: ?>
            <button type="submit">Play Again</button>
        <?php endif; ?>
    </form>

    <p><?php echo $message; ?></p>
</body>
</html>
