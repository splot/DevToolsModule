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
     * You should register any event listeners or services or perform any configuration in this function.
     * 
     * It it called on application boot.
     */
    public function boot() {
        
    }

}