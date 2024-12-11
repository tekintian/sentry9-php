<?php

namespace Sentry9\processor;


/**
 * This processor removes all the data of the HTTP body to ensure no sensitive
 * informations are sent to the server in case the request method is POST, PUT,
 * PATCH or DELETE.
 *
 * @author tekintian <tekintian@gmail.com>
 */
final class RemoveHttpBodyProcessor extends Processor
{
    /**
     * {@inheritdoc}
     */
    public function process(&$data)
    {
        if (isset($data['request'], $data['request']['method']) && in_array(strtoupper($data['request']['method']), array('POST', 'PUT', 'PATCH', 'DELETE'))) {
            $data['request']['data'] = self::STRING_MASK;
        }
    }
}
