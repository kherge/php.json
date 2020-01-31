<?php

namespace KHerGe\JSON;

use Exception;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use KHerGe\File\File;
use KHerGe\JSON\Exception\Decode\ControlCharacterException;
use KHerGe\JSON\Exception\Decode\DepthException as DecodeDepthException;
use KHerGe\JSON\Exception\Decode\StateMismatchException;
use KHerGe\JSON\Exception\Decode\SyntaxException;
use KHerGe\JSON\Exception\Decode\UTF8Exception;
use KHerGe\JSON\Exception\DecodeException;
use KHerGe\JSON\Exception\Encode\DepthException as EncodeDepthException;
use KHerGe\JSON\Exception\Encode\InfiniteOrNotANumberException;
use KHerGe\JSON\Exception\Encode\InvalidPropertyNameException;
use KHerGe\JSON\Exception\Encode\RecursionException;
use KHerGe\JSON\Exception\Encode\UnsupportedTypeException;
use KHerGe\JSON\Exception\EncodeException;
use KHerGe\JSON\Exception\LintingException;
use KHerGe\JSON\Exception\UnknownException;
use KHerGe\JSON\Exception\ValidationException;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

/**
 * Manages encoding, decoding, linting, and validation of JSON data.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class JSON implements JSONInterface
{
    /**
     * The JSON linter.
     *
     * @var JsonParser
     */
    private $linter;

    /**
     * {@inheritdoc}
     */
    public function decode(
        $json,
        $associative = false,
        $depth = 512,
        $options = 0
    ) {
        $decoded = json_decode($json, $associative, $depth, $options);

        if ($this->hasError()) {
            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    throw new DecodeDepthException(
                        'The maximum stack depth of %d was exceeded.',
                        $depth
                    );
                case JSON_ERROR_STATE_MISMATCH:
                    throw new StateMismatchException(
                        'The value is not JSON or is malformed.'
                    );
                case JSON_ERROR_CTRL_CHAR:
                    throw new ControlCharacterException(
                        'An unexpected control character was found.'
                    );
                case JSON_ERROR_SYNTAX:
                    throw new SyntaxException(
                        'The encoded JSON value has a syntax error.'
                    );
                case JSON_ERROR_UTF8:
                    throw new UTF8Exception(
                        'The encoded JSON value contains invalid UTF-8 characters.'
                    );
                default:
                    throw new UnknownException(
                        'An unrecognized decoding error was encountered: %s',
                        json_last_error_msg()
                    );
            }
        }

        return $decoded;
    }

    /**
     * {@inheritdoc}
     */
    public function decodeFile(
        $file,
        $associative = false,
        $depth = 512,
        $options = 0
    ) {
        try {
            return $this->decode(
                (new File($file, 'r'))->read(),
                $associative,
                $depth,
                $options
            );
        } catch (Exception $exception) {
            throw new DecodeException(
                'The JSON encoded file "%s" could not be decoded.',
                $file,
                $exception
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function encode($value, $options = 0, $depth = 512)
    {
        $encoded = json_encode($value, $options, $depth);

        if ($this->hasError()) {
            switch (json_last_error()) {
                case JSON_ERROR_DEPTH:
                    throw new EncodeDepthException(
                        'The maximum stack depth of %d was exceeded.',
                        $depth
                    );

                case JSON_ERROR_RECURSION:
                    if (0 === ($options & JSON_PARTIAL_OUTPUT_ON_ERROR)) {
                        throw new RecursionException(
                            'A recursive object was found and partial output is not enabled.'
                        );
                    }

                    break;

                case JSON_ERROR_INF_OR_NAN:
                    if (0 === ($options & JSON_PARTIAL_OUTPUT_ON_ERROR)) {
                        throw new InfiniteOrNotANumberException(
                            'An INF or NAN value was found an partial output is not enabled.'
                        );
                    }

                    break;

                case JSON_ERROR_UNSUPPORTED_TYPE:
                    if (0 === ($options & JSON_PARTIAL_OUTPUT_ON_ERROR)) {
                        throw new UnsupportedTypeException(
                            'An unsupported value type was found an partial output is not enabled.'
                        );
                    }

                    break;

                case JSON_ERROR_INVALID_PROPERTY_NAME:
                    throw new InvalidPropertyNameException(
                        'The value contained a property with an invalid JSON key name.'
                    );

                    break;

                default:
                    throw new UnknownException(
                        'An unrecognized encoding error was encountered: %s',
                        json_last_error_msg()
                    );
            }
        }

        return $encoded;
    }

    /**
     * {@inheritdoc}
     */
    public function encodeFile($value, $file, $options = 0, $depth = 512)
    {
        try {
            (new File($file, 'w'))->write(
                $this->encode($value, $options, $depth)
            );
        } catch (Exception $exception) {
            throw new EncodeException(
                'The value could not be encoded and saved to "%s".',
                $file
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function lint($json)
    {
        $result = $this->doLint($json);

        if ($result instanceof ParsingException) {
            throw new LintingException(
                'The encoded JSON value is not valid.',
                $result
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function lintFile($file)
    {
        $result = $this->doLint((new File($file, 'r'))->read());

        if ($result instanceof ParsingException) {
            throw new LintingException(
                'The encoded JSON value in the file "%s" is not valid.',
                $file,
                $result
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate($schema, $decoded)
    {
        $storage = new SchemaStorage();
        $storage->addSchema('file://schema', $schema);

        $validator = new Validator(new Factory($storage));

        $validator->check($decoded, $schema);

        if ($validator->isValid()) {
            return null;
        }

        $errors = [];

        foreach ($validator->getErrors() as $error) {
            $errors[] = sprintf(
                '[%s] %s',
                $error['property'],
                $error['message']
            );
        }

        if (!empty($errors)) {
            throw new ValidationException(
                "The decoded JSON value failed validation:\n%s",
                join("\n", $errors)
            );
        }
    }

    /**
     * Checks if the last JSON related operation resulted in an error.
     *
     * @return boolean Returns `true` if it did or `false` if not.
     */
    private function hasError()
    {
        return (JSON_ERROR_NONE !== json_last_error());
    }

    /**
     * Actually performs the linting operation.
     *
     * @param string $json The encoded JSON value.
     *
     * @return null|ParsingException The linting error.
     */
    private function doLint($json)
    {
        if (null === $this->linter) {
            $this->linter = new JsonParser();
        }

        return $this->linter->lint($json);
    }
}
