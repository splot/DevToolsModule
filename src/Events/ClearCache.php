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

use Psr\Log\LoggerInterface;

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
     * Logger.
     *
     * @var LoggerInterface|null
     */
    private $logger = null;

    /**
     * Constructor.
     * 
     * @param CacheProvider $cacheProvider Cache provider.
     * @param LoggerInterface $logger [optional] A specific logger that may be used for listeners to log reactions.
     */
    public function __construct(CacheProvider $cacheProvider, LoggerInterface $logger = null) {
        $this->cacheProvider = $cacheProvider;
        $this->logger = $logger;
    }

    /**
     * Returns the cache provider.
     * 
     * @return CacheProvider
     */
    public function getCacheProvider() {
        return $this->cacheProvider;
    }

    /**
     * Gets a logger that may be used for listeners to log reactions.
     *
     * @return LoggerInterface|null
     */
    public function getLogger() {
        return $this->logger;
    }
}
