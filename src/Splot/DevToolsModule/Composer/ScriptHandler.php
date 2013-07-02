<?php
/**
 * Clears cache after composer actions.
 * 
 * Should be executed as post-update and post-install commands on composer.
 * 
 * @package SplotDevToolsModule
 * @subpackage Composer
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\DevToolsModule\Composer;

use Composer\Script\Event;

use Splot\Framework\Composer\AbstractScriptHandler;

class ScriptHandler extends AbstractScriptHandler
{

    /**
     * Triggers clearing of caches.
     * 
     * @param Event $event Composer event.
     */
    public static function clearCache(Event $event) {
        $application = self::boot();
        $console = $application->getContainer()->get('console');
        $console->call('cache:clear');
    }

}