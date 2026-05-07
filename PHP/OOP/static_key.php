<?php
  class Hospital {
    
    const NAME = "City General Hospital";

    
    public static $totalPatients = 0;

    public function admitPatient() {
        self::$totalPatients++; 
        echo "Hello";
    }
}

echo Hospital::NAME;

Hospital::$totalPatients = 5; 
echo Hospital::$totalPatients; 
echo Hospital::admitPatient(); 

?>