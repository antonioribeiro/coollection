<?php

namespace PragmaRX\Coollection\Package;

use Exception;
use Traversable;
use JsonSerializable;
use Illuminate\Support\HigherOrderCollectionProxy;
use Tightenco\Collect\Contracts\Support\Arrayable;
use Tightenco\Collect\Contracts\Support\Jsonable;
use Tightenco\Collect\Support\Collection as TightencoCollection;

class Coollection extends TightencoCollection
{
    /**
     * Consants
     */
    const NOT_FOUND = '!__NOT__FOUND__!';

    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $__items;

    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $allowItems = false;

    /**
     * Raise exception on null.
     *
     * @static boolean
     */
    public static $raiseExceptionOnNull = true;

    /**
     * Create a new coollection.
     *
     * @param  mixed  $items
     * @return void
     */
    public function __construct($items = [])
    {
        parent::__construct($items);

        $this->__items = $this->items;

        unset($this->items);
    }

    /**
     * Dynamically access collection proxies.
     *
     * @param  string  $key
     * @return mixed|static
     *
     * @throws \Exception
     */
    public function __get($key)
    {
        if ($key == 'items') {
            return $this->__items;
        }

        if (property_exists($this, $key)) {
            return $this->{$key};
        }

        if (($value = $this->getByPropertyName($key)) !== static::NOT_FOUND) {
            return $value;
        }

        if (!in_array($key, static::$proxies)) {
            if (static::$raiseExceptionOnNull) {
                throw new Exception("Property [{$key}] does not exist on this collection instance.");
            }

            return null;
        }

        return new HigherOrderCollectionProxy(collect($this), $key);
    }

    /**
     * Dynamically access collection proxies.
     *
     * @param  string  $key
     * @return mixed|static
     *
     * @throws \Exception
     */
    public function __isset($key)
    {
        if ($key == 'items') {
            $key = '__items';
        }

        return isset($this->{$key});
    }

    /**
     * Dynamically access collection proxies.
     *
     * @param  string  $key
     *
     * @throws \Exception
     */
    public function __set($key, $value)
    {
        if ($key == 'items') {
            $key = $this->allowItems ? 'items' : '__items';

            $this->{$key} = $value;

            return;
        }

        if (property_exists($this, $key)) {
            $this->{$key} = $value;

            return;
        }

        throw new Exception("Property [{$key}] does not exist on this collection instance.");
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->toArray();
    }

    /**
     * Create the items array based on the internal __items.
     */
    private function createItems()
    {
        $this->allowItems = true;

        $this->items = $this->__toArray();
    }

    /**
     * To array.
     *
     * @param $items
     * @return mixed|null
     */
    public function __toArray($items = null)
    {
        $items = is_null($items) ? $this->__items : $items;

        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $items instanceof Arrayable ? $items->toArray() : $items);
    }

    /**
     * Store and drop items.
     */
    private function dropItems()
    {
        $this->__items = $this->items;

        unset($this->items);

        $this->allowItems = false;
    }

    /**
     * Execute a callback over each item.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        return $this->__wrap(
            parent::each(
                $this->coollectizeCallback($callback)
            )
        );
    }

    /**
     * Run a filter over each of the items.
     *
     * @param  callable|null  $callback
     * @return static
     */
    public function filter(callable $callback = null)
    {
        return $this->runViaLaravelCollection(function () use ($callback) {
            return parent::filter(
                $this->coollectizeCallback($callback)
            );
        });
    }

    /**
     * Take the first item.
     *
     * @param callable|null $callback
     * @param null $default
     * @return mixed|static
     */
    public function first(callable $callback = null, $default = null)
    {
        return $this->__wrap(
            parent::first(
                $this->coollectizeCallback($callback),
                $default
            )
        );
    }

    /**
     * Get an item from the collection by key.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->__wrap(parent::get($key, $default));
    }

    /**
     * Get an array as a key.
     *
     * @param $key
     * @return mixed|string
     */
    private function getArrayKey($key)
    {
        if (array_key_exists($key, $this->__toArray())) {
            return $key;
        }

        $value = $this->keys()->mapWithKeys(function ($item) {
            return [snake($item) => $item];
        })->get($key);

        return is_null($value)
            ? static::NOT_FOUND
            : $value;
    }

    /**
     * Get a property by name.
     *
     * @param $key
     * @return string|static
     */
    private function getByPropertyName($key)
    {
        if (($key = $this->getArrayKey($key)) !== static::NOT_FOUND) {
            if (is_array($this->__items[$key])) {
                return $this->__wrap($this->__items[$key]);
            }

            return $this->__items[$key];
        }

        return static::NOT_FOUND;
    }

    /**
     * Get the keys of the collection items.
     *
     * @return static
     */
    public function keys()
    {
        return $this->runViaLaravelCollection(function () {
            return parent::keys();
        });
    }

    /**
     * Run a map over each of the items.
     *
     * @param  callable  $callback
     * @return static
     */
    public function map(callable $callback)
    {
        return $this->runViaLaravelCollection(function () use ($callback) {
            return parent::map(
                $this->coollectizeCallback($callback)
            );
        });
    }

    /**
     * Run a dictionary map over the items.
     *
     * The callback should return an associative array with a single key/value pair.
     *
     * @param  callable  $callback
     * @return static
     */
    public function mapToDictionary(callable $callback)
    {
        return $this->runViaLaravelCollection(function () use ($callback) {
            return parent::mapToDictionary(
                $callback
            );
        });
    }

    /**
     * Execute a closure via Laravel's Collection
     * @param $param
     * @return Coollection|array
     */
    private function runViaLaravelCollection($param)
    {
        $this->createItems();

        $result = $this->__wrap($param());

        $this->dropItems();

        return $result;
    }

    /**
     * Should it raise exception when the property is null?
     *
     * @return bool
     */
    public static function shouldRaiseExceptionOnNull()
    {
        return self::$raiseExceptionOnNull;
    }

    /**
     * Get and remove the last item from the collection.
     *
     * @return mixed|static
     */
    public function pop()
    {
        return $this->runViaLaravelCollection(function () {
            return parent::pop();
        });
    }

    /**
     * Get and remove an item from the collection.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return $this->runViaLaravelCollection(function () use ($key, $default) {
            return parent::pull($key, $default);
        });
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param  callable  $callback
     * @param  mixed  $initial
     * @return mixed|static
     */
    public function reduce(callable $callback, $initial = null)
    {
        return $this->runViaLaravelCollection(function () use ($callback, $initial) {
            return parent::reduce(
                $this->coollectizeCallbackForReduce($callback),
                $initial
            );
        });
    }

    /**
     * Merge the collection with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function merge($items)
    {
        return $this->runViaLaravelCollection(function () use ($items) {
            return parent::merge($items);
        });
    }

    /**
     * Raise exception on null setter.
     *
     * @param bool $raiseExceptionOnNull
     */
    public static function setRaiseExceptionOnNull(bool $raiseExceptionOnNull)
    {
        self::$raiseExceptionOnNull = $raiseExceptionOnNull;
    }

    /**
     * Get and remove the first item from the collection.
     *
     * @return mixed|static
     */
    public function shift()
    {
        return $this->runViaLaravelCollection(function () {
            return parent::shift();
        });
    }

    /**
     * Splice a portion of the underlying collection array.
     *
     * @param  int  $offset
     * @param  int|null  $length
     * @param  mixed  $replacement
     * @return static
     */
    public function splice($offset, $length = null, $replacement = [])
    {
        $args = func_num_args();

        return $this->runViaLaravelCollection(function () use ($offset, $length, $replacement, $args) {
            if ($args == 1) {
                return parent::splice($offset);
            }

            return parent::splice($offset, $length, $replacement);
        });
    }

    /**
     * Check if value is arrayable
     *
     * @param  mixed  $items
     * @return bool
     */
    protected function isArrayable($items)
    {
        return
            is_array($items) ||
            $items instanceof self ||
            $items instanceof Arrayable ||
            $items instanceof Jsonable ||
            $items instanceof JsonSerializable ||
            $items instanceof Traversable
            ;
    }

    /**
     * Wrap on static if the value is arrayable.
     *
     * @param $value
     * @return static
     */
    protected function __wrap($value)
    {
        if (is_object($value)) {
            return $value;
        }

        return $this->isArrayable($value)
            ? new static(parent::wrap($value)->toArray())
            : $value;
    }

    /**
     * Get the last item from the collection.
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed|static
     */
    public function last(callable $callback = null, $default = null)
    {
        return $this->__wrap(parent::last($callback, $default));
    }

    /**
     * Get one or a specified number of items randomly from the collection.
     *
     * @param  int|null  $number
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function random($number = null)
    {
        return $this->__wrap(parent::random($number));
    }

    /**
     * Get the mode of a given key.
     *
     * @param  mixed  $key
     * @return mixed|static
     */
    public function mode($key = null)
    {
        return $this->__wrap(parent::mode($key));
    }

    /**
     * ORIGINAL IS BROKEN IN LARAVEL
     * PR: https://github.com/laravel/framework/pull/21854#issuecomment-340220246
     */
    public function unique($key = null, $strict = false)
    {
        $callback = $this->valueRetriever($key);

        $exists = [];

        return $this->reject(function ($item, $key) use ($callback, $strict, &$exists) {
            if (in_array($id = $callback($item, $key), $exists, $strict)) {
                return true;
            }

            $exists[] = $id;
        });
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->__items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->__items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->__items[] = $value;
        } else {
            $this->__items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->__items[$key]);
    }

    /**
     * @param $originalCallback
     * @return callable
     */
    public function coollectizeCallback(callable $originalCallback = null)
    {
        if (is_null($originalCallback)) {
            return null;
        }

        return function ($value, $key) use ($originalCallback) {
            return $originalCallback(
                $this->__wrap($value), $key
            );
        };
    }

    /**
     * @param $originalCallback
     * @return callable
     */
    public function coollectizeCallbackForReduce(callable $originalCallback = null)
    {
        if (is_null($originalCallback)) {
            return null;
        }

        return function ($carry, $item) use ($originalCallback) {
            return $originalCallback(
                $carry,
                $this->__wrap($item)
            );
        };
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        $this->createItems();

        $result = parent::toArray();

        $this->dropItems();

        return $result;
    }

    /**
     * Intersect the collection with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function intersect($items)
    {
        return $this->runViaLaravelCollection(function () use ($items) {
            return parent::intersect($items);
        });
    }

    /**
     * Intersect the collection with the given items by key.
     *
     * @param  mixed  $items
     * @return static
     */
    public function intersectByKeys($items)
    {
        return $this->runViaLaravelCollection(function () use ($items) {
            return parent::intersectByKeys($items);
        });
    }

    /**
     * Get the items in the collection whose keys are not present in the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function diffKeys($items)
    {
        return $this->runViaLaravelCollection(function () use ($items) {
            return parent::diffKeys($items);
        });
    }

    /**
     * Get the items in the collection whose keys and values are not present in the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function diffAssoc($items)
    {
        return $this->runViaLaravelCollection(function () use ($items) {
            return parent::diffAssoc($items);
        });
    }

    /**
     * Overwrite the original array with the
     *
     * @param $overwrite
     * @return Coollection
     */
    function overwrite($overwrite)
    {
        $this->__items = array_replace_recursive($this->__items, $this->getArrayableItems($overwrite));

        return $this;
    }
}
