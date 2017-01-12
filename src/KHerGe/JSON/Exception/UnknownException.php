<?php

namespace KHerGe\JSON\Exception;

/**
 * An exception that is thrown if a new type of error is encountered.
 *
 * PHP may introduce new types of errors during the encoding and decoding
 * process. This exception exists to recognize that there is an error with
 * the process, but the nature of the error is not recognized by the library.
 * The exception message and could should match the one provided by the error
 * checker.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class UnknownException extends Exception
{
}
