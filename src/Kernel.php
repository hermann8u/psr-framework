<?php

declare(strict_types=1);

namespace App;

use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

/**
 * The kernel of the application
 */
final class Kernel
{
    const CONFIG_EXTENSIONS = '.{php,xml,yaml,yml}';

    /** @var string */
    private $env;

    /** @var bool */
    private $debug;

    /** @var ContainerInterface */
    private $container;

    public function __construct(string $env, bool $debug = false)
    {
        $this->env = $env;
        $this->debug = $debug;
    }

    /**
     * Run the application's kernel
     *
     * @throws \Exception
     */
    public function run(): void
    {
        $this->boot();

        $request = $this->container->get(ServerRequestCreatorInterface::class)->fromGlobals();
        $response = $this->container->get(RequestHandler::class)->handle($request);

        (new SapiEmitter())->emit($response);
    }

    /**
     * Prepare the kernel to run the application
     *
     * @throws \Exception
     */
    private function boot(): void
    {
        if ($this->debug) {
            $this->initWhoops();
        }

        $containerDumpFile = $this->getCacheDir().'/container.php';

        if ($this->debug || !file_exists($containerDumpFile)) {
            $this->buildContainer($containerDumpFile);
        }

        require_once $containerDumpFile;

        $this->container = new \CachedContainer();
    }

    /**
     * Build the container based on the application's configuration and dump it to the given file
     *
     * @param string $containerDumpFile
     *
     * @throws \Exception
     */
    private function buildContainer(string $containerDumpFile): void
    {
        $container = new ContainerBuilder();

        $container->setParameter('app.environment', $this->env);
        $container->setParameter('app.debug', $this->debug);
        $container->setParameter('app.project_dir', $this->getProjectDir());
        $container->setParameter('app.cache_dir', $this->getCacheDir());

        $container->registerForAutoconfiguration(MiddlewareInterface::class)->addTag('app.middleware');

        $loader = $this->getContainerLoader($container);
        $loader->load('{packages}/*'.self::CONFIG_EXTENSIONS, 'glob');
        $loader->load('{packages}/'.$this->env.'/**/*'.self::CONFIG_EXTENSIONS, 'glob');
        $loader->load('{services}'.self::CONFIG_EXTENSIONS, 'glob');
        $loader->load('{services}_'.$this->env.self::CONFIG_EXTENSIONS, 'glob');

        $container->compile();

        // dump the container
        @mkdir(dirname($containerDumpFile), 0777, true);
        file_put_contents(
            $containerDumpFile,
            (new PhpDumper($container))->dump(['class' => 'CachedContainer'])
        );
    }

    private function getContainerLoader(ContainerBuilder $container): LoaderInterface
    {
        $locator = new FileLocator($this->getConfigDir());
        $resolver = new LoaderResolver([
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
            new ClosureLoader($container),
        ]);

        return new DelegatingLoader($resolver);
    }

    /**
     * Init Whoops, an error handler for the debug mode
     */
    private function initWhoops(): void
    {
        if (!class_exists('\\Whoops\\Run')) {
            return;
        }

        $whoops = new \Whoops\Run();
        $whoops->appendHandler(new \Whoops\Handler\PrettyPageHandler());

        $whoops->register();
    }

    private function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    private function getCacheDir(): string
    {
        return sprintf('%s/var/cache/%s', $this->getProjectDir(), $this->env);
    }

    private function getConfigDir(): string
    {
        return $this->getProjectDir().'/config';
    }
}
