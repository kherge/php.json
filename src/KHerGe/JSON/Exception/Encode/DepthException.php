<?php

namespace KHerGe\JSON\Exception\Encode;

use KHerGe\JSON\Exception\DecodeException;

/**
 * An exception that is thrown when the maximum stack depth is reached.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class DepthException extends DecodeException
{
}
