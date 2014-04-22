<?php
namespace Blitzen;

use Zend\Uri\Http as HttpUri;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    // tests
    public function testOptionSettersAndGetters()
    {
        $application = new Application();
        $application->setOption('death', 'toaster in the bath');
        $this->assertEquals('toaster in the bath', $application->getOption('death'));
        $application->setOptions(['death' => 'toaster in the bath']);
        $this->assertArrayHasKey('death', $application->getOptions());
    }


    public function testRouteSettersAndGetters()
    {
        $application = new Application();
        $application->setRoute('death' , ['in front of train']);
        $this->assertEquals(['in front of train'], $application->getRoute('death'));
        $application->setRoutes(['death' => 'in front of train']);
        $this->assertArrayHasKey('death', $application->getRoutes());
    }

    public function testServiceSettersAndGetters()
    {
        $application = new Application();
        $application->setService('death', ['jump of building']);
        $this->assertEquals(['jump of building'], $application->getService('death'));
        $application->setServices(['death' => 'jump off building']);
        $this->assertArrayHasKey('death', $application->getServices());
    }

    public function testGoGoGoExceptsWithNoRoutes()
    {
        $this->setExpectedException('\InvalidArgumentException', 'No routes defined');
        $application = new Application([], []);
        $application->goGoGo();
    }

    public function testGoGoGo()
    {
        $application = new Application([], ['foo' => ['/foo']]);
        $application->goGoGo();
        $this->assertInstanceOf('Dash\Router\Http\Router', $application->getRouter());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Request', $application->getRequest());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $application->getResponse());
    }

}