<?php

namespace Sentry9;


/**
 * Serializes a value into a representation that should reasonably suggest
 * both the type and value, and be serializable into JSON.
 * @package raven
 */
class ReprSerializer extends Serializer
{
    protected function serializeValue($value)
    {
        if ($value === null) {
            return 'null';
        } elseif ($value === false) {
            return 'false';
        } elseif ($value === true) {
            return 'true';
        } elseif (is_float($value) && (int) $value == $value) {
            return $value.'.0';
        } elseif (is_integer($value) || is_float($value)) {
            return (string) $value;
        } elseif (is_object($value) || gettype($value) == 'object') {
            return 'Object '.get_class($value);
        } elseif (is_resource($value)) {
            return 'Resource '.get_resource_type($value);
        } elseif (is_array($value)) {
            return 'Array of length ' . count($value);
        } else {
            return $this->serializeString($value);
        }
    }
}
