<?php

namespace OptimoleWP\PageProfiler\Storage;

abstract class Base {
    
    abstract public function store(string $key, array $data);
    abstract public function get(string $key);
    abstract public function delete(string $key);
    abstract public function delete_all();
}