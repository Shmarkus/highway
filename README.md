#Highway
The library is a wrapper over [mrjgreen/phroute](https://github.com/mrjgreen/phroute) routing library. The added 
functionality is following:

 * Annotation based configuration for controllers (**@Controller**)
 * Annotation based configuration for request mapping (**@RequestMapping**)
 * Annotation based configuration for protected content (**@Security**)
 * Dependency injection support

## Initialization
### Basic
To get the system working, put the following to your index.php (assuming that your controllers are in /src/Controller
 folder and in Controller namespace):
```PHP

$router = new CodeHouse\Highway\Router(__DIR__ . '/src/Controller', 'Controller');
echo $router->serve($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
```
### Dependency injection
In order to use dependency injection, after initializing the router, initialize DI container and pass it to the 
Highway instance:
```PHP
$builder = new \DI\ContainerBuilder();
$builder->useAnnotations(true);
$router->setDiContainer($builder->build());
```
### Security
If you need to use role based authorization, you must pass security handler to Highway router instance. The security 
handler defines methods that are invoked when router finds @Security annotation. An example security handler looks 
something like this:
```PHP
class SecurityHandler
{
    public static function admin()
    {
        $auth = new \Services\Authentication();
        if ($auth->getLoggedInUserRole() != UserRoles::ADMIN) {
            throw new InvalidRoleException('Role needed: ' . UserRoles::ADMIN . ', Role found: ' . $auth->getLoggedInUserRole());
        }
    }
}
```
It checks whether user has the admin role and if not, then throws an InvalidRoleException! This method is invoked 
when user is trying to access a method that is annotated with @Security annotation and the annotation has role value 
"admin".

Configuring the router to use the handler is simple:
```PHP

$router = new CodeHouse\Highway\Router(__DIR__ . '/src/Controller', 'Controller', new SecurityHandler());
echo $router->serve($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
```
## Annotations
### Controller
This annotation is indicative for the library. All other annotations are scanned only if the class is annotated with 
the @Controller annotation!
```PHP
use \CodeHouse\Highway\Annotations as Highway;

/**
 * @Highway\Controller
 */
 class SomeController { .. }
 
```
### Request mapping
This annotation is used on methods to indicate the URL that will point to this current method. Note that you can only
 use each URL and method once.
```PHP
use \CodeHouse\Highway\Annotations as Highway;

...

/**
 * @Highway\RequestMapping(value="/users", method="get")
 */
 public function listUsers() { ... }
 
...
 
```
Note that the method name does not matter here. When user navigates to [server]/users then the return value of 
*listUsers()*  is served! 

You can also use dynamic URLs. Read about them from mrjgreen's page!

### Security
If you have to restrict access to certain methods, then use the @Security tag.
```PHP
use \CodeHouse\Highway\Annotations as Highway;

...

/**
 * @Highway\Security(role="admin")
 */
 public function adminOnlyMethod() { ... }
 
...
 
```