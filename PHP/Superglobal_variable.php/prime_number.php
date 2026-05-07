<form method="post">
    <label>Enter a Number</label>
    <input type="number" name="num">
    <input type="submit" name="submit">
</form>

<?php
if(isset($_POST['submit'])){

    $num = $_POST['num'];
    $count = 0;
    if($num==0 || $num==1){
        echo $num." is Composite Number";
    }
    else{
        for($i = 2; $i <= $num/2; $i++){
            if($num % $i == 0){
                $count++;
                break;
            }
        }
        if($count == 1){
            echo $num." is not a Prime Number";
        }else{
            echo $num." is a Prime Number";
        }
    }
    

    

}
?>