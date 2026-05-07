<form action="" method="post">
    Name: <input type="text" name="n"> <br> <br>
    G-mail: <input type="mail" name="mail"> <br> <br>
    contact: <input type="number" name="num"> <br> <br>
    <input type="submit" name="submit">
</form>

<?php
if(isset($_POST['submit'])){
    $result1= $_POST['n'];
    $result2 = $_POST["mail"];
    $result3 = $_POST["num"];
    echo $result1;
    echo $result2;
    echo $result3;
}

?>