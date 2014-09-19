<?php
/**
 * @TODO Copy this file to index.php to get started quickly
 *
 * Created by PhpStorm.
 * User: parsingphase
 * Date: 06/09/14
 * Time: 17:18
 */

use Phase\Adze\Application;
use Phase\Blog\Silex\BlogControllerProvider;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$app->loadConfig(require dirname(__DIR__) . '/config/config.php');
$app->setupCoreProviders();
$app->addDefaultTemplatePath();

$resourceController = $app->getResourceController();
// Make the components folder accessible through the ResourceController at /resources
$resourceController->addPathMapping('components', dirname(__DIR__) . '/components');

//TODO remove this default route and set your own
//$app->setUpDefaultHomepage();
$app->setDefaultRouteByUrl('/blog');

//Note: template setup below will be cleaned up as code is refactored
$app->mount('/blog', new BlogControllerProvider());

$app->setUpErrorHandling();

$app->register(new \Phase\TddDeciphered\StyleServiceProvider());

$app->run();
