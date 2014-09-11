<?php
/**
 * Created by PhpStorm.
 * User: parsingphase
 * Date: 06/09/14
 * Time: 17:16
 */

namespace Phase\Adze;


use Doctrine\DBAL\Connection;
use Phase\Adze\Exception\UnpromotedApplicationException;
use Silex\Application as SilexApplication;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use SimpleUser\UserServiceProvider;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Extended Silex Application with selected functionality enabled and with convenient accessor functions
 * @package Phase\Adze
 */
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
     * Checking shim function to ensure that we're using a full Adze Application rather than a base Silex one
     *
     * Typically used where a standard Silex interface specifies a Silex\Application but we're relying on receiving
     * an Adze\Application
     *
     * @param SilexApplication $app
     * @return Application
     * @throws Exception\UnpromotedApplicationException
     */
    public static function assertAdzeApplication(SilexApplication $app)
    {
        if (!$app instanceof self) {
            throw new UnpromotedApplicationException();
        }
        return $app;
    }

    /**
     * Set application configuration values from an associative array
     *
     * @param $config
     * @return $this
     */
    public function loadConfig($config)
    {
        foreach ($config as $k => $v) {
            $this[$k] = $v;
        }
        return $this;
    }

    /**
     * Accessor for the Controller Factory, to help create new Controllers
     *
     * @return ControllerCollection
     */
    public function getControllerFactory()
    {
        return $this['controllers_factory'];
    }

    /**
     * Accessor for the ResourceController, to be able to add new resource directories
     *
     * @return \Phase\Adze\ResourcesControllerProvider
     */
    public function getResourceController()
    {
        return $this->resourceController;
    }

    /**
     * @return Connection
     */
    public function getDatabaseConnection()
    {
        return $this['db'];
    }

    /**
     * Set up the standard Providers that an Adze application expects to rely on
     *
     * @return $this
     */
    public function setupCoreProviders()
    {

        $this->register(
            new SecurityServiceProvider(),
            array(
                'security.firewalls' => array(
                    'secured_area' => array(
                        'pattern' => '^.*$',
                        'anonymous' => true,
                        'remember_me' => array(),
                        'form' => array(
                            'login_path' => '/user/login',
                            'check_path' => '/user/login_check',
                        ),
                        'logout' => array(
                            'logout_path' => '/user/logout',
                        ),
                        'users' => $this->share(
                                function ($app) {
                                    return $app['user.manager'];
                                }
                            ),
                    ),
                ),
            )
        );

        // Notes from https://github.com/jasongrimes/silex-simpleuser
        // Note: As of this writing, RememberMeServiceProvider must be registered *after* SecurityServiceProvider or SecurityServiceProvider
        // throws 'InvalidArgumentException' with message 'Identifier "security.remember_me.service.secured_area" is not defined.'
        $this->register(new RememberMeServiceProvider());

        $this->register(new SessionServiceProvider());
        $this->register(new TranslationServiceProvider()); // required for default form views
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

        // Register the SimpleUser service provider.
        $this->register($u = new UserServiceProvider());

        // Optionally mount the SimpleUser controller provider.
        $this->mount('/user', $u);

        return $this;
    }

    /**
     * Get the twig loader so that more template sources can be added
     * @deprecated Use getTwigFilesystemLoader
     *
     * @return \Twig_Loader_Chain
     */
    public function getTwigLoaderChain()
    {
        return $this['twig.loader'];
    }

    /**
     * Append a new twig loader pointing to a new source of templates
     *
     * @deprecated Use getTwigFilesystemLoader
     *
     * @param \Twig_LoaderInterface $loader
     * @return $this
     */
    public function appendTwigLoader(\Twig_LoaderInterface $loader)
    {
        $this->getTwigLoaderChain()->addLoader($loader);
        return $this;
    }

    /**
     * @return \Twig_Loader_Filesystem
     */
    public function getTwigFilesystemLoader()
    {
        return $this['twig.loader.filesystem'];
    }

    /**
     * Builds and returns the factory.
     *
     * @return FormFactory
     */
    public function getFormFactory()
    {
        return $this['form.factory'];
    }

    /**
     * @return SecurityContext
     */
    public function getSecurityContext()
    {
        return $this['security'];
    }

    /**
     * Add a new Controller, mount it, and make its templates and resources available to the application
     *
     * @deprecated possibly not entirely necessary as modules can define their own template paths / namespaces
     *
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
