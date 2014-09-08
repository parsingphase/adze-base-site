<?php
/**
 * Created by PhpStorm.
 * User: parsingphase
 * Date: 06/09/14
 * Time: 21:16
 */

namespace Phase\Blog;


use Phase\Adze\Application as AdzeApplication;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

/**
 * @see Silex book chapter 6, ControllerProviders
 * Class SilexControllerProvider
 * @TODO rename to ....\Silex\BlogControllerProvider?
 * @package Phase\Blog
 */
class SilexControllerProvider implements ControllerProviderInterface
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
        // OR create AdzeApplication by composition
        // OR create another shim that composites a Silex App and returns something with accessor functions
        // Promoter function AdzeApplication::assertAdzeApplication is probably best option - else have to shim
        // addressable array-like behaviour & who knows what else?

        //TODO add routes here

//        $controllers->get(
//            '/',
//            function (AdzeApplication $app) {
//                return $app->redirect('/hello');
//            }
//        );

        return $controllers;

    }
}
