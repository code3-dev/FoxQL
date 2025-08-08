<?php

declare(strict_types=1);

namespace FoxQL\Core;

/**
 * Raw class for FoxQL
 * 
 * Allows using raw SQL expressions and functions in queries
 */
class Raw
{
    /**
     * The raw SQL expression.
     *
     * @var string
     */
    protected $value;

    /**
     * Create a new Raw instance.
     *
     * @param string $value The raw SQL expression
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Get the raw SQL expression.
     *
     * @return string The raw SQL expression
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Get the string representation of the raw SQL expression.
     *
     * @return string The raw SQL expression
     */
    public function __toString(): string
    {
        return $this->value;
    }
}