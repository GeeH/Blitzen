<?php
function pr($obj)
{
    die(xdebug_var_dump($obj));
}

chdir(__DIR__ . '/../');
include('vendor/autoload.php');

$application = \Blitzen\ApplicationFactory::gimme();
$application->goGoGo();
pr($application->getRouteResult());