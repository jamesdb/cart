<?php

namespace jamesdb\Cart\Storage;

class NativeSessionDriver implements StorageInterface
{
    /**
     * Return data stored in session.
     *
     * @param  string $identifier
     *
     * @return array
     */
    public function get($identifier)
    {
        return isset($_SESSION[$identifier]) ? $_SESSION[$identifier] : [];
    }

    /**
     * Store data in session.
     *
     * @param  string $identifier
     * @param  array  $data
     *
     * @return void
     */
    public function store($identifier, $data)
    {
        $_SESSION[$identifier] = $data;
    }

    /**
     * Clear data stored in session.
     *
     * @param  string $identifier
     *
     * @return void
     */
    public function clear($identifier)
    {
        unset($_SESSION[$identifier]);
    }
}
