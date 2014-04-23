<?php
return [
    'factories' => [
        'index' => function(\Zend\ServiceManager\ServiceManager $serviceManager) {
            return $serviceManager->get('config');
        }
    ]
];