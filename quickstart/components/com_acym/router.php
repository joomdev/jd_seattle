<?php
defined('_JEXEC') or die('Restricted access');
?><?php

include_once __DIR__.DIRECTORY_SEPARATOR.'router'.DIRECTORY_SEPARATOR.'base.php';
include_once __DIR__.DIRECTORY_SEPARATOR.'router'.DIRECTORY_SEPARATOR.'router.php';

function AcymBuildRoute(&$query)
{
    $router = new AcymRouter();

    return $router->build($query);
}

function AcymParseRoute($segments)
{
    $router = new AcymRouter();

    return $router->parse($segments);
}

