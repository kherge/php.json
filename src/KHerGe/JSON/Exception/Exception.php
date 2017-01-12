<?php

namespace KHerGe\JSON\Exception;

/**
 * Serves as the base exception class for the library.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Exception extends \Exception
{
    /**
     * Initializes the new exception.
     *
     * @param string     $format    The message format.
     * @param mixed      $value,... A value to format.
     * @param \Exception $previous  The previous exception.
     */
    public function __construct($format = '', ...$value)
    {
        $previous = (end($value) instanceof \Exception)
            ? array_pop($value)
            : null;

        parent::__construct(vsprintf($format, $value), 0, $previous);
    }
}
