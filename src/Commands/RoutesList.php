<?php
namespace Splot\DevToolsModule\Commands;

use MD\Foundation\Utils\ArrayUtils;

use Splot\Framework\Console\AbstractCommand;
use Splot\Framework\Routes\Route;

class RoutesList extends AbstractCommand
{

    protected static $name = 'routes:list';
    protected static $description = 'Displays all registered routes.';

    protected static $arguments = array();

    public function execute()
    {
        $router = $this->get('router');

        $routes = array();
        foreach ($router->getRoutes() as $route) {
            $routes[] = $this->parseRouteInfo($route);
        }

        $routes = ArrayUtils::multiSort($routes, 'url');
        $routes = ArrayUtils::multiSort($routes, 'private');

        foreach($routes as $route) {
            if ($route['private']) {
                $this->writeln('<info>'. $route['name'] .'</info>: private route');
            } else {
                $this->writeln('<info>'. $route['name'] .'</info>: <comment>'. $route['url'] .'</comment>');
            }

            if ($this->getOption('verbose')) {
                $this->writeln('    <comment>HTTP Methods:</comment> '. implode(', ', $route['methods']));
                $this->writeln('    <comment>Class:</comment>        '. $route['class']);
                $this->writeln();
            }
        }

        $this->writeln();
        $this->writeln('<info>Found <comment>'. count($routes) .'</comment> routes.</info>');
    }

    protected function parseRouteInfo(Route $route)
    {
        $info = array(
            'name' => $route->getName(),
            'class' => $route->getControllerClass(),
            'url' => $route->getUrlPattern(),
            'module' => $route->getModuleName(),
            'methods' => array(),
            'private' => $route->isPrivate()
        );

        foreach ($route->getMethods() as $httpMethod => $method) {
            if ($method['method'] !== false) {
                $info['methods'][] = strtoupper($httpMethod);
            }
        }

        return $info;
    }
}
