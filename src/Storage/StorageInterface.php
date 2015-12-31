<?php

namespace jamesdb\Cart\Storage;

interface StorageInterface
{
    /**
     * Return stored data.
     *
     * @param  string $identifier
     *
     * @return array
     */
    public function get($identifier);

    /**
     * Store data.
     *
     * @param  string $identifier
     * @param  array  $data
     *
     * @return void
     */
    public function store($identifier, $data);

    /**
     * Clear data.
     *
     * @param  string identifier
     *
     * @return void
     */
    public function clear($identifier);
}
