<?php

namespace PragmaRX\Coollection\Package;

use Closure;
use Countable;
use Exception;
use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;
use IlluminateAgnostic\Str\Support\Str;
use IlluminateAgnostic\Arr\Support\Arr;
use IlluminateAgnostic\Collection\Support\Collection;
use IlluminateAgnostic\Collection\Support\Traits\Macroable;
use IlluminateAgnostic\Collection\Contracts\Support\Jsonable;
use IlluminateAgnostic\Collection\Contracts\Support\Arrayable;
use IlluminateAgnostic\Collection\Support\HigherOrderCollectionProxy;
use IlluminateAgnostic\Collection\Support\Collection as TightencoCollect;

class Coollection implements
    ArrayAccess,
    Arrayable,
    Countable,
    IteratorAggregate,
    Jsonable,
    JsonSerializable
{
    use Macroable {
        __call as __callMacro;
    }

    /**
     * Consants
     */
    const NOT_FOUND = '!__NOT__FOUND__!';

    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items;

    /**
     * Raise exception on null.
     *
     * @static boolean
     */
    public static $raiseExceptionOnNull = true;

    /**
     * The methods that can be proxied.
     *
     * @var array
     */
    protected static $proxies = [
        'average',
        'avg',
        'contains',
        'each',
        'every',
        'filter',
        'first',
        'flatMap',
        'keyBy',
        'map',
        'partition',
        'reject',
        'sortBy',
        'sortByDesc',
        'sum',
    ];

    /**
     * The methods that must return array.
     *
     * @var array
     */
    protected static $returnArray = ['toArray', 'jsonSerialize', 'unwrap'];

    /**
     * Cache __toArray results.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Create a new coollection.
     *
     * @param  mixed  $items
     */
    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Transfer calls to Illuminate\Collection.
     *
     * @param $name
     * @param $arguments
     * @return mixed|static
     */
    public function __call($name, $arguments)
    {
        if (static::hasMacro($name)) {
            return $this->__callMacro($name, $arguments);
        }

        return $this->call($name, $arguments);
    }

    /**
     * Transfer calls to Illuminate\Collection.
     *
     * @param $name
     * @param $arguments
     * @return mixed|static
     */
    public function call($name, $arguments = [])
    {
        return $this->runViaLaravelCollection(function ($collection) use (
            $name,
            $arguments
        ) {
            return call_user_func_array(
                [$collection, $name],
                $this->coollectizeCallbacks($this->__toArray($arguments), $name)
            );
        },
        $name);
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @param $value
     * @return Coollection
     */
    public function coollectizeItems($value)
    {
        $value = is_null($value) ? $this->items : $value;

        if (!$this->isArrayable($value)) {
            return $value;
        }

        $result = array_map(function ($value) {
            if ($this->isArrayable($value)) {
                return new static($value);
            }

            return $value;
        }, $this->getArrayableItems($value));

        return new static($result);
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
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
        if (($value = $this->getByPropertyName($key)) !== static::NOT_FOUND) {
            return $value;
        }

        if (!in_array($key, static::$proxies)) {
            if (static::$raiseExceptionOnNull) {
                throw new Exception(
                    "Property [{$key}] does not exist on this collection instance."
                );
            }

            return null;
        }

        return $this->runViaLaravelCollection(function ($collection) use (
            $key
        ) {
            return new HigherOrderCollectionProxy($collection, $key);
        });
    }

    /**
     * Recursive toArray().
     *
     * @param $value
     * @return array
     */
    public function __toArray($value = null)
    {
        $value = is_null($value) ? $this->items : $value;

        if (!$this->isArrayable($value)) {
            return $value;
        }

        $result = array_map(function ($value) {
            if ($this->isArrayable($value)) {
                return $this->__toArray($value);
            }

            return $value;
        }, $this->getArrayableItems($value));

        return $result;
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
        if (
            ($value = $this->call('get', [$key, static::NOT_FOUND])) ===
            static::NOT_FOUND
        ) {
            $value = Arr::get($this->items, $key, $default);

            if (is_array($value)) {
                $value = $this->__wrap($value);
            }
        }

        return $value;
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param  mixed  $items
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->getItems();
        } elseif ($items instanceof Collection) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }

    /**
     * Get an array as a key.
     *
     * @param $key
     * @return mixed|string
     */
    private function getArrayKey($key)
    {
        $data = $this->__toArray();

        $cases = [
            $key,
            $this->snakeCase($key),
            Str::lower($this->snakeCase($key)),
            $this->camelCase($key),
            $this->kebabCase($key),
            Str::lower($this->kebabCase($key)),
            $this->stringCase($key),
            Str::lower($this->stringCase($key)),
            Str::lower($key),
        ];

        $data = $this->filter(function ($value, $key) use ($cases) {
            return array_search($key, $cases) !== false ||
                array_search(Str::lower($key), $cases) !== false;
        })
            ->keys()
            ->first();

        return is_string($data) ? $data : static::NOT_FOUND;
    }

    /**
     * Transform string to snake case.
     *
     * @param $string
     * @return string
     */
    public function snakeCase($string)
    {
        return Str::snake($string);
    }

    /**
     * Transform anything to string case.
     *
     * @param $string
     * @return string
     */
    public function stringCase($string)
    {
        $string = $this->snakeCase($string);

        return str_replace('_', ' ', $string);
    }

    /**
     * Transform anything to kebab case.
     *
     * @param $string
     * @return string
     */
    public function kebabCase($string)
    {
        return Str::kebab($this->camelCase($string));
    }

    /**
     * Transform anything to camel case.
     *
     * @param $string
     * @return string
     */
    public function camelCase($string)
    {
        return Str::camel($string);
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
            if (is_array($this->items[$key])) {
                return $this->__wrap($this->items[$key]);
            }

            return $this->items[$key];
        }

        return static::NOT_FOUND;
    }

    /**
     * Execute a closure via Laravel's Collection
     *
     * @param $closure
     * @param null $method
     * @return mixed
     */
    private function runViaLaravelCollection($closure, $method = null)
    {
        $collection = new TightencoCollect($this->items);

        $result = $closure($collection);

        if (!$this->methodMustReturnArray($method)) {
            $result = $this->coollectizeItems($result);
        }

        $this->items = $collection->all();

        return $result;
    }

    /**
     * Does the method must return an array?
     *
     * @param $method
     * @return bool
     */
    public function methodMustReturnArray($method)
    {
        return in_array($method, static::$returnArray);
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
     * Check if value is arrayable
     *
     * @param  mixed  $items
     * @return bool
     */
    protected function isArrayable($items)
    {
        return is_array($items) ||
            $items instanceof self ||
            $items instanceof Arrayable ||
            $items instanceof Jsonable ||
            $items instanceof JsonSerializable ||
            $items instanceof Traversable;
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
            ? new static($this->wrap($value)->toArray())
            : $value;
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        if (!isset($this->items[$key])) {
            return null;
        }

        return $this->items[$key];
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
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
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
        unset($this->items[$key]);
    }

    /**
     * @param $items
     * @return array|Coollection
     * @internal param $originalCallback
     */
    public function coollectizeCallbacks($items, $method)
    {
        foreach ($items as $key => $item) {
            if ($item instanceof Closure) {
                $items[$key] =
                    $method === 'reduce'
                        ? $this->coollectizeCallbackForReduce($item)
                        : $this->coollectizeCallback($item);
            }
        }

        return $items;
    }

    /**
     * @param $originalCallback
     * @return callable
     */
    public function coollectizeCallback(callable $originalCallback = null)
    {
        return function ($value = null, $key = null) use ($originalCallback) {
            return $originalCallback($this->__wrap($value), $key);
        };
    }

    /**
     * @param $originalCallback
     * @return callable
     */
    public function coollectizeCallbackForReduce(callable $originalCallback)
    {
        return function ($carry, $item) use ($originalCallback) {
            return $originalCallback($carry, $this->__wrap($item));
        };
    }

    /**
     * Overwrite the original array with the
     *
     * @param $overwrite
     * @return Coollection
     */
    public function overwrite($overwrite)
    {
        $this->items = array_replace_recursive(
            $this->items,
            $this->getArrayableItems($overwrite)
        );

        return $this;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return $this->call('toJson', [$options]);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->call('jsonSerialize');
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return $this->call('count');
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->call('getIterator');
    }

    /**
     * Sort by key.
     *
     * @return Coollection
     */
    public function sortByKey()
    {
        $items = $this->items;

        ksort($items);

        return $this->__wrap($items);
    }

    /**
     * Recursively Sort by key.
     *
     * @return Coollection
     */
    public function sortByKeysRecursive()
    {
        $items = $this->toArray();

        array_sort_by_keys_recursive($items);

        return $this->__wrap($items);
    }

    /**
     * Get raw items.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Dynamically check a property exists on the underlying object.
     *
     * @param  mixed  $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->items[$name]);

        return false;
    }
}
