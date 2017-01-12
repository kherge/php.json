<?php

namespace KHerGe\JSON\Exception\Encode;

use KHerGe\JSON\Exception\EncodeException;

/**
 * An exception that is thrown when a recursive value was encoded.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class RecursionException extends EncodeException
{
}
