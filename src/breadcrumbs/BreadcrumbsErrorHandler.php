<?php

namespace Sentry9\breadcrumbs;

use Sentry9\Client;

class BreadcrumbsErrorHandler
{
    protected $existingHandler;

    /**
     * @var Client the client object that sends the message to the server
     */
    protected $ravenClient;

    /**
     * @param Client $ravenClient
     */
    public function __construct(Client $ravenClient)
    {
        $this->ravenClient = $ravenClient;
    }

    public function handleError($code, $message, $file = '', $line = 0, $context = array())
    {
        $this->ravenClient->breadcrumbs->record(array(
            'category' => 'error_reporting',
            'message' => $message,
            'level' => $this->ravenClient->translateSeverity($code),
            'data' => array(
                'code' => $code,
                'line' => $line,
                'file' => $file,
            ),
        ));

        if ($this->existingHandler !== null) {
            return call_user_func($this->existingHandler, $code, $message, $file, $line, $context);
        } else {
            return false;
        }
    }

    public function install()
    {
        $this->existingHandler = set_error_handler(array($this, 'handleError'), E_ALL);
        return $this;
    }
}
