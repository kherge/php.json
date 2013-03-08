<?php

namespace Herrera\Json\Tests;

use Herrera\Json\Exception\JsonException;
use Herrera\Json\Json;
use Herrera\PHPUnit\TestCase;

class JsonTest extends TestCase
{
    /**
     * @var Json
     */
    private $json;

    public function testDecode()
    {
        $data = array('rand' => rand());

        $this->assertEquals(
            (object) $data,
            $this->json->decode(json_encode($data))
        );
    }

    public function testDecodeInvalidUtf8()
    {
        $this->setExpectedException(
            'Herrera\\Json\\Exception\\JsonException',
            'Malformed UTF-8 characters, possibly incorrectly encoded.'
        );

        $this->json->decode('{"bad": \"' . "\xf0\x28\x8c\x28" . '"}');
    }

    public function testDecodeFailLint()
    {
        $this->setExpectedException('Seld\\JsonLint\\ParsingException');

        $this->json->decode('{');
    }

    /**
     * @depends testDecode
     */
    public function testDecodeFile()
    {
        $file = $this->createFile();
        $data = array('rand' => rand());

        file_put_contents($file, json_encode($data));

        $this->assertEquals((object) $data, $this->json->decodeFile($file));
    }

    public function testDecodeFileReadError()
    {
        $this->setExpectedException(
            'Herrera\\Json\\Exception\\FileException',
            'No such file or directory'
        );

        $this->json->decodeFile('/does/not/exist');
    }

    public function testLint()
    {
        $json = '{"test": 213}';

        $this->json->lint($json);

        $this->setExpectedException('Seld\\JsonLint\\ParsingException');

        $this->json->lint($json . '{');
    }

    public function testValidate()
    {
        $schema = (object) array(
            'title' => 'test schema',
            'type' => 'object',
            'properties' => (object) array(
                'random' => (object) array('type' => 'integer'),
                'static' => (object) array('type' => 'string')
            ),
            'required' => array('random')
        );

        $data = (object) array('random' => rand());

        $this->json->validate($schema, $data);

        $data->static = true;

        try {
            $this->json->validate($schema, $data);
        } catch (JsonException $exception) {
            $this->assertEquals(
                array('static: boolean value found, but a string is required'),
                $exception->getErrors()
            );
        }
    }

    protected function setUp()
    {
        $this->json = new Json();
    }
}