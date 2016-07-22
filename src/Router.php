<?php
namespace CodeHouse\Highway;

use CodeHouse\Highway\Annotations\Controller;
use CodeHouse\Highway\Annotations\RequestMapping;
use CodeHouse\Highway\Annotations\Security;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

class Router
{
    /**
     * @var \Phroute\Phroute\RouteCollector
     */
    private $routeCollector;
    private $diContainer;

    /**
     * Highway constructor.
     * @param string $pathToControllers Full path to directory where controller files reside
     * @param String $controllerNamespace Full controller namespace
     * @param object $securityFilterHandler Security handler, null for none (default)
     */
    public function __construct($pathToControllers, $controllerNamespace, $securityFilterHandler = null)
    {
        $this->registerAnnotations();
        $this->routeCollector = new \Phroute\Phroute\RouteCollector();

        $controllers = $this->readControllers($pathToControllers);
        foreach ($controllers as $file) {
            if (strstr($file, '.php') == '.php') {
                require_once $pathToControllers . '/' . $file;
                $className = substr($file, 0, -4);
                $reflection = new \ReflectionClass($controllerNamespace . '\\' . $className);
                $this->parseController($reflection);
            }
        }

        //if security handler is set, try to register appropriate methods
        if ($securityFilterHandler != null && is_object($securityFilterHandler)) {
            $reflection = new \ReflectionClass($securityFilterHandler);
            foreach ($reflection->getMethods() as $method) {
                $this->routeCollector->filter('require' . ucfirst($method->getName()), array($method->class, $method->name));
            }
        }
    }

    private function registerAnnotations()
    {
        AnnotationRegistry::registerFile(__DIR__ . '/Annotations/Controller.php');
        AnnotationRegistry::registerFile(__DIR__ . '/Annotations/RequestMapping.php');
        AnnotationRegistry::registerFile(__DIR__ . '/Annotations/Security.php');
    }

    /**
     * Scan controller folder for files
     *
     * @param string $directory Path to scan
     *
     * @return array Array of found files
     *
     * @throws \Exception When reading directory listing fails for whatever reason
     */
    private function readControllers($directory)
    {
        $files = scandir($directory);
        if ($files === false) {
            throw new \Exception('Provided directory is invalid! Directory:' . $directory);
        }
        return $files;
    }

    /**
     * Get controller annotations. If class does not have controller annotation, it will be skipped
     *
     * @param \ReflectionClass $reflection
     */
    public function parseController(\ReflectionClass $reflection)
    {
        if ($this->isController($reflection)) {
            foreach ($reflection->getMethods() as $method) {
                $this->parseMethodAnnotations($method);
            }
        }
    }

    /**
     * @param \ReflectionClass $reflection Reflection of the class to check
     *
     * @return boolean
     */
    private function isController(\ReflectionClass $reflection)
    {
        $reader = new AnnotationReader();
        $classAnnotations = $reader->getClassAnnotations($reflection);
        foreach ($classAnnotations as $annotation) {
            if ($annotation instanceof Controller) {
                return true;
            }
        }
        return false;
    }

    private function parseMethodAnnotations(\ReflectionMethod $method)
    {
        $hasSecurityConstraint = false;
        $httpMethod = 'get';
        $url = '/';
        $reader = new AnnotationReader();
        $annotations = $reader->getMethodAnnotations($method);
        if (count($annotations) > 0) {
            foreach ($annotations as $annotation) {
                if ($annotation instanceof RequestMapping) {
                    $url = $annotation->value;
                    $httpMethod = $annotation->method;
                } else if ($annotation instanceof Security) {
                    $hasSecurityConstraint = $annotation->role;
                }
            }
            if ($hasSecurityConstraint) {
                $this->collectSecureRoute($httpMethod, $url, $method, $hasSecurityConstraint);
            } else {
                $this->collectRoute($httpMethod, $url, $method);
            }
        }
    }

    public function collectSecureRoute($httpMethod, $url, $method, $requiredRoleName)
    {
        $this->routeCollector->$httpMethod("$url", ["$method->class", "$method->name"], ['before' => 'require' . ucfirst($requiredRoleName)]);
    }

    /**
     * Create new route collector entry
     *
     * @param string $httpMethod HTTP method to use (get, post, any)
     * @param string $url URL mapping to use
     * @param \ReflectionMethod $method Method related to annotation
     */
    public function collectRoute($httpMethod, $url, $method)
    {
        $this->routeCollector->$httpMethod("$url", ["$method->class", "$method->name"]);
    }

    /**
     * Set DI container
     *
     * @param $container
     */
    public function setDiContainer($container)
    {
        $this->diContainer = $container;
    }

    /**
     * Produce the page requested with $httpMethod from $uri
     *
     * @param $httpMethod
     * @param $uri
     *
     * @return string HTML as template
     */
    public function serve($httpMethod, $uri)
    {
        if ($this->diContainer != null) {
            $resolver = new DIHandlerResolver();
            $resolver->setContainer($this->diContainer);
            $dispatcher = new Dispatcher($this->routeCollector->getData(), $resolver);
        } else {
            $dispatcher = new Dispatcher($this->routeCollector->getData(), null);
        }
        return $dispatcher->dispatch($httpMethod, $uri);
    }
}