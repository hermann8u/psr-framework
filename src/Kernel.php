<?php

declare(strict_types=1);

namespace App;

use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

/**
 * The kernel of the application
 */
final class Kernel
{
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

        $fileLocator = new FileLocator($this->getProjectDir().'/config');
        $loader = new YamlFileLoader($container, $fileLocator);

        try {
            $loader->load('services.yaml');
            $loader->load('services_'.$this->env.'.yaml');
        } catch (FileLocatorFileNotFoundException $e) {
        }

        $container->compile();

        // dump the container
        @mkdir(dirname($containerDumpFile), 0777, true);
        file_put_contents(
            $containerDumpFile,
            (new PhpDumper($container))->dump(['class' => 'CachedContainer'])
        );
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
        return sprintf("%s/var/cache/%s", $this->getProjectDir(), $this->env);
    }
}
