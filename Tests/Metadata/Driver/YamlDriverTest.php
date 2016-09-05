<?php

namespace Vich\UploaderBundle\Tests\Metadata\Driver;

use PHPUnit\Framework\TestCase;
use Vich\UploaderBundle\Metadata\Driver\YamlDriver;

/**
 * YamlDriverTest.
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class YamlDriverTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testInconsistentYamlFile()
    {
        $rClass = new \ReflectionClass('\DateTime');
        $driver = $this->getDriver($rClass);

        $driver->mappingContent = [];

        $driver->loadMetadataForClass($rClass);
    }

    /**
     * @dataProvider fieldsProvider
     */
    public function testLoadMetadataForClass($mapping, $expectedMetadata)
    {
        $rClass = new \ReflectionClass('\DateTime');

        $driver = $this->getDriver($rClass);
        $driver->mappingContent = [
            $rClass->name => $mapping,
        ];

        $metadata = $driver->loadMetadataForClass($rClass);

        $this->assertInstanceOf('\Vich\UploaderBundle\Metadata\ClassMetadata', $metadata);
        $this->assertObjectHasAttribute('fields', $metadata);
        $this->assertEquals($expectedMetadata, $metadata->fields);
    }

    protected function getDriver(\ReflectionClass $class, $found = true)
    {
        $fileLocator = $this->createMock('\Metadata\Driver\FileLocatorInterface');
        $driver = new TestableYamlDriver($fileLocator);

        $fileLocator
            ->expects($this->once())
            ->method('findFileForClass')
            ->with($this->equalTo($class), $this->equalTo('yml'))
            ->will($this->returnValue($found ? 'something not null' : null));

        return $driver;
    }

    public function fieldsProvider()
    {
        $singleField = [
            'mapping' => [
                'file' => [
                    'mapping' => 'dummy_file',
                    'filename_property' => 'fileName',
                    'size' => 'sizeField',
                    'mime_type' => 'mimeTypeField',
                    'original_name' => 'originalNameField',
                ],
            ],
            'metadata' => [
                'file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                    'size' => 'sizeField',
                    'mimeType' => 'mimeTypeField',
                    'originalName' => 'originalNameField',
                ],
            ],
        ];

        $severalFields = [
            'mapping' => [
                'file' => [
                    'mapping' => 'dummy_file',
                    'filename_property' => 'fileName',
                ],
                'image' => [
                    'mapping' => 'dummy_image',
                    'filename_property' => 'imageName',
                    'size' => 'imageSize',
                    'mime_type' => 'imageMimeType',
                    'original_name' => 'imageOriginalName',
                ],
            ],
            'metadata' => [
                'file' => [
                    'mapping' => 'dummy_file',
                    'propertyName' => 'file',
                    'fileNameProperty' => 'fileName',
                    'size' => null,
                    'mimeType' => null,
                    'originalName' => null,
                ],
                'image' => [
                    'mapping' => 'dummy_image',
                    'propertyName' => 'image',
                    'fileNameProperty' => 'imageName',
                    'size' => 'imageSize',
                    'mimeType' => 'imageMimeType',
                    'originalName' => 'imageOriginalName',
                ],
            ],
        ];

        return [
            [[], []],
            [$singleField['mapping'], $singleField['metadata']],
            [$severalFields['mapping'], $severalFields['metadata']],
        ];
    }
}

class TestableYamlDriver extends YamlDriver
{
    public $mappingContent;

    protected function loadMappingFile($file)
    {
        return $this->mappingContent;
    }
}
