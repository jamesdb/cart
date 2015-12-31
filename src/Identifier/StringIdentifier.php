<?php

namespace jamesdb\Cart\Identifier;

class StringIdentifier implements IdentifierInterface
{
    /**
     * The identifier.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Constructor.
     *
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Return the identifier.
     *
     * @return string
     */
    public function get()
    {
        return $this->identifier;
    }
}
