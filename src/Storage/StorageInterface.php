<?php

namespace jamesdb\Cart\Storage;

interface StorageInterface
{
    /**
     * Return the Cart.
     *
     * @param  string $identifer
     *
     * @return array
     */
    public function get($identifer);

    /**
     * Store the Cart.
     *
     * @param  string $identifier
     * @param  array  $data
     *
     * @return void
     */
    public function store($identifer, $data);

    /**
     * Clear the Cart.
     *
     * @param  string identifier
     *
     * @return void
     */
    public function clear($identifier);
}
