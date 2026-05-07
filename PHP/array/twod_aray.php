<?php
    $arr=[
        [2,5,7,9],
        [6,82,456,1],
        [78,125,566,2]
    ];
    for($i=0; $i<3; $i++){
        echo "Row Number ".$i;
        echo "<ul>";
        for($j=0; $j<4; $j++){
            // echo "<br>";
            if($arr[$i][$j]==1){
                echo "<li>"."Asadul "."</li>";
            }else{
                echo "<li>".$arr[$i][$j]."</li>";
            }
            
        }
        echo "<br>";
        echo "</ul>";
    }
    // foreach ($arr as $row) {
    //     foreach ($row as $value) {
    //        echo $value . ", ";
    //     }
    //     echo "<br>";
    // }
?>