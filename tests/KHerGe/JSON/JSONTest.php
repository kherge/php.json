<?php

namespace Test\KHerGe\JSON;

use KHerGe\JSON\Exception\Decode\ControlCharacterException;
use KHerGe\JSON\Exception\Decode\DepthException as DecodeDepthException;
use KHerGe\JSON\Exception\Decode\StateMismatchException;
use KHerGe\JSON\Exception\Decode\SyntaxException;
use KHerGe\JSON\Exception\Decode\UTF8Exception;
use KHerGe\JSON\Exception\DecodeException;
use KHerGe\JSON\Exception\Encode\DepthException as EncodeDepthException;
use KHerGe\JSON\Exception\Encode\InfiniteOrNotANumberException;
use KHerGe\JSON\Exception\Encode\RecursionException;
use KHerGe\JSON\Exception\Encode\UnsupportedTypeException;
use KHerGe\JSON\Exception\EncodeException;
use KHerGe\JSON\Exception\LintingException;
use KHerGe\JSON\Exception\ValidationException;
use KHerGe\JSON\JSON;
use PHPUnit\Framework\TestCase;

use function KHerGe\File\remove;
use function KHerGe\File\temp_file;

/**
 * Verifies that the JSON data manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @coversDefaultClass \KHerGe\JSON\JSON
 */
class JSONTest extends TestCase
{
    /**
     * The JSON data manager.
     *
     * @var JSON
     */
    private $json;

    /**
     * The temporary paths.
     *
     * @var string[]
     */
    private $temp = [];

    /**
     * Returns exceptional decoding conditions and expected exceptions.
     *
     * @return array The everything.
     */
    public function getExceptionalDecodingConditions()
    {
        return [

            // #0
            [
                '[[1]]',
                DecodeDepthException::class
            ],

            // #1
            [
                '[1}',
                StateMismatchException::class
            ],

            // #2
            [
                '["' . chr(0) . 'test"]',
                ControlCharacterException::class
            ],

            // #3
            [
                '[',
                SyntaxException::class
            ],

            // #4
            [
                '["' . chr(193) . '"]',
                UTF8Exception::class
            ]

        ];
    }

    /**
     * Returns exceptional encoding conditions and expected exceptions.
     *
     * @return array The everything.
     */
    public function getExceptionalEncodingConditions()
    {
        return [

            // #0
            [
                [[[[[[1]]]]]],
                EncodeDepthException::class
            ],

            // #1
            [
                call_user_func(
                    function () {
                        $value = (object) [];
                        $value->value = $value;

                        return $value;
                    }
                ),
                RecursionException::class
            ],

            // #2
            [
                INF,
                InfiniteOrNotANumberException::class
            ],

            // #3
            [
                STDIN,
                UnsupportedTypeException::class
            ]

        ];
    }

    /**
     * Verify that a JSON encoded value can be decoded.
     *
     * @covers ::decode
     * @covers ::hasError
     */
    public function testDecodeAnEncodedJsonValue()
    {
        self::assertEquals(
            ['test' => 123],
            $this->json->decode('{"test":123}', true),
            'The decoded value was not returned.'
        );
    }

    /**
     * Verify that a decoding error throws the appropriate exception.
     *
     * @param string $json  The invalid JSON value.
     * @param string $class The expected exception class.
     *
     * @covers ::decode
     * @covers ::hasError
     *
     * @dataProvider getExceptionalDecodingConditions
     */
    public function testADecodingErrorThrowsTheAppropriateException(
        $json,
        $class
    ) {
        $this->expectException($class);

        $this->json->decode($json, false, 2);
    }

    /**
     * Verify that a JSON encoded file can be read and decoded.
     *
     * @covers ::decodeFile
     */
    public function testDecodeAJsonEncodedFile()
    {
        $this->temp[] = $path = temp_file();

        file_put_contents($path, '{"test":123}');

        self::assertEquals(
            ['test' => 123],
            $this->json->decodeFile($path, true),
            'The decoded value was not returned.'
        );
    }

    /**
     * Verify that a a decoding error for a file includes the path in the exception.
     *
     * @covers ::decodeFile
     */
    public function testADecodingErrorThrowsAnExceptionWithTheFilePathInIt()
    {
        $path = '/this/path/should/not/exist.json';

        $this->expectException(DecodeException::class);
        $this->expectExceptionMessageRegExp(
            "#$path#"
        );

        $this->json->decodeFile($path);
    }

    /**
     * Verify that a value can be encoded.
     *
     * @covers ::encode
     * @covers ::hasError
     */
    public function testEncodeANativeValueIntoJson()
    {
        self::assertEquals(
            '{"test":123}',
            $this->json->encode(['test' => 123]),
            'The encoded value was not returned.'
        );
    }

    /**
     * Verify that an encoding error throws the appropriate exception.
     *
     * @param mixed  $value The unencodable value.
     * @param string $class The expected exception class.
     *
     * @covers ::encode
     * @covers ::hasError
     *
     * @dataProvider getExceptionalEncodingConditions
     */
    public function testAnEncodingErrorThrowsTheAppropriateException(
        $value,
        $class
    ) {
        $this->expectException($class);

        $this->json->encode($value, 0, 5);
    }

    /**
     * Verify that a value can be encoded and saved to a file.
     *
     * @covers ::encodeFile
     */
    public function testEncodeANativeValueAndSaveItToAFile()
    {
        $this->temp[] = $path = temp_file();

        $this->json->encodeFile(['test' => 123], $path);

        self::assertEquals(
            '{"test":123}',
            file_get_contents($path),
            'The encoded value was not written to a file.'
        );
    }

    /**
     * Verify that a a encoding error for a file includes the path in the exception.
     *
     * @covers ::encodeFile
     */
    public function testAEncodingErrorThrowsAnExceptionWithTheFilePathInIt()
    {
        $path = '/this/path/should/not/exist.json';

        $this->expectException(EncodeException::class);
        $this->expectExceptionMessageRegExp(
            "#$path#"
        );

        $this->json->encodeFile(STDIN, $path);
    }

    /**
     * Verify that a failed lint throws an exception.
     *
     * @covers ::doLint
     * @covers ::lint
     */
    public function testFailingLintingThrowsAnException()
    {
        $this->expectException(LintingException::class);

        $this->json->lint('{');
    }

    /**
     * Verify that a failed file lint throws an exception with a path in it.
     *
     * @covers ::doLint
     * @covers ::lintFile
     */
    public function testFailingFileLintingThrowsAnException()
    {
        $this->temp[] = $path = temp_file();

        file_put_contents($path, '{');

        $this->expectException(LintingException::class);
        $this->expectExceptionMessageRegExp("#$path#");

        $this->json->lintFile($path);
    }

    /**
     * Verify that a failed validation throws an exception.
     *
     * @covers ::validate
     */
    public function testFailingValidationThrowsAnException()
    {
        $schema = (object) [
            'type' => 'object',
            'properties' => (object) [
                'test' => (object) [
                    'description' => 'A test value.',
                    'type' => 'integer'
                ]
            ]
        ];

        $this->expectException(ValidationException::class);

        $this->json->validate($schema, (object) ['test' => '123']);
    }

    /**
     * Verify that a successful validation does not throw an exception.
     *
     * @covers ::validate
     */
    public function testSuccessfulValidationDoesNotThrowAnException()
    {
        $schema = (object) [
            'type' => 'object',
            'properties' => (object) [
                'test' => (object) [
                    'description' => 'A test value.',
                    'type' => 'integer'
                ]
            ]
        ];

        $this->json->validate($schema, (object) ['test' => 123]);

        self::assertTrue(true);
    }

    /**
     * Creates a new JSON data manager.
     */
    protected function setUp() : void
    {
        $this->json = new JSON();
    }

    /**
     * Deletes the temporary paths.
     */
    protected function tearDown() : void
    {
        foreach ($this->temp as $path) {
            if (file_exists($path)) {
                remove($path);
            }
        }
    }
}
