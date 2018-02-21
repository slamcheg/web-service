<?php

namespace Proxy\Service\Core;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Kernel
{
    protected $routes;
    protected $container;

    /**
     * Kernel constructor.
     */
    public function __construct()
    {
        $this->routes = new RouteCollection();
        $this->configureContainer();
    }

    /**
     * Configure DI container
     */
    public function configureContainer(): void
    {
        $this->container = new ContainerBuilder();
        $loader = new PhpFileLoader($this->container, new FileLocator($this->getRootDir() . '/config'));
        $loader->load('services.php');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        $this->getContainer()->get(RequestStack::class)->push($request);
        $this->logRequest($request);
        $context = (new RequestContext())->fromRequest($request);
        $matcher = new UrlMatcher($this->getRoutes(), $context);
        /** @var Logger $logger */
        try {
            $attributes = $matcher->match($request->getPathInfo());
            $controller = $attributes['controller'];
            $class = $this->getContainer()->get($controller[0]);
            unset($attributes['controller']);
            $response = new JsonResponse(call_user_func_array([$class, $controller[1]], $attributes));

        } catch (ResourceNotFoundException $e) {
            $response = new JsonResponse(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (MethodNotAllowedException $e) {
            $response = new JsonResponse(['message' => 'Method not allowed'], Response::HTTP_METHOD_NOT_ALLOWED);
        }catch (\Exception $e){
            $response = new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $this->logResponse($response);
        return $response;
    }

    /**
     * @param Response $response
     */
    private function logResponse(Response $response): void
    {
        $handler = new RotatingFileHandler($this->getLogDir() . 'response.log');
        $handler->setFormatter(new JsonFormatter());

        $loggerResponse = new Logger('responseLogger');
        $loggerResponse->pushHandler($handler);
        $loggerResponse->info('request', [
            'headers' => $response->headers->all(),
            'content' => $response->getContent(),
            'statusCode' => $response->getStatusCode()
        ]);
    }

    /**
     * @param Request $request
     */
    private function logRequest(Request $request): void
    {
        $handler = new RotatingFileHandler($this->getLogDir() . 'request.log');
        $handler->setFormatter(new JsonFormatter());

        $loggerRequest = new Logger('requestLogger');
        $loggerRequest->pushHandler($handler);
        $loggerRequest->info('request', [
            'headers' => $request->headers,
            'content' => $request->getContent()
        ]);
    }

    /**
     * @param string $path
     * @param array $controller
     * @param array $methods
     */
    public function map(string $path, array $controller, array $methods): void
    {
        $this->routes->add($path, new Route($path, $controller, [], [], null, [], $methods));
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * @return string
     */
    public function getRootDir(): string
    {
        return dirname(dirname(__DIR__));
    }

    public function getLogDir(): string
    {
        return dirname(dirname(__DIR__)) . '/logs/';
    }


    /**
     * @return mixed
     */
    public function getContainer(): ContainerBuilder
    {
        return $this->container;
    }
}