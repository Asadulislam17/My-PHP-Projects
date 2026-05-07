<?php
    class Student{
        private $name;
        private $roll;

        // function __construct($n,$r){
        //     $this->name=$n;
        //     $this->roll=$r;
        // }
        public function setName($nam){
            $this->name=$nam;
        }
        public function getName(){
            return $this->name;
        }
        function output($n,$r){
            $this->setName($n);
            $this->roll=$r;
            
            echo "Your name: " . $this->name . " | Roll: " . $this->roll."<br>";
        }
    }
    $student=new Student();
    $student->output("Asadul Islam", 1295322);
    echo $student->getName();
    
?>
