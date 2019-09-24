<?php

use IlluminateAgnostic\Str\Support\Str;
use PragmaRX\Coollection\Package\Coollection;

if (!function_exists('coollect')) {
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

if (!class_exists(Illuminate\Support\Collection::class)) {
    /**
     * @codeCoverageIgnore
     */
    if (!function_exists('dump')) {
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
    if (!function_exists('dd')) {
        function dd(...$args)
        {
            dump(...$args);

            die();
        }
    }

    if (!function_exists('array_sort_by_keys_recursive')) {
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

    if (! function_exists('with')) {
        /**
         * Return the given object. Useful for chaining.
         *
         * @param  mixed  $object
         * @return mixed
         */
        function with($object)
        {
            return $object;
        }
    }
}
