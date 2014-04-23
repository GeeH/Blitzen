<?php

namespace Blitzen;

use Blitzen\Controller\BaseController;
use Dash\Router\Http\MatchResult\SuccessfulMatch;
use Dash\Router\Http\Parser\ParserManager;
use Dash\Router\Http\Route\RouteManager;
use Dash\Router\Http\Router;
use Dash\Router\Http\RouterFactory;
use Dash\Router\MatchResult\MatchResultInterface;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\ServiceManager\Config;
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
     * @var ServiceManager
     */
    protected $serviceLocator;

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
        $httpResponseSender = new HttpResponseSender();
        $this->bootstrap();
        if (!$this->route()) {
            $httpResponseSender->sendHeaders($this->getResponse());
            return false;
        }
        $this->dispatch();

        $httpResponseSender->sendHeaders($this->getResponse());
        $httpResponseSender->sendContent($this->getResponse());

        return true;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function bootstrap()
    {
        if (!($this->router instanceof Router)) {
            $this->router = $this->createRouterFromConfig();
        }
        if (!($this->request instanceof Request)) {
            $this->request = new Request();
        }
        if (!($this->response instanceof Response)) {
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

        if ($this->serviceLocator instanceof ServiceManager) {
            return $this->serviceLocator;
        }
        $config = new Config($this->getServices());
        $serviceLocator = new ServiceManager($config);

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

        $this->serviceLocator = $serviceLocator;
        return $serviceLocator;
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
     * @return \Dash\Router\MatchResult\MatchResultInterface|\Dash\Router\MatchResult\UnsuccessfulMatch|null
     * @throws \Dash\Router\Exception\UnexpectedValueException
     * @throws \Zend\Http\Exception\InvalidArgumentException
     */
    protected function route()
    {
        if ($this->getRouteResult() instanceof \Dash\Router\MatchResult\MatchResultInterface) {
            return false;
        }

        $routeResult = $this->getRouter()->match($this->getRequest());

        if ($routeResult instanceof \Dash\Router\MatchResult\UnsuccessfulMatch) {
            // Unsuccessful Match = 404
            $this->getResponse()->setStatusCode(404);
            $this->getResponse()->setReasonPhrase('Page not found');
            return false;
        }

        if ($routeResult instanceof \Dash\Router\Http\MatchResult\MethodNotAllowed) {
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

    protected function dispatch()
    {
        /** @var SuccessfulMatch $routeResult */
        $routeResult = $this->getRouteResult();
        $controllerKey = $routeResult->getParam('controller');
        $controller = $this->getServiceLocator()->get($controllerKey);

        // if the factory returns an object, we can try and dispatch the action
        if ($controller instanceof BaseController) {
            $action = $this->getRouteResult()->getParam('action');
            $result = $controller->{$action}($this->getResponse(), $routeResult->getParams());
        }

        if (!isset($result)) {
            $result = $controller;
        }

        if (is_string($result) || is_int($result)) {
            $this->getResponse()->setContent($result);
            return true;
        }

        $this->getResponse()->setContent(json_encode($result));
        $this->getResponse()->getHeaders()->addHeaders(['Content-type' => 'application/json']);

        return true;
    }
} 