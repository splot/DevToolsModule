<?php
namespace Splot\DevToolsModule\Commands;

use MD\Foundation\Utils\StringUtils;

use Splot\Framework\Console\AbstractCommand;

use Splot\DevToolsModule\Events\ClearCache as ClearCacheEvent;

class CacheClear extends AbstractCommand 
{

    protected static $name = 'cache:clear';
    protected static $description = 'Clears all caches.';

    protected static $arguments = array();

    /**
     * Clears all caches.
     */
    public function execute() {
        $this->write('Clearing cache...');
        $cacheProvider = $this->get('cache_provider');

        foreach($cacheProvider->getCaches() as $cache) {
            $cache->flush();
        }

        // also clear the file system cache to make sure all caches are cleared
        $cacheDir = $this->getParameter('cache_dir');
        $filesystem = $this->get('filesystem');
        $clear = array(
            'twig',
            strtolower($this->getParameter('env'))
        );

        foreach($clear as $dir) {
            $filesystem->remove($cacheDir . $dir);
        }

        // trigger cache clear event
        $this->get('event_manager')->trigger(new ClearCacheEvent($cacheProvider));

        $this->writeln(' <info>done</info>');
    }

}