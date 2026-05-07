<?php
    class Student{
        public $name;
        public $roll;
        function output($n,$r){
            $this->name=$n;
            $this->roll=$r;
            echo "Your name: " . $this->name . " | Roll: " . $this->roll."<br>";
        }
    }
    $student=new Student();
    $student->output("Asadul Islam", 1295322);
    $student->output("Rakibul Islam", 1295367);
    class Car{
        public $name;
        public $color;
        public $model;

        public function __construct($n, $c, $m){
            $this->name=$n;
            $this->color=$c;
            $this->model=$m;
            $this->start();
            $this->revarce();
            $this->break1();
            $this->stop();
        }
        public function start(){
            
            echo $this->name." is Start! and Color is: ".$this->color." and Model is: ".$this->model."<br>";
        }
        public function revarce(){
            
            echo $this->name." is Revarce! and Color is: ".$this->color." and Model is: ".$this->model."<br>";
            // echo $this->name." is Revarce!<br>";
        }
        public function break1(){
            
            echo $this->name." is Break! and Color is: ".$this->color." and Model is: ".$this->model."<br>";
            // echo $this->name." is Break!<br>";
        }
        public function stop(){
            
            echo $this->name." is Stop! and Color is: ".$this->color." and Model is: ".$this->model."<br>";
            // echo $this->name." is Stop!<br>";
        }

    }
    $car=new Car("BMW", "Red", "Tr123");
   // $car->start();
    // $car->revarce();
    // $car->break1();
    // $car->stop();
    
?>
