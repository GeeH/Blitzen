<?php
// This is global bootstrap for autoloading 
require('vendor/autoload.php');

function pr($something)
{
    if (is_array($something) || is_object($something)) {
        $something = json_encode($something);
    }

    error_log($something);

}