<?php

namespace OptimoleWP\PageProfiler\Storage;

class Transients extends Base {
    const PREFIX = '_oprof_';
    const EXPIRATION_TIME = 7 * DAY_IN_SECONDS;

    private $expiration_time;
    public function __construct() {
        $this->expiration_time = apply_filters('optml_page_profiler_transient_expiration', self::EXPIRATION_TIME);
    }

    private function get_key(string $key){
        return self::PREFIX . $key;
    }
    public function store(string $key, array $data){
        set_transient( $this->get_key($key), $data, $this->expiration_time );
    }

    public function get(string $key){
        return get_transient( $this->get_key($key) );
    }

    public function delete(string $key){
        delete_transient( $this->get_key($key) );
    }
    public function delete_all(){
        // Not implemented for now.
    }
}