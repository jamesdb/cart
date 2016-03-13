<?php

namespace jamesdb\Cart\Storage;

use Aura\Session\Session;

class AuraSessionDriver implements StorageInterface
{
    /**
     * @var \Aura\Session\Session
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param \Aura\Session\Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session->getSegment('jamesdb\Cart');
    }

    /**
     * {@inheritdoc}
     */
    public function get($identifier)
    {
        return $this->session->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function store($identifier, $data)
    {
        $this->session->set($identifier, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function clear($identifier)
    {
        $this->session->clear();
    }
}
