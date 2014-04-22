<?php

namespace Blitzen;

use Dash\Router\Http\Parser\ParserManager;
use Dash\Router\Http\Route\RouteManager;
use Dash\Router\Http\Router;
use Dash\Router\Http\RouterFactory;
use Dash\Router\MatchResult\MatchResultInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\ServiceManager\ServiceManager;

class Application
{
    /**
     * @var array
     */
    protected $options;
    /**
     * @var array
     */
    protected $routes;
    /**
     * @var Router
     */
    protected $router;
    /**
     * @var array
     */
    protected $services;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var MatchResultInterface
     */
    protected $routeResult;

    /**
     * @param array $options
     * @param array $routes
     * @param array $services
     */
    function __construct(array $options = [], array $routes = [], array $services = [])
    {
        $this->options = $options;
        $this->routes = $routes;
        $this->services = $services;
    }

    /**
     * @return MatchResultInterface
     */
    public function getRouteResult()
    {
        return $this->routeResult;
    }

    /**
     * @param MatchResultInterface $routeResult
     */
    public function setRouteResult(MatchResultInterface $routeResult)
    {
        $this->routeResult = $routeResult;
    }

    /**
     * Gets single option by key
     *
     * @param $option  Key name
     * @throws \InvalidArgumentException
     */
    public function getOption($option)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        throw new \InvalidArgumentException("Option `$option` not set");
    }

    /**
     * Sets single option by key
     *
     * @param $option  Key name
     * @param $value  Option value
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * Returns entire options array
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets entire options array
     *
     * @param array $options Array of options in key/value pairs
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Gets single route definition by route name
     *
     * @param $routeName  Route name to retrieve
     * @throws \InvalidArgumentException
     */
    public function getRoute($routeName)
    {
        if (array_key_exists($routeName, $this->routes)) {
            return $this->routes[$routeName];
        }

        throw new \InvalidArgumentException("Route `$routeName` not found");
    }

    /**
     * Sets single route definition by route name
     *
     * @param $route  Route name
     * @param array $definition Route definition
     */
    public function setRoute($route, array $definition)
    {
        $this->routes[$route] = $definition;
    }

    /**
     * Returns all service declarations
     *
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Sets all service declarations
     *
     * @param array $services
     */
    public function setServices(array $services)
    {
        $this->services = $services;
    }

    /**
     * Sets single service declaration by it's named key
     *
     * @param $serviceName  Service Named key
     * @param array $serviceDeclaration Service Declaration array
     */
    public function setService($serviceName, array $serviceDeclaration)
    {
        $this->services[$serviceName] = $serviceDeclaration;
    }

    /**
     * Gets single service declaration by it's name key
     *
     * @param $serviceName  Named key
     * @throws \InvalidArgumentException
     */
    public function getService($serviceName)
    {
        if (array_key_exists($serviceName, $this->services)) {
            return $this->services[$serviceName];
        }

        throw new \InvalidArgumentException("Service `$serviceName` not found");
    }

    public function goGoGo()
    {
        $this->bootstrap();
        $this->route();
        return true;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function bootstrap()
    {
        if (!is_a('Dash\Router\Router', $this->router)) {
            $this->router = $this->createRouterFromConfig();
        }
        if (!is_a('Zend\Http\PhpEnvironment\Request', $this->request)) {
            $this->request = new Request();
        }
        if (!is_a('Zend\Http\PhpEnvironment\Request', $this->response)) {
            $this->response = new Response();
        }
    }

    /**
     * @return Router
     * @throws \InvalidArgumentException
     */
    protected function createRouterFromConfig()
    {
        if (empty($this->getRoutes())) {
            throw new \InvalidArgumentException('No routes defined');
        }

        $factory = new RouterFactory();
        return $factory->createService($this->getServiceLocator());
    }

    /**
     * Returns entire route definition array
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Sets entire route definition array
     *
     * @param array $routes
     */
    public function setRoutes(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @return ServiceManager
     * @throws \Zend\ServiceManager\Exception\InvalidServiceNameException
     */
    protected function getServiceLocator()
    {
        $serviceLocator = new ServiceManager();

        $routeManager = new RouteManager();
        $routeManager->setServiceLocator($serviceLocator);
        $serviceLocator->setService('Dash\Router\Http\Route\RouteManager', $routeManager);

        $parserManager = new ParserManager();
        $parserManager->setServiceLocator($serviceLocator);
        $serviceLocator->setService('Dash\Router\Http\Parser\ParserManager', $parserManager);

        $serviceLocator->setService(
            'config',
            [
                'dash_router' => [
                    'routes' => $this->getRoutes()
                ]
            ]
        );

        return $serviceLocator;
    }

    /**
     * @return \Dash\Router\MatchResult\MatchResultInterface|\Dash\Router\MatchResult\UnsuccessfulMatch|null
     * @throws \Dash\Router\Exception\UnexpectedValueException
     * @throws \Zend\Http\Exception\InvalidArgumentException
     */
    protected function route()
    {
        if(is_a($this->getRouteResult(), 'Dash\Router\MatchResult\MatchResultInterface')) {
            return false;
        }

        $routeResult = $this->getRouter()->match($this->getRequest());

        if (is_a($routeResult, ' Dash\Router\MatchResult\UnsuccessfulMatch')) {
            // Unsuccessful Match = 404
            $this->getResponse->setStatusCode(404);
            return false;
        }

        if (is_a($routeResult, 'Dash\Router\Http\MatchResult\MethodNotAllowed')) {
            // Method Not Allowed = 405
            $this->getResponse()->setStatusCode(405);
            $this->getResponse()->setReasonPhrase('Method not allowed');
            $this->getResponse()->getHeaders()->addHeaders(
                ['Allow' => implode(', ', $routeResult->getAllowedMethods())]
            );
            return false;
        }

        $this->setRouteResult($routeResult);

        return true;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
} 