<?php

use IlluminateAgnostic\Str\Support\Str;
use PragmaRX\Coollection\Package\Coollection;

if (! function_exists('coollect')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed  $value
     * @return \PragmaRX\Coollection\Package\Coollection
     */
    function coollect($value = null)
    {
        if ($value instanceof Coollection) {
            return $value;
        }

        return new Coollection($value);
    }
}

if (! function_exists('snake')) {
    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    function snake($value, $delimiter = '_')
    {
        return Str::snake($value, $delimiter);
    }
}

if (! function_exists('lower')) {
    /**
     * Convert the given string to lower-case.
     *
     * @param  string  $value
     * @return string
     */
    function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }
}

if (! function_exists('upper')) {
    /**
     * Convert the given string to lower-case.
     *
     * @param  string  $value
     * @return string
     */
    function upper($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }
}

/**
 * @codeCoverageIgnore
 */
if (! function_exists('dump')) {
    function dump(...$args)
    {
        foreach ($args as $value) {
            var_dump($value);
        }
    }
}

/**
 * @codeCoverageIgnore
 */
if (! function_exists('dd')) {
    function dd(...$args)
    {
        dump(...$args);

        die;
    }
}

if (! function_exists('starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    function starts_with($haystack, $needles)
    {
        return Str::startsWith($haystack, $needles);
    }
}

if (! function_exists('array_sort_by_keys_recursive')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param array $array
     */
    function array_sort_by_keys_recursive(array &$array)
    {
        ksort($array, SORT_NATURAL);

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                array_sort_by_keys_recursive($array[$key]);
            }
        }
    }
}
