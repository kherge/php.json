<?php

namespace Test\KHerGe\JSON\Exception;

use KHerGe\JSON\Exception\Exception;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the base exception class functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @coversDefaultClass \KHerGe\JSON\Exception\Exception
 */
class ExceptionTest extends TestCase
{
    /**
     * Verify that an exception without a message can be instantiated.
     *
     * @covers ::__construct
     */
    public function testCreateAnExceptionWithoutAMessage()
    {
        new Exception();
    }

    /**
     * Verify that the message for a new exception is formatted.
     *
     * @covers ::__construct
     */
    public function testCreateAnExceptionWithAFormattedMessage()
    {
        $exception = new Exception(
            'This is a %s message.',
            'test'
        );

        self::assertEquals(
            'This is a test message.',
            $exception->getMessage(),
            'The message was not formatted correctly.'
        );
    }

    /**
     * Verify that a previous exception can be passed to the new exception.
     *
     * @covers ::__construct
     */
    public function testCreateAnExceptionWithAPreviousException()
    {
        $previous = new \Exception();
        $exception = new Exception(
            'This is a %s message.',
            'test',
            $previous
        );

        self::assertEquals(
            'This is a test message.',
            $exception->getMessage(),
            'The message was not formatted correctly.'
        );

        self::assertSame(
            $previous,
            $exception->getPrevious(),
            'The previous exception was not set correctly.'
        );
    }
}
