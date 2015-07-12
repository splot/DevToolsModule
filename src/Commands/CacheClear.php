<?php
namespace Splot\DevToolsModule\Commands;

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
        $this->writeln('Clearing caches...');

        // clear container cache
        $this->get('container.cache')->flush();
        $this->writeln('    Cleared <comment>container</comment> cache.');

        // clear other registered caches
        $cacheProvider = $this->get('cache_provider');
        foreach($cacheProvider->getCaches() as $name => $cache) {
            $cache->flush();
            $this->writeln('    Cleared <comment>'. $name .'</comment> cache.');
        }

        // trigger cache clear event
        $this->get('event_manager')->trigger(new ClearCacheEvent($cacheProvider, $this->getLogger()));

        // after everything make sure that cache dir exists
        $cacheDir = rtrim($this->getParameter('cache_dir'), DS);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
            $this->writeln('<comment>Cache directory "'. $cacheDir .'" did not exist, so created it.');
        }

        $this->writeln('<info>Finished clearing all caches.</info>');
    }

}