<?php

namespace Sentry9;
/**
 * Utilities
 *
 * @package raven
 */

class Util
{
    /**
     * Because we love Python, this works much like dict.get() in Python.
     *
     * Returns $var from $array if set, otherwise returns $default.
     *
     * @param array  $array
     * @param string $var
     * @param mixed  $default
     * @return mixed
     */
    public static function get($array, $var, $default = null)
    {
        if (isset($array[$var])) {
            return $array[$var];
        }

        return $default;
    }
}
