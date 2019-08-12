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

    /** @var bool */
    private $booted;

    public function __construct(string $env, bool $debug = false)
    {
        $this->booted = false;
        $this->env = $env;
        $this->debug = $debug;
    }

    public function run()
    {
        $this->boot();

        $request = $this->container->get(ServerRequestCreatorInterface::class)->fromGlobals();
        $response = $this->container->get(RequestHandler::class)->handle($request);

        (new SapiEmitter())->emit($response);
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $containerDumpFile = $this->getProjectDir().'/var/cache/'.$this->env.'/container.php';

        if ($this->debug || !file_exists($containerDumpFile)) {
            $this->buildContainer($containerDumpFile);
        }

        require_once $containerDumpFile;

        $this->container = new \CachedContainer();
        $this->booted = true;
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    private function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    private function buildContainer(string $containerDumpFile)
    {
        $container = new ContainerBuilder();

        $container->setParameter('app.project_dir', $this->getProjectDir());
        $container->setParameter('app.cache_dir', $this->getProjectDir().'/var/cache/'.$this->env);
        $container->setParameter('app.environment', $this->env);
        $container->setParameter('app.debug', $this->debug);

        $container->registerForAutoconfiguration(MiddlewareInterface::class)->addTag('app.middleware');

        $fileLocator = new FileLocator($this->getProjectDir().'/config');
        $loader = new YamlFileLoader($container, $fileLocator);

        try {
            $loader->load('services.yaml');
            $loader->load('services_'.$this->env.'.yaml');
        } catch (FileLocatorFileNotFoundException $e) {
        }

        $container->compile();

        //dump the container
        @mkdir(dirname($containerDumpFile), 0777, true);
        file_put_contents(
            $containerDumpFile,
            (new PhpDumper($container))->dump(['class' => 'CachedContainer'])
        );
    }
}
