<?php
    class Student{
        private $name;
        private $roll;

        function __construct($n,$r){
            $this->name=$n;
            $this->roll=$r;
        }
        function output(){
            
            echo "Your name: " . $this->name . " | Roll: " . $this->roll."<br>";
        }
    }
    $student=new Student("Asadul Islam", 1295322);
    $student->output();
    // $student->output("Rakibul Islam", 1295367);
    
?>
