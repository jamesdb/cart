<?php

namespace jamesdb\Cart\Storage;

class NativeSessionDriver implements StorageInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($identifier)
    {
        return isset($_SESSION[$identifier]) ? $_SESSION[$identifier] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function store($identifier, $data)
    {
        $_SESSION[$identifier] = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($identifier)
    {
        unset($_SESSION[$identifier]);
    }
}
