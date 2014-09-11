<?php
/**
 * Created by PhpStorm.
 * User: parsingphase
 * Date: 06/09/14
 * Time: 21:16
 */

namespace Phase\Blog\Silex;


use Phase\Adze\Application as AdzeApplication;
use Phase\Blog\Blog;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

/**
 * @see Silex book chapter 6, ControllerProviders
 * Class SilexControllerProvider
 * @TODO rename to ....\Silex\BlogControllerProvider?
 * @package Phase\Blog
 */
class BlogControllerProvider implements ControllerProviderInterface
{

    /**
     * Returns routes to connect to the given application.
     *
     * @param SilexApplication $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(SilexApplication $app)
    {
        // TODO: Implement connect() method.
        $app = AdzeApplication::assertAdzeApplication($app);
        $controllers = $app->getControllerFactory();


        //TODO add routes here
        $app = AdzeApplication::assertAdzeApplication($app);


        $app['blog.controller'] = $app->share(
            function (AdzeApplication $app) {
                $dbConnection = $app->getDatabaseConnection();
                $blog = new Blog($dbConnection);
                $blogController = new BlogController($blog, $app);
                return $blogController;
            }
        );

        $controllers->get(
            '/{uid}_{slug}',
            'blog.controller:singlePostAction'
        )->bind('blog.post');

        $controllers->get(
            '/',
            'blog.controller:indexAction'
        )->bind('blog.index');

        return $controllers;

    }
}
