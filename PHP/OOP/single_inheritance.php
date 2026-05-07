<?php  
class Animal {  
    private $atmPin = "1234"; 

 
     public function setPin($pin) {
         $this->atmPin = $pin;
    }

    
    public function getPin() {
        return $this->atmPin;
    }

    public function makeSound() {  
        echo "Animals make sound<br>"; 
    }  
}  

class Dog extends Animal {  
    public function bark() {  
        echo "Dog barks<br>";  
        
        echo "Accessing Private PIN: " . $this->getPin() . "\n";
    }  
}  

$dog = new Dog();  


 $dog->setPin("9999"); 
// echo $dog->getPin(); 

$dog->makeSound();  
$dog->bark();   
?>
