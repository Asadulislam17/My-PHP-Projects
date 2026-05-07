<?php
interface StorageInterface {
    public function save(array $data);
    public function getAll();
}
