<?php
date_default_timezone_set("Asia/Dhaka");
    echo time()."<br>";
    echo "Only day d: " . date("d") . "<br>";
    echo "seven day name D: " . date("D") . "<br>";
    echo "full month in alphabte F: " . date("F") . "<br>";
    echo "month in alphabte First 3 leter M: " . date("M") . "<br>";
    echo "month show number m: " . date("m") . "<br>";
    echo "Full Year show Y: " . date("Y") . "<br>";
    echo "year er last 2 digites y: " . date("y") . "<br>";
    echo "hours 0-to-23 H: " . date("H") . "<br>";
    echo "hours 1-to-12 h: " . date("h") . "<br>";
    echo "minutes i: " . date("i") . "<br>";
    echo "secoend s: " . date("s") . "<br>";
    echo "Today is " . date("Y/m/d") . "<br>";
    echo "Today is " . date("Y.m.d") . "<br>";
    echo "Today is " . date("Y-m-d") . "<br>";
    echo "Today is " . date("l"). "<br>";
    // echo date('l, F j, Y'. "<br>");



    $birthDate = date_create("05-10-2000"); 
    $today = date_create(date("d-m-Y"));
    $diff = date_diff($birthDate, $today);
    echo "আপনার জন্ম থেকে আজ পর্যন্ত মোট দিন: " . $diff->format("%a") . " দিন" . "<br>";
    echo "বয়স: " . $diff->format("%y বছর, %m মাস, %d দিন");
?>