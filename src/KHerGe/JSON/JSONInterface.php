<?php

namespace KHerGe\JSON;

use KHerGe\JSON\Exception\DecodeException;
use KHerGe\JSON\Exception\EncodeException;
use KHerGe\JSON\Exception\LintingException;
use KHerGe\JSON\Exception\UnknownException;
use KHerGe\JSON\Exception\ValidationException;

/**
 * Defines the public interface for a JSON data manager.
 *
 * The JSON data manager is responsible for processing JSON data from strings
 * and files. Its purpose is to provide a simplified interface to functions that
 * are already offered by either PHP or third-party libraries. By implementing
 * an interface, the implementation can be swapped if needed.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
interface JSONInterface
{
    /**
     * Decodes a JSON encoded value.
     *
     * This method will decode a string containing an encoded JSON value. If
     * the value could not be decoded, an exception is thrown that corresponds
     * with the type of error that was encountered.
     *
     * ```php
     * $value = $json->decode($encoded);
     * ```
     *
     * @param string  $json        The JSON encoded value.
     * @param boolean $associative Decode objects as associative arrays?
     * @param integer $depth       The maximum recursive depth.
     * @param integer $options     The decoding options.
     *
     * @return mixed The decoded value.
     *
     * @throws DecodeException  If the value could not be decoded.
     * @throws UnknownException If there was an error but it was not recognized.
     */
    public function decode(
        $json,
        $associative = false,
        $depth = 512,
        $options = 0
    );

    /**
     * Reads a file and decodes its contents as a JSON encoded value.
     *
     * This method will read the contents of a file and decode its contents as
     * if it were a JSON encoded file. If the value could not be decoded, an
     * exception is thrown that corresponds with the type of error that was
     * encountered.
     *
     * ```php
     * $value = $json->decodeFile('/path/to/file.json');
     * ```
     *
     * @param string  $file        The path to the file.
     * @param boolean $associative Decode objects as associative arrays?
     * @param integer $depth       The maximum recursive depth.
     * @param integer $options     The decoding options.
     *
     * @return mixed The decoded value.
     *
     * @throws DecodeException  If the value could not be decoded.
     * @throws UnknownException If there was an error but it was not recognized.
     */
    public function decodeFile(
        $file,
        $associative = false,
        $depth = 512,
        $options = 0
    );

    /**
     * Encodes a value into JSON data.
     *
     * This method will encode a value into JSON data and return it. If the
     * value could not be encoded, an exception is thrown that corresponds
     * with the type of error that was encountered.
     *
     * ```php
     * $encoded = $json->encode($value);
     * ```
     *
     * @param mixed   $value   The value to encode.
     * @param integer $options The encoding options.
     * @param integer $depth   The maximum recursive depth.
     *
     * @return string The encoded value.
     *
     * @throws EncodeException  If the value could not be encoded.
     * @throws UnknownException If there was an error but it was not recognized.
     */
    public function encode($value, $options = 0, $depth = 512);

    /**
     * Encodes a value into JSON data and saves it to a file.
     *
     * This method will encode a value into JSON data and save it to a file.
     * If the value could not be encoded, or if the contents of the file could
     * not be written, an exception is thrown that corresponds to the type of
     * error that was encountered.
     *
     * ```php
     * $json->encode($value, $file);
     * ```
     *
     * @param mixed   $value   The value to encode.
     * @param string  $file    The path to the file.
     * @param integer $options The encoding options.
     * @param integer $depth   The maximum recursive depth.
     *
     * @throws EncodeException  If the value could not be encoded.
     * @throws UnknownException If there was an error but it was not recognized.
     */
    public function encodeFile($value, $file, $options = 0, $depth = 512);

    /**
     * Lints an encoded JSON value.
     *
     * This method will lint an encoded JSON value to check for syntax errors.
     * If an error is found, an exception is thrown that contains information
     * on what the error(s) is.
     *
     * ```php
     * $json->lint($encoded);
     * ```
     *
     * @param string $json The JSON encoded value.
     *
     * @throws LintingException If the encoded value failed to lint.
     */
    public function lint($json);

    /**
     * Lints the contents of a file as JSON encoded data.
     *
     * This method will read the contents of the file and lint it as if it were
     * JSON encoded data. If an error is found, an exception is thrown that
     * contains information on what the error(s) is.
     *
     * ```php
     * $json->lintFile('/path/to/file.json');
     * ```
     *
     * @param string $file The path to the file.
     *
     * @throws LintingException If the encoded value failed to lint.
     */
    public function lintFile($file);

    /**
     * Validates a decoded JSON value using a decoded JSON schema.
     *
     * This method will validate a decoded JSON value using the decoded JSON
     * schema (i.e. {@link http://json-schema.org/ json-schema}). If the data
     * failed validation, an exception is thrown that contains information on
     * what validation constraints failed.
     *
     * ```php
     * $json->validate($schema, $decoded);
     * ```
     *
     * @param object $schema  The decoded schema.
     * @param mixed  $decoded The decoded value.
     *
     * @throws ValidationException If the encoded value fails validation.
     */
    public function validate($schema, $decoded);
}
