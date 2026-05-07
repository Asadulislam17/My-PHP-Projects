<?php
class DataHandler {
    private $filename;

    public function __construct($file) {
        $this->filename = $file;
    }

    public function saveData($name, $email) {
        $data = "Name: $name | Email: $email" . PHP_EOL;
        file_put_contents($this->filename, $data, FILE_APPEND);
    }

    public function displayData() {
        if (file_exists($this->filename)) {
            $lines = file($this->filename);
            foreach ($lines as $line) {
                echo "<li>" . htmlspecialchars($line) . "</li>";
            }
        } else {
            echo "No data found.";
        }
    }
}
?>
