<?php

namespace Sentry9;

class TransactionStack
{
    private $stack=[];
    public function __construct()
    {
        $this->stack = array();
    }

    public function clear()
    {
        $this->stack = array();
    }

    public function peek()
    {
        $len = count($this->stack);
        if ($len === 0) {
            return null;
        }
        return $this->stack[$len - 1];
    }

    public function push($context)
    {
        $this->stack[] = $context;
    }

    /** @noinspection PhpInconsistentReturnPointsInspection
     * @param string|null $context
     * @return mixed
     */
    public function pop($context = null)
    {
        if (!$context) {
            return array_pop($this->stack);
        }
        while (!empty($this->stack)) {
            if (array_pop($this->stack) === $context) {
                return $context;
            }
        }
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
}
