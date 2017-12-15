<?php
use Phalcon\Di\FactoryDefault;

error_reporting(E_ALL);
define('APP_PATH', dirname(__DIR__));
define('BASE_PATH', APP_PATH . '/..');

function php_error_handler($errno, $errstr, $errfile, $errline)
{
    global $application,$di;
    $message = $errstr . '=>' . $errfile. '[' . $errline. ']';
    if ($di->getConfig()->environment=='dev') {
        echo $message;
        $application->logger->error($message);
        exit(1);
    }
    $application->logger->error($message);
//    $application->view->setVar('message', 'Catch error:Your Request Page is not exist');
//    $content = $application->view->render('public', 'error');
    $content = [
        'code' => $errno,
    ];
    $application->response->setJsonContent($content);
    $application->response->setContent($content);
    $application->response->setStatusCode(401);
    $application->response->send();
}

/**
 * @param $e Exception
 */
function exception_handler($e)
{
    global $di;
    global $application;
    $content = [];
    if ($e instanceof \Phalcon\Mvc\Dispatcher\Exception) {
        $content['message'] = 'Not Found';
        $content['router'] = $application->dispatcher->getControllerName() . '/' . $application->dispatcher->getActionName();
    } else {
        $message ='Catch Exception:' . $e->getMessage() . ':' . $e->getTraceAsString();
        if ($di->getConfig()->environment=='dev') {
            echo $message;
            $application->logger->error($message);
            exit(1);
        }
        $application->logger->error($message);
    }
//    $application->view->setVar('message', 'Catch exception:Your Request Page is not exist');
//    $content = $application->view->render('public', 'error');
    $content['code'] = $e->getCode();
    $application->response->setJsonContent($content);
    $application->response->setStatusCode(402);
    $application->response->send();
}


    /**
     * The FactoryDefault Dependency Injector automatically registers
     * the services that provide a full stack framework.
     */
    $di = new FactoryDefault();

    /**
     * Handle routes
     */
    include APP_PATH . '/config/router.php';

    /**
     * Read services
     */
    include APP_PATH . '/config/services.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    $loader = include APP_PATH . '/config/loader.php';

    /**
     * Handle the request
     */
    $application = \app\common\libs\Application::getApp($di);

    if ($config->debug) {
        $loadEvents = new \app\common\events\LoadEvent();
        $loadEventManager = new \Phalcon\Events\Manager();
        $loadEventManager->attach('loader', $loadEvents);
        $loader->setEventsManager($loadEventManager);

        $events = new \app\common\events\ApplicationEvent();
        $manager = new \Phalcon\Events\Manager();
        $manager->attach('application', $events);
        $application->setEventsManager($manager);

    }

    set_error_handler('php_error_handler');
    set_exception_handler('exception_handler');

    $application->useImplicitView(false);

    $response = $application->handle();

    $response->send();