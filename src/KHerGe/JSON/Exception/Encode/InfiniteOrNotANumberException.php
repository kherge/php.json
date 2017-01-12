<?php

namespace KHerGe\JSON\Exception\Encode;

use KHerGe\JSON\Exception\EncodeException;

/**
 * An exception that is thrown when encoding an INF or NAN value.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class InfiniteOrNotANumberException extends EncodeException
{
}
