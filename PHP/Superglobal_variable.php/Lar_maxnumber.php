<form method="post">
    First Number: <input type="number" name="num1">;
    Seccond Number: <input type="number" name="num2">;
    Third Number: <input type="number" name="num3">; 
    <input type="submit" name="sub">
</form>

<?php
    if (isset($_POST['sub'])){
        $a =$_POST['num1'];
        $b =$_POST['num2'];
        $c =$_POST['num3'];

        echo ($a>$b) ? (($a>$c) ? $a:$b) : (($b>$c) ? $b:$cm3);
        // if($a>=$b && $a>=$c){
        //     echo "Largest Number:".$a;
        // } elseif($b>=$a && $b>=$c){
        //     echo "Largest Number:".$b;
        // } else {
        //     echo "Largest Number:".$c;
        // }
    }

?>