<?php
/**
 * Event triggered when cache is being cleared.
 * 
 * @package SplotDevToolsModule
 * @subpackage Events
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\DevToolsModule\Events;

use Splot\EventManager\AbstractEvent;

use Splot\Cache\CacheProvider;

class ClearCache extends AbstractEvent
{

    /**
     * Cache provider.
     * 
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * Constructor.
     * 
     * @param CacheProvider $cacheProvider Cache provider.
     */
    public function __construct(CacheProvider $cacheProvider) {
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * Returns the cache provider.
     * 
     * @return CacheProvider
     */
    public function getCacheProvider() {
        return $this->cacheProvider;
    }

}