<?php
/**
 * Clears PHP's opcache by creating a php file in the web folder and making a call
 * to the webserver to execute it. The created file clears the opcache.
 * 
 * @package SplotDevToolsModule
 * @subpackage Cache
 * @author Michał Pałys-Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2015, Michał Dudek
 * @license MIT
 */
namespace Splot\DevToolsModule\Cache;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;

use MD\Foundation\Utils\StringUtils;

use Symfony\Component\Filesystem\Filesystem;

use Splot\DevToolsModule\Events\ClearCache;
use Splot\Framework\Resources\Finder;
use Splot\Framework\Routes\Router;

class ClearOpcache implements LoggerAwareInterface
{

    /**
     * Resource finder.
     *
     * @var Finder
     */
    protected $resourceFinder;

    /**
     * Router
     *
     * @var Router
     */
    protected $router;

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Path to web dir.
     *
     * @var string
     */
    protected $webDir;

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param Finder               $resourceFinder Resource finder.
     * @param Router               $router         Router.
     * @param Filesystem           $filesystem     Filesystem.
     * @param string               $webDir         Path to the web dir.
     * @param LoggerInterface|null $logger         [optional] Logger.
     */
    public function __construct(
        Finder $resourceFinder,
        Router $router,
        Filesystem $filesystem,
        $webDir,
        LoggerInterface $logger = null
    ) {
        $this->resourceFinder = $resourceFinder;
        $this->router = $router;
        $this->filesystem = $filesystem;
        $this->webDir = rtrim($webDir, '/');
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Clears the web server's opcache by creating a PHP file in the web dir and executing it with a request.
     *
     * The file is afterwards removed.
     *
     * @return boolean
     */
    public function clear()
    {
        // create a file that will be accessed by the web server to clear the cache
        $templateFile = $this->resourceFinder->find('SplotDevToolsModule::clear_opcache.php.tpl', 'templates');
        $template = file_get_contents($templateFile);

        $targetFileName = 'clear_opcache_'. md5(uniqid() . php_uname() . StringUtils::random()) .'.php';
        $targetFile = $this->webDir .'/'. $targetFileName;

        $this->filesystem->dumpFile($targetFile, $template);

        // access this file with web request
        $url = rtrim($this->router->getProtocolAndHost(), '/') .'/'. $targetFileName;
        
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL             => $url,
            CURLOPT_POST            => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FAILONERROR     => true,
            CURLOPT_HEADER          => false,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false
        ));
        $result = curl_exec($ch);

        // we can now safely remove the file
        $this->filesystem->remove($targetFile);

        // and parse the response to check if it worked
        $error = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);

        if ($error || empty($result) || $result !== 'OK') {
            $this->logger->warning(sprintf(
                'Failed to clear opcache by calling the web server at "%s". Error: "%s"',
                $url,
                $error ? $error : $result
            ));
            return false;
        }

        $this->logger->info('    Cleared {cache} by calling {url}', array(
            'cache' => 'opcache',
            'url' => $url
        ));

        return true;
    }

    /**
     * Event listener for ClearCache event.
     *
     * @param  ClearCache $event The event.
     */
    public function onCacheClear(ClearCache $event)
    {
        // if PHP version lower than 5.5 then there's no opcache, then silently exit
        if (version_compare(phpversion(), '5.5', '<')) {
            return;
        }

        if ($logger = $event->getLogger()) {
            $this->setLogger($logger);
        }

        $this->clear();
    }

    /**
     * {inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
