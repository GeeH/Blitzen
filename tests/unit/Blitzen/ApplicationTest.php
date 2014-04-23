<?php
namespace Blitzen;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{

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
        $application->setRoute('death', ['in front of train']);
        $this->assertEquals(['in front of train'], $application->getRoute('death'));
        $application->setRoutes(['death' => 'in front of train']);
        $this->assertArrayHasKey('death', $application->getRoutes());
    }

    // tests

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

    public function testGoGoGoWith404()
    {
        $application = new Application([], ['foo' => ['/foo']]);
        $application->goGoGo();
        $this->assertInstanceOf('Dash\Router\Http\Router', $application->getRouter());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Request', $application->getRequest());
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $application->getResponse());
        $this->assertEquals(404, $application->getResponse()->getStatusCode());
    }


    public function testGoGoGoTwiceRoutesOnlyOnce()
    {
        $application = new Application([], ['foo' => ['/foo']]);
        $application->goGoGo();
        $this->assertEquals(404, $application->getResponse()->getStatusCode());
        $application->setRoutes(['foo' => ['/']]);
        $application->goGoGo();
        $this->assertEquals(404, $application->getResponse()->getStatusCode());
    }

    public function testGoGoGoWith405()
    {
        $application = new Application([], ['foo' => ['/', 'index', 'index', ['post']]]);
        $application->goGoGo();
        $this->assertEquals(405, $application->getResponse()->getStatusCode());
    }

    public function testGoGoGoWith200()
    {
        $config = ['foo' => ['/', 'gobblers', 'Gobblers\Knob']];

        $application = new Application(
            [], $config, [
                'factories' => [
                    'Gobblers\Knob' => function () {
                        return 'Punxatawny';
                    }
                ]
            ]
        );
//
//        $routeManager = $this->getMockBuilder('Dash\Router\Http\Route\RouteManager')
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $serviceManager = $this->getMockBuilder('Zend\ServiceManager\ServiceManager')
//            ->disableOriginalConstructor()
//            ->setMethods(['get'])
//            ->getMock();
//
//        $serviceManager->expects($this->atLeastOnce())
//            ->method('get')
//            ->will($this->onConsecutiveCalls($routeManager, ['dash_router' => ['routes' => $config]]));
//
//        $application->setServiceLocator($serviceManager);

        $application->goGoGo();
        $this->assertEquals(200, $application->getResponse()->getStatusCode());
    }

    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

}