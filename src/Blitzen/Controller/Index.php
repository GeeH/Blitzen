<?php
/**
 * User: garyhockin
 * Date: 22/04/2014
 * Time: 13:12
 */

namespace Blitzen\Controller;

use Zend\Http\PhpEnvironment\Response;

class Index extends BaseController
{
    public function index(Response $response, array $params)
    {
        return 'hiya';
    }
} 