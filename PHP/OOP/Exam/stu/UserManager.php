<?php
class Student {
    private $storage;

    public function __construct(StorageInterface $storage) {
        $this->storage = $storage;
    }

    public function addUser($id, $name, $batch) {
        $userData = [
            'id' => $id,
            'name' => $name,
            'batch' => $batch
        ];
        $this->storage->save($userData);
    }

    public function result() {
        return $this->storage->getAll();
    }

    public function re($id) {
    $filename = 'users.txt';
    if (!file_exists($filename)) return null;

    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        
        $data = explode('|', $line); 
        
    
        if (trim($data[0]) == trim($id)) {
            return $line;
        }
    }
    return null;
}

}
