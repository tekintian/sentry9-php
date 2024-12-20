<?php

namespace Sentry9\processor;


use Sentry9\Client;

/**
 * This processor sanitizes the configured HTTP headers to ensure no sensitive
 * informations are sent to the server.
 *
 * @author tekintian <tekintian@gmail.com>
 */
final class SanitizeHttpHeadersProcessor extends Processor
{
    /**
     * @var string[] $httpHeadersToSanitize The list of HTTP headers to sanitize
     */
    private $httpHeadersToSanitize = array();

    /**
     * {@inheritdoc}
     */
    public function __construct(Client $client)
    {
        parent::__construct($client);
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessorOptions(array $options)
    {
        $this->httpHeadersToSanitize = array_merge($this->getDefaultHeaders(), isset($options['sanitize_http_headers']) ? $options['sanitize_http_headers'] : array());
    }

    /**
     * {@inheritdoc}
     */
    public function process(&$data)
    {
        if (isset($data['request']) && isset($data['request']['headers'])) {
            foreach ($data['request']['headers'] as $header => &$value) {
                if (in_array($header, $this->httpHeadersToSanitize)) {
                    $value = self::STRING_MASK;
                }
            }
        }
    }

    /**
     * Gets the list of default headers that must be sanitized.
     *
     * @return string[]
     */
    private function getDefaultHeaders()
    {
        return array('Authorization', 'Proxy-Authorization', 'X-Csrf-Token', 'X-CSRFToken', 'X-XSRF-TOKEN');
    }
}
