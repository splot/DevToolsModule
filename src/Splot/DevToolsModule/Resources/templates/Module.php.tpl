<?php
/**
 * Module class for {moduleName}.
 * 
 * Created on {date}.
 * 
 * @package {applicationName}
 */
namespace {namespace};

use Splot\Framework\Modules\AbstractModule;

class {moduleName} extends AbstractModule
{

    /**
     * Prefix that will be added to all URL's from this module.
     * 
     * @var string|null
     */
    protected $urlPrefix = '{urlPrefix}';

    /**
     * Namespace for all commands that belong to this module
     * 
     * @var string|null
     */
    protected $commandNamespace;

    /**
     * If the module depends on other modules then return those dependencies from this method.
     *
     * It works exactly the same as application's ::loadModules().
     * 
     * @return array
     */
    public function loadModules() {
        return array();
    }

    /**
     * This method is called on the module during configuration phase so you can register any services,
     * listeners etc here.
     *
     * It should not contain any logic, just wiring things together.
     *
     * If the module contains any routes they should be registered here.
     */
    public function configure() {
        parent::configure();
    }

    /**
     * This method is called on the module during the run phase. If you need you can include any logic
     * here.
     */
    public function run() {

    }

}