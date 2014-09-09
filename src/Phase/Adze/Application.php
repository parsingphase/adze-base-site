<?php
/**
 * Created by PhpStorm.
 * User: parsingphase
 * Date: 06/09/14
 * Time: 17:16
 */

namespace Phase\Adze;


use Phase\Adze\Exception\UnpromotedApplicationException;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

class Application extends SilexApplication
{
    use SilexApplication\TwigTrait;
    use SilexApplication\SecurityTrait;
    use SilexApplication\FormTrait;
    use SilexApplication\UrlGeneratorTrait;
    use SilexApplication\MonologTrait;

    /**
     * @var ResourcesControllerProvider
     * @todo Register in DI store if required
     */
    protected $resourceController;

    /**
     * @param SilexApplication $app
     * @return self
     * @throws Exception\UnpromotedApplicationException
     */
    public static function assertAdzeApplication(SilexApplication $app)
    {
        if (!$app instanceof self) {
            throw new UnpromotedApplicationException();
        }
        return $app;
    }

    public function loadConfig($config)
    {
        foreach ($config as $k => $v) {
            $this[$k] = $v;
        }
        return $this;
    }

    /**
     * @return ControllerCollection
     */
    public function getControllerFactory()
    {
        return $this['controllers_factory'];
    }

    /**
     * @return \Phase\Adze\ResourcesControllerProvider
     */
    public function getResourceController()
    {
        return $this->resourceController;
    }

    public function setupCoreProviders()
    {
        //TODO must set up twig(/module) paths before this?

        // TODO create a ResourceProvider to mount at /resources, which can pass through module frontend files

        $this->register(new SessionServiceProvider());
        $this->register(new FormServiceProvider());

        $this->register(new DoctrineServiceProvider());

        $this->register(new UrlGeneratorServiceProvider());
        $this->register(new ValidatorServiceProvider());
        $this->register(new ServiceControllerServiceProvider());

        $this->register(
            new TwigServiceProvider(),
            ['twig.options' => array('cache' => $this['twig.cache.dir'])]
        );
        /*
        $this['twig'] = $this->share(
            $this->extend(
                'twig',
                function ($twig, $app) {
                    // add custom globals, filters, tags, ...

                    return $twig;
                }
            )
        );
        */

        $this->resourceController = new ResourcesControllerProvider();
        $this->mount('/resources', $this->resourceController);


        return $this;
    }

    /**
     * @return \Twig_Loader_Chain
     */
    public function getTwigLoaderChain()
    {
        return $this['twig.loader'];
    }

    public function appendTwigLoader(\Twig_LoaderInterface $loader)
    {
        $this->getTwigLoaderChain()->addLoader($loader);
        return $this;
    }

    /**
     * @param $mountPoint
     * @param ControllerProviderInterface $controller
     * @param null $templatesPath
     * @param null|array $resourcesPaths [prefix => path]
     * @return $this
     */
    public function enableModule(
        $mountPoint,
        ControllerProviderInterface $controller,
        $templatesPath = null,
        $resourcesPaths = null
    ) {
        $this->mount($mountPoint, $controller);
        if ($templatesPath) {
            $this->appendTwigLoader(new \Twig_Loader_Filesystem($templatesPath));
        }
        if ($resourcesPaths) {
            foreach ($resourcesPaths as $k => $v) {
                $this->getResourceController()->addPathMapping($k, $v);
            }
        }

        return $this;
    }
}
