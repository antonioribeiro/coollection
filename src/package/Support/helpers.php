<?php

if (! function_exists('coollect')) {
    /**
     * Create a collection from the given value.
     *
     * @param  mixed  $value
     * @return \Tightenco\Collect\Support\Collection
     */
    function collect($value = null)
    {
        return new Coollection($value);
    }
}
