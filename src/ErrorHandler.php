<?php

namespace Sentry9;

/**
 * 
 * 异常和错误的事件处理程序
 * 
 * Event handlers for exceptions and errors
 * 
 * sentry8
 * $client = new Client('http://public:secret/example.com/1');
 * or Sentry9
 * $client = new Client('http://public/example.com/1');
 * 
 * $error_handler = new ErrorHandler($client);
 * $error_handler->registerExceptionHandler();
 * $error_handler->registerErrorHandler();
 * $error_handler->registerShutdownFunction();
 *
 * @package Sentry9
 */

class ErrorHandler
{
    protected $old_exception_handler;
    protected $call_existing_exception_handler = false;
    protected $old_error_handler;
    protected $call_existing_error_handler = false;
    protected $reservedMemory;
    protected $client;
    protected $send_errors_last = false;
    protected $fatal_error_types = array(
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_CORE_WARNING,
        E_COMPILE_ERROR,
        E_COMPILE_WARNING,
        E_STRICT,
    );

    /**
     * @var array
     * Error types which should be processed by the handler.
     * A 'null' value implies "whatever error_reporting is at time of error".
     */
    protected $error_types = null;
    
    /** @var \Exception|null */
    private $lastHandledException;

    public function __construct($client, $send_errors_last = false, $error_types = null,
                                $__error_types = null)
    {
        // support legacy fourth argument for error types
        if ($error_types === null) {
            $error_types = $__error_types;
        }

        $this->client = $client;
        $this->error_types = $error_types;
        $this->fatal_error_types = array_reduce($this->fatal_error_types, array($this, 'bitwiseOr'));
        if ($send_errors_last) {
            $this->send_errors_last = true;
            $this->client->store_errors_for_bulk_send = true;
        }
    }

    public function bitwiseOr($a, $b)
    {
        return $a | $b;
    }

    public function handleException($e, $isError = false, $vars = null)
    {
        // 如果这里的$e 不是一个MyErrorException的实例，就new一个MyErrorException,
        // 解决 php 8.2 无法动态创建属性提示问题
        if (!is_a($e, 'MyErrorException')) {
            $e = new MyErrorException($e->getMessage(), 0, $e->getCode(), $e->getFile(), $e->getLine());
        }
        $e->event_id = $this->client->captureException($e, null, null, $vars);
        $this->lastHandledException = $e;

        if (!$isError && $this->call_existing_exception_handler) {
            if ($this->old_exception_handler !== null) {
                call_user_func($this->old_exception_handler, $e);
            } else {
                throw $e;
            }
        }
    }

    public function handleError($type, $message, $file = '', $line = 0, $context = array())
    {
        // http://php.net/set_error_handler
        // The following error types cannot be handled with a user defined function: E_ERROR,
        // E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, and
        // most of E_STRICT raised in the file where set_error_handler() is called.

        if (error_reporting() !== 0) {
            $error_types = $this->error_types;
            if ($error_types === null) {
                $error_types = error_reporting();
            }
            if ($error_types & $type) {
                $e = new MyErrorException($message, 0, $type, $file, $line);
                $this->handleException($e, true, $context);
            }
        }

        if ($this->call_existing_error_handler) {
            if ($this->old_error_handler !== null) {
                return call_user_func(
                    $this->old_error_handler,
                    $type,
                    $message,
                    $file,
                    $line,
                    $context
                );
            } else {
                return false;
            }
        }
        return true;
    }

    public function handleFatalError()
    {
        unset($this->reservedMemory);

        if (null === $error = error_get_last()) {
            return;
        }

        if ($this->shouldCaptureFatalError($error['type'], $error['message'])) {
            $e = new MyErrorException(
                @$error['message'], 0, @$error['type'],
                @$error['file'], @$error['line']
            );
            
            $this->client->useCompression = $this->client->useCompression && PHP_VERSION_ID > 70000;
            $this->handleException($e, true);
        }
    }

    /**
     * @param int $type
     * @param string|null $message
     * @return bool
     */
    public function shouldCaptureFatalError($type, $message = null)
    {
        if (PHP_VERSION_ID >= 70000 && $this->lastHandledException) {
            if ($type === E_CORE_ERROR && strpos($message, 'Exception thrown without a stack frame') === 0) {
                return false;
            }

            if ($type === E_ERROR) {
                $expectedMessage = 'Uncaught '
                    . \get_class($this->lastHandledException)
                    . ': '
                    . $this->lastHandledException->getMessage();

                if (strpos($message, $expectedMessage) === 0) {
                    return false;
                }
            }
        }       

        return (boolean)($type & $this->fatal_error_types);
    }

    /**
     * Register a handler which will intercept unhandled exceptions and report them to the
     * associated Sentry client.
     *
     * @param bool $call_existing Call any existing exception handlers after processing
     *                            this instance.
     * @return ErrorHandler
     */
    public function registerExceptionHandler($call_existing = true)
    {
        $this->old_exception_handler = set_exception_handler(array($this, 'handleException'));
        $this->call_existing_exception_handler = $call_existing;
        return $this;
    }

    /**
     * Register a handler which will intercept standard PHP errors and report them to the
     * associated Sentry client.
     *
     * @param bool  $call_existing Call any existing errors handlers after processing
     *                             this instance.
     * @param array $error_types   All error types that should be sent.
     * @return ErrorHandler
     */
    public function registerErrorHandler($call_existing = true, $error_types = null)
    {
        if ($error_types !== null) {
            $this->error_types = $error_types;
        }
        $this->old_error_handler = set_error_handler(array($this, 'handleError'), E_ALL);
        $this->call_existing_error_handler = $call_existing;
        return $this;
    }

    /**
     * Register a fatal error handler, which will attempt to capture errors which
     * shutdown the PHP process. These are commonly things like OOM or timeouts.
     *
     * @param int $reservedMemorySize Number of kilobytes memory space to reserve,
     *                                which is utilized when handling fatal errors.
     * @return ErrorHandler
     */
    public function registerShutdownFunction($reservedMemorySize = 10)
    {
        register_shutdown_function(array($this, 'handleFatalError'));

        $this->reservedMemory = str_repeat('x', 1024 * $reservedMemorySize);
        return $this;
    }
}
