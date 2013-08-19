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
     * Prefix that will be added to all URL's from this module's controllers.
     * 
     * @var string
     */
    protected $_urlPrefix = '{urlPrefix}';

    /**
     * Boot the module.
     * 
     * You should register any event listeners or services or perform any configuration in this method.
     * 
     * It is called on application boot. Keep in mind that results of this function (whatever the method does
     * to the application scope) may be cached, so you shouldn't perform any logic actions here as this method
     * sometimes might not be called.
     */
    public function boot() {
        
    }

    /**
     * Initialize the module.
     * 
     * This method is called after application and all its modules have been fully booted (and therefore all services
     * and event listeners registered). You can perform any actions here that reuse components from other modules.
     */
    public function init() {

    }

}