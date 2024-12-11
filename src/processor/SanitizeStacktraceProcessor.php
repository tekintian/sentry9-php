<?php

namespace Sentry9\processor;


/**
 * This processor removes the `pre_context`, `context_line` and `post_context`
 * informations from all exceptions captured by an event.
 *
 * @author tekintian <tekintian@gmail.com>
 */
class SanitizeStacktraceProcessor extends Processor
{
    /**
     * {@inheritdoc}
     */
    public function process(&$data)
    {
        if (!isset($data['exception'], $data['exception']['values'])) {
            return;
        }

        foreach ($data['exception']['values'] as &$exception) {
            if (!isset($exception['stacktrace'])) {
                continue;
            }

            foreach ($exception['stacktrace']['frames'] as &$frame) {
                unset($frame['pre_context'], $frame['context_line'], $frame['post_context']);
            }
        }
    }
}
