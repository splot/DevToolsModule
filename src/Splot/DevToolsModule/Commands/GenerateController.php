<?php
namespace Splot\DevToolsModule\Commands;

use MD\Foundation\Utils\StringUtils;

use Splot\Framework\Modules\AbstractModule;
use Splot\Framework\Console\AbstractCommand;

class GenerateController extends AbstractCommand 
{

    protected static $name = 'generate:controller';
    protected static $description = 'Generates a controller.';

    protected static $arguments = array(
        'name' => 'Controller name in short notation, e.g. AcmeBlogModule:Index where "Index" is the controller name.'
    );

    /**
     * Generates a controller.
     * 
     * @param string $name Controller name in short notation, e.g. AcmeBlogModule:Index where "Index" is the controller name.
     */
    public function execute($name) {
        list($moduleName, $controllerName) = $this->validateName($name);
        $module = $this->validateModule($moduleName);
        $url = $this->validateUrl($controllerName);

        $controllerPath = str_replace(NS, DS, $controllerName);
        $fullPath = rtrim($module->getModuleDir(), DS) . DS .'Controllers'. DS . trim(str_replace(NS, DS, $controllerName), DS) .'.php';

        /* Figure out namespace and class */
        $controllerNamespacePath = str_replace('/', NS, $controllerName);
        $controllerNamespacePath = trim($controllerNamespacePath, NS);
        $controllerNamespacePath = explode(NS, $controllerNamespacePath);
        $controllerClass = array_pop($controllerNamespacePath);
        $controllerNamespace = (count($controllerNamespacePath)) ? NS . implode(NS, $controllerNamespacePath) : '';

        /* Get code */
        $controllerTemplate = file_get_contents($this->get('resource_finder')->find('SplotDevToolsModule::Controller.php.tpl', 'templates'));
        $controllerCode = StringUtils::parseVariables($controllerTemplate, array(
            'namespace' => $module->getNamespace(),
            'controllerNamespace' => $controllerNamespace,
            'controllerClass' => $controllerClass,
            'url' => $url
        ));

        $filesystem = $this->get('filesystem');

        if ($filesystem->exists($fullPath)) {
            throw new \InvalidArgumentException('Controller with name "'. $name .'" already exists.');
        }

        /* Ask for final confirmation */
        if (!$this->confirm('Are you sure you want to generate a controller <info>'. $name .'</info>'. NL . TAB .'in <comment>'. $fullPath .'</comment>?', true)) {
            $this->writeln('Canceled.');
            return 0;
        }

        /* Generate! */
        $this->write('Generating...');
        $filesystem->dumpFile($fullPath, $controllerCode);
        $this->writeln('done.');

        // also ask if a default template should be generated
        if (!$this->get('application')->hasModule('SplotTwigModule') || !$this->confirm('Do you want to generate a Twig template for this controller as well?', true)) {
            return 1;
        }

        $templatePath = rtrim($module->getModuleDir(), DS) . DS .'Resources'. DS .'views'. DS . trim($controllerPath, DS) . DS .'index.html.twig';
        $templateCode = file_get_contents($this->get('resource_finder')->find('SplotDevToolsModule::view.html.twig.tpl', 'templates'));
        $filesystem->dumpFile($templatePath, $templateCode);
        
        $this->writeln('Done.');
        $this->writeln();
    }

    /*****************************************
     * HELPERS
     *****************************************/
    /**
     * Validate controller name.
     * 
     * @param string $name Controller name.
     * @return array [0] => module name, [1] => contoller name
     * 
     * @throws \InvalidArgumentException When name is invalid for any reason.
     */
    protected function validateName($name) {
        $nameExploded = explode(':', $name);
        if (count($nameExploded) !== 2) {
            throw new \InvalidArgumentException('Invalid controller name, it should be in format "FullModuleName:ControllerName", "'. $name .'" given.');
        }

        $nameExploded = array_map('trim', $nameExploded);
        if (empty($nameExploded[0]) || empty($nameExploded[1])) {
            throw new \InvalidArgumentException('Neither module name nor controller name can be empty, "'. $name .'" given.');
        }

        $nameExploded[1] = str_replace('/', NS, $nameExploded[1]);
        if (!StringUtils::isClassName($nameExploded[1], true)) {
            throw new \InvalidArgumentException('Controller name has to be a valid class name (or namespaced class name), "'. $nameExploded[1] .'" given.');
        }

        return $nameExploded;
    }

    /**
     * Validate module.
     * 
     * @param string $name Module name.
     * @return AbstractModule
     * 
     * @throws \InvalidArgumentException When module name is invalid.
     */
    protected function validateModule($name) {
        $application = $this->get('application');
        if (!$application->hasModule($name)) {
            throw new \InvalidArgumentException('There is currently no module called "'. $name .'" registered in the application.');
        }

        return $application->getModule($name);
    }

    /**
     * Asks the user to what URL should the controller respond and creates the default based on the controller name.
     * 
     * @param string $name Name of the controller.
     * @return string
     */
    protected function validateUrl($name) {
        $url = StringUtils::urlFriendly($name);
        $url = $this->ask('To what URL should this controller respond?', $url);
        $url = trim($url, '/');
        return (!empty($url)) ? '/'. $url .'/' : '';
    }

}