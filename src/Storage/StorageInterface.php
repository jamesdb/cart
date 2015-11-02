<?php

namespace jamesdb\Cart\Storage;

interface StorageInterface
{
    /**
     * Return the Cart.
     *
     * @param  string $identifier
     *
     * @return array
     */
    public function get($identifier);

    /**
     * Store the Cart.
     *
     * @param  string $identifier
     * @param  array  $data
     *
     * @return void
     */
    public function store($identifier, $data);

    /**
     * Clear the Cart.
     *
     * @param  string identifier
     *
     * @return void
     */
    public function clear($identifier);
}
