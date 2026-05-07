<?php
class FileStorage implements StorageInterface {
    private $filename;

    public function __construct($filename) {
        $this->filename = $filename;
    }

    public function save(array $data) {
   
        $line = implode(" | ", $data) . PHP_EOL;
        file_put_contents($this->filename, $line, FILE_APPEND);
    }

    public function getAll() {
        if (!file_exists($this->filename)) {
            return [];
        }
       
        return file($this->filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
}
