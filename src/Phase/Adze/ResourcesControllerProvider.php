<?php
/**
 * Created by PhpStorm.
 * User: parsingphase
 * Date: 08/09/14
 * Time: 18:28
 */

namespace Phase\Adze;

use Phase\Adze\Application as AdzeApplication;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Crude controller for making files from installed libraries available to the frontend.
 *
 * Essentially, a dumber but simpler Assetic
 *
 * @package Phase\Adze
 */
class ResourcesControllerProvider implements ControllerProviderInterface
{

    protected $pathMap = [];

    /**
     * Returns routes to connect to the given application.
     *
     * @param SilexApplication $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(SilexApplication $app)
    {
        $app = AdzeApplication::assertAdzeApplication($app);
        $controllers = $app->getControllerFactory();

        $resourceController = $this;

        // For all files, use the prefixMap to see if the file specified is present in the given directory
        $controllers->get(
            '/{path}',
            function (AdzeApplication $app, $path) use ($resourceController) {
                $out = null;

                $file = $resourceController->getFileForUri($path);
                if ($file) {
                    $extension = strtolower($file->getExtension());
                    switch ($extension) {
                        // determine manually a couple of file types that we can't get from magic
                        case 'js':
                            $mimeType = 'text/javascript';
                            break;
                        case 'css':
                            $mimeType = 'text/css';
                            break;
                        default:
                            $mimeType = null;
                    }
                    if ($mimeType) {
                        $out = $app->sendFile($file, 200, ['Content-Type' => $mimeType]);
                    } else {
                        $out = $app->sendFile($file);
                    }
                }
                return $out;
            }
        )->assert('path', '.+'); // match slashes too

        return $controllers;
    }

    /**
     * Add a directory to allow its files to be accessed through the ResourcesController via a given prefix
     *
     * @param $prefix
     * @param $dirPath
     */
    public function addPathMapping($prefix, $dirPath)
    {
        $this->pathMap[$prefix] = $dirPath;
    }

    /**
     * Given a URL relative to the controller root, return the file that matches it
     *
     * @param $path
     * @return null|File
     */
    public function getFileForUri($path)
    {
        $filePath = null;
        foreach ($this->pathMap as $stem => $dir) {
            if (strpos($path, $stem) === 0) {
                $relativePath = substr($path, strlen($stem));
                //Ensure no directory traversal
                $relativePath = preg_replace('/\.{2,}/', '.', $relativePath);
                $filePath = new File($dir . $relativePath, true);
                break;
            }
        }
        return $filePath;
    }
}
