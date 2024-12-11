<?php

namespace Sentry9\processor;


/**
 * This processor removes all the cookies from the request to ensure no sensitive
 * informations are sent to the server.
 *
 * @author tekintian <tekintian@gmail.com>
 */
final class RemoveCookiesProcessor extends Processor
{
    /**
     * {@inheritdoc}
     */
    public function process(&$data)
    {
        if (isset($data['request'])) {
            if (isset($data['request']['cookies'])) {
                $data['request']['cookies'] = self::STRING_MASK;
            }

            if (isset($data['request']['headers']) && isset($data['request']['headers']['Cookie'])) {
                $data['request']['headers']['Cookie'] = self::STRING_MASK;
            }
        }
    }
}
