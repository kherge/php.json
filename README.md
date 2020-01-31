[![Build Status](https://travis-ci.org/kherge-php/json.svg?branch=master)](https://travis-ci.org/kherge-php/json)
[![Packagist](https://img.shields.io/packagist/v/kherge/json.svg)](https://packagist.org/packages/kherge/json)
[![Packagist Pre Release](https://img.shields.io/packagist/vpre/kherge/json.svg)](https://packagist.org/packages/kherge/json)

JSON
====

A library for encoding, decoding, linting, and validating JSON data.

This library provides a simplified interface into existing functionality that
is provided by PHP's `json` extension, `justinrainbow/json-schema`, and also
`seld/jsonlint`. The purpose is to make it easy to use while making it hard
to miss errors.

Usage
-----

```php
<?php

use KHerGe\JSON\JSON;

$json = new JSON();

// Decode JSON values.
$decoded = $json->decode('{"test":123}');

// Decode JSON values in files.
$decoded = $json->decodeFile('/path/to/file.json');

// Encode native values.
$encoded = $json->encode(['test' => 123]);

// Encode native values into files.
$json->encodeFile(['test' => 123], '/path/to/file.json');

// Lint an encoded JSON value.
$json->lint('{"test":}');

// Lint an encoded JSON value in a file.
$json->lintFile('/path/to/file.json');

// Validate a decoded JSON value using a JSON schema.
$json->validate(
    $json->decodeFile('/path/to/schema.json'),
    $decoded
);
```

Documentation
-------------

The [`JSONInterface`](src/KHerGe/JSON/JSONInterface.php) interface is your
best resource. The `JSON` class you will be using implements this interface
and contains all of the information you will need.

Requirements
------------

- PHP 7.3+
    - json

Installation
------------

    composer require kherge/json=^3

License
-------

This library is released under the MIT and Apache 2.0 licenses.
