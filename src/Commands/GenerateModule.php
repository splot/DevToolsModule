<?php
namespace Splot\DevToolsModule\Commands;

use MD\Foundation\Utils\StringUtils;

use Splot\Framework\Application\AbstractApplication;
use Splot\Framework\Console\AbstractCommand;

class GenerateModule extends AbstractCommand 
{

    protected static $name = 'generate:module';
    protected static $description = 'Generates a module directory structure and classes.';

    protected static $arguments = array(
        'name' => 'Short name of the module to be generated, e.g. Blog (if full name would be AcmeBlogModule)'
    );

    /**
     * Generates a module directory structure and classes.
     * 
     * @param string $name Short name of the module to be generated, e.g. Blog (if full name would be AcmeBlogModule)
     */
    public function execute($name) {
        $application = $this->get('application');
        $applicationName = $application->getName();

        /* Provide some required information */
        // validate the name of the module
        $name = $this->validateName($name, $applicationName);
        // validate application name (could be altered to the instantiated application name)
        $applicationName = $this->validateApplicationName($applicationName);
        // generate, confirm and validate the full module name
        $moduleName = $this->validateModuleName($name, $applicationName, $application);
        $this->writeln();
        // generate, confirm and validate namespace in which this module should be created
        $namespace = $this->validateNamespace($name, $applicationName);
        $this->writeln();
        // generate, confirm and validate directory path at which this module should be created
        $path = $this->validatePath($namespace);
        $this->writeln();
        // generate, confirm and validate the URL prefix for this module
        $urlPrefix = $this->validateUrlPrefix($name);
        $this->writeln();

        // confirm generation of assets dirs
        $generateAssets = $this->confirm('Do you want to generate all public assets dirs?', true);
        $this->writeln();

        // confirm generation of everything
        if (!$this->confirm('Are you sure to generate <info>'. $moduleName .'</info> with namespace <info>'. $namespace .'</info>'. NL .'    at path <comment>'. $path .'</comment>?', true)) {
            $this->writeln('Canceled.');
            return 0;
        }

        /* Generate! */
        $this->writeln();
        $this->write('Generating...');
        if (!$this->generateModule($name, $moduleName, $namespace, $path, $urlPrefix, $applicationName, $generateAssets)) {
            $this->write('<error>failed</error>');
            return 0;
        }

        $this->writeln('done.');
        $this->writeln();

        /* once generated already boot the module for current request to validate it and possibly quickly reuse it */
        $moduleClassName = $namespace . NS . $moduleName;
        $module = new $moduleClassName();
        $application->bootModule($module);

        /* ask if a controller should also be generated? */
        if ($this->confirm('Do you also want to generate a controller for this module?', true)) {
            // ask for the controller name and validate it
            $controllerName = $this->ask('What is the controller name?', 'Index', array('Index', 'Default', $name, 'Home'), function($answer) {
                if (!StringUtils::isClassName($answer)) {
                    throw new \RuntimeException('Please provide a valid name that can be used as a class name for the controller.');
                }
                return $answer;
            });

            // delegate the task of generating a controller to its specific task
            $this->get('console')->call('generate:controller', $moduleName .':'. $controllerName, $this->output);
        }

        /* finish up and report everything */
        $this->writeln();
        $this->writeln('Done generating <info>'. $moduleName .'</info>');
        $this->writeln('You have to add the following line into your <comment>Application::loadModules()</comment> method in order to register this module:');
        $this->writeln(TAB . TAB . '$modules[] = new '. $namespace . NS . $moduleName .'();');
    }

    /**
     * Generates a module directory and classes and files from the given information.
     * 
     * @param string $name Short name of the module.
     * @param string $moduleName Full name of the module.
     * @param string $path Path at which the module should be generated.
     * @param string $urlPrefix URL prefix for the module.
     * @param string $applicationName Name of the application for which this controller should be generated.
     * @param bool $generateAssets [optional] Should also public assets dirs be generated? It will create .gitkeep files. Default: false.
     * @return bool
     */
    protected function generateModule($name, $moduleName, $namespace, $path, $urlPrefix, $applicationName, $generateAssets = false) {
        $finder = $this->get('resource_finder');
        $filesystem = $this->get('filesystem');

        $moduleTemplate = file_get_contents($finder->find('SplotDevToolsModule::Module.php.tpl', 'templates'));
        $moduleCode = StringUtils::parseVariables($moduleTemplate, array(
            'moduleName' => $moduleName,
            'date' => gmdate('d-m-Y H:i') .' GMT',
            'applicationName' => $applicationName,
            'namespace' => $namespace,
            'urlPrefix' => $urlPrefix
        ));
        $filesystem->dumpFile($path . DS . $moduleName .'.php', $moduleCode);

        $configTemplate = file_get_contents($finder->find('SplotDevToolsModule::config.php.tpl', 'templates'));
        $configCode = StringUtils::parseVariables($configTemplate, array(
            'moduleName' => $moduleName
        ));
        $filesystem->dumpFile($path . DS . 'Resources'. DS .'config'. DS .'config.php', $configCode);

        if ($generateAssets) {
            $assetsDirs = array('css', 'js', 'images', 'views', 'less', 'scss', 'fonts');
            foreach($assetsDirs as $type) {
                $filesystem->dumpFile($path . DS . 'Resources'. DS .'public'. DS . $type . DS . '.gitkeep', ' ');
            }
        }

        return true;
    }

    /*****************************************
     * HELPERS
     *****************************************/
    /**
     * Validates the short module name.
     * 
     * @param string $name Short name of the module to validate.
     * @param string $applicationName Name of the application for which to validate the module name.
     * @return string Valid short module name.
     * 
     * @throws \InvalidArgumentException When the given name is not valid.
     */
    protected function validateName($name, $applicationName) {
        if (substr($name, -6) === 'Module') {
            throw new \InvalidArgumentException('You should not add "Module" suffix to the module name, it will be added automatically where necessary, "'. $name .'" given.');
        }

        if (substr($name, 0, strlen($applicationName)) === $applicationName) {
            throw new \InvalidArgumentException('You should not add "'. $applicationName .'" (application name) suffix to the module name, it will be added automatically where necessary, "'. $name .'" given.');
        }

        return ucfirst($name);
    }

    /**
     * Asks user to confirm the application name and validates it.
     * 
     * @param string $applicationName Name of application.
     * @return string Valid application name.
     */
    protected function validateApplicationName($applicationName) {
        $applicationName = $this->ask('What is the application name (top level namespace part) for which the module should be created?',
            ucfirst($applicationName),
            array(),
            function($answer) {
                if (!StringUtils::isClassName($answer)) {
                    throw new \RuntimeException('Please provide a valid name that can be used as a namespace for the module.');
                }
                return $answer;
            }
        );
        return $applicationName;
    }

    /**
     * Validates full module name in context of an application.
     * 
     * @param string $name Short name of the module.
     * @param string $applicationName Name of the application for which the module is generated.
     * @param AbstractApplication $application Application for which the module is generated.
     * @return string Valid full module name.
     * 
     * @throws \InvalidArgumentException When the application already has a module with such name registered.
     */
    protected function validateModuleName($name, $applicationName, AbstractApplication $application) {
        $moduleName = $applicationName . ucfirst($name) .'Module';
        if ($application->hasModule($moduleName)) {
            throw new \InvalidArgumentException('The application already has a module called "'. $moduleName .'" registered.');
        }
        return $moduleName;
    }

    /**
     * Asks user to provide a namespace under which this module should be generated.
     * 
     * @param string $name Short name of the module.
     * @param string $applicationName Name of the application for which the module is generated.
     * @return string Valid namespace.
     */
    protected function validateNamespace($name, $applicationName) {
        $this->writeln('Provide a PHP namespace under which this module should be created. You can use both "/" or "\" namespace separators for convenience.');
        $this->writeln();

        $namespace = $this->ask('What namespace to use?', $applicationName .'\\Modules\\'. $name, array(), function($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('The namespace cannot be empty.');
            }

            $answer = str_replace('/', NS, $answer);
            $answer = trim($answer, '\ ');

            if (!StringUtils::isClassName($answer, true)) {
                throw new \InvalidArgumentException('The namespace is invalid, "'. $answer .'" given.');
            }

            $path = explode(NS, $answer);

            if (count($path) === 1) {
                throw new \RuntimeException('The namespace has to contain at least two elements. By convention it should be [ApplicationName]\Modules\[ModuleName]');
            }

            return $answer;
        });

        return $namespace;
    }

    /**
     * Asks user where to place the generated module and validates if it can be created there.
     * 
     * @param string $namespace Namespace for the module.
     * @return string Path where to generate the module.
     * 
     * @throws \RuntimeException If there already is a directory with this namespace.
     */
    protected function validatePath($namespace) {
        $this->writeln('Provide a location relative to the project\'s root directory in which this module should be created.'. NL .'Most usually this will be the <info>src/</info> directory.');
        $this->writeln();

        $location = $this->ask('Where to place the module?', 'src/');
        $location = $this->getParameter('root_dir') . ltrim($location, DS);

        $path = str_replace(NS, DS, $namespace);
        $path = trim($path, DS);
        $path = rtrim($location, DS) . DS . $path;

        if (is_dir($path) || file_exists($path)) {
            throw new \RuntimeException('There is a directory "'. $path .'" already. Please choose a different module name.');
        }

        return $path;
    }

    /**
     * Asks user to provide a URL prefix for the module.
     * 
     * @param string $name Short name of the module.
     * @return string Valid URL prefix with leading and trailing slashes.
     */
    protected function validateUrlPrefix($name) {
        $prefix = '/'. trim(strtolower($name), '/') .'/';
        $this->writeln('With what prefix should all the links (URL\'s) leading to controllers from this module be prefixed?');
        $this->writeln();
        $prefix = $this->ask('What URL prefix?', $prefix);
        $prefix = trim($prefix, '/');
        return (!empty($prefix)) ? '/'. $prefix : '';
    }

}