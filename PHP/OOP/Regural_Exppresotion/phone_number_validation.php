<?php
$phone_numbers = ['+8801703441733', '8801912345678', '01301234567', '01234567890'];
$pattern = '/^(?:\+88|88)?01[3-9]\d{8}$/';

foreach ($phone_numbers as $number) {
    if (preg_match($pattern, $number)) {
        echo "$number : Valid <br>";
    } else {
        echo "$number : Invalid <br>";
    }
}
?>
