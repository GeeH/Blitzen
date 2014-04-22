<?php
namespace Blitzen;

class ApplicationFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGimmeReturnsApplication()
    {
        $this->assertInstanceOf('Blitzen\Application', ApplicationFactory::gimme([], [], []));
    }

    public function testPassingOptionsStoresThoseOptions()
    {
        $application = ApplicationFactory::gimme(['groundhog' => 'Phil'], [], []);
        $this->assertEquals($application->getOption('groundhog'), 'Phil');
    }

    // tests

    public function testNotPassingOptionsTriesDefaultFile()
    {
        ApplicationFactory::setDefaultOptionsPath(realpath(__DIR__ . '/../assets/config/options.php'));
        $application = ApplicationFactory::gimme(null, [], []);
        $this->assertEquals($application->getOption('weatherman'), 'Phil Conners');
    }

    public function testNotPassingOptionsAndNoFileThrowsException()
    {
        $path = 'Ned Ryerson';
        ApplicationFactory::setDefaultOptionsPath($path);
        $this->setExpectedException(
            '\InvalidArgumentException',
            'No options file found at ' . $path
        );
        ApplicationFactory::gimme();
    }

    public function testPassingRoutesStoresThoseRoutes()
    {
        $application = ApplicationFactory::gimme([], ['shadow' => ['/shadow']], []);
        $this->assertEquals($application->getRoute('shadow'), ['/shadow']);
    }

    public function testNotPassingRoutesTriesDefaultFile()
    {
        ApplicationFactory::setDefaultRoutesPath(realpath(__DIR__ . '/../assets/config/routes.php'));
        $application = ApplicationFactory::gimme([], null, []);
        $this->assertEquals($application->getRoute('shadow'), ['/shadow']);
    }

    public function testNotPassingRoutesAndNoFileThrowsException()
    {
        $path = 'Rita Hanson';
        ApplicationFactory::setDefaultRoutesPath($path);
        $this->setExpectedException('\InvalidArgumentException', 'No routes file found at ' . $path);
        ApplicationFactory::gimme([]);
    }

    public function testPassingServicesStoresThoseServices()
    {
        $application = ApplicationFactory::gimme([], [], ['Gobblers\Knob' => 'Punxytawny'], []);
        $this->assertEquals($application->getService('Gobblers\Knob'), 'Punxytawny');
    }

    public function testNotPassingServicesTriesDefaultFile()
    {
        ApplicationFactory::setDefaultServicesPath(realpath(__DIR__ . '/../assets/config/services.php'));
        $application = ApplicationFactory::gimme([], [], null);
        $this->assertEquals($application->getService('Gobblers\Knob'), 'Punxytawny');
    }

    public function testNotPassingServicesAndNoFileThrowsException()
    {
        $path = 'Larry';
        ApplicationFactory::setDefaultServicesPath($path);
        $this->setExpectedException('\InvalidArgumentException', 'No services file found at ' . $path);
        ApplicationFactory::gimme([], []);
    }

    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }


}