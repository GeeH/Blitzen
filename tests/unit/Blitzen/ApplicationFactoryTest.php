<?php
namespace Blitzen;

class ApplicationFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    // tests
    public function testGimmeReturnsConfiguredApplication()
    {
        $this->assertInstanceOf('Blitzen\Application', ApplicationFactory::gimme());
    }

}