<?php

namespace Sumish;

use Closure;
use ArrayAccess;
use ReflectionClass;
use Psr\Container\ContainerInterface;

class Container implements ArrayAccess, ContainerInterface {
    protected $bindings = [];
    protected $definitions = [];
    protected $instances = [];
    protected $values = [];
    protected $parameters = [];

    public function __construct(array $components = []) {
        $this->push($components);
    }

    public function __get($id) {
        return $this[$id];
    }

    public function __set($id, $component) {
        $this[$id] = $component;
    }

    public function __call($id, $parameters) {
        return $this->resolveCallback($id, $parameters);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }

    public function offsetExists($offset) {
        return $this->has($offset);
    }

    public function offsetUnset($offset) {
        $this->unregister($offset);
    }

    public static function create(array $components = []) {
        static $container = null;

        if (is_null($container)) {
            $container = new self($components);
        }

        return $container;
    }

    public function get($id) {
        $parameters = $this->getParameters($id);
        return $this->resolveDefinition($id, $parameters);
    }

    public function reset() {
        $this->bindings = [];
        $this->definitions = [];
        $this->instances = [];
        $this->values = [];
        $this->parameters = [];

        return $this;
    }

    public function set($id, $component, $parameters = []) {
        if (!$this->has($id)) {
            $this->register($id, $component, $parameters);
        }
    }

    public function singleton($id, $component, $parameters = []) {
        $this->set($id, $component, $parameters);
    }

    public function push(array $components = []) {
        foreach ($components as $id => $component) {
            $this->set($id, $component);
        }
    }

    public function register($id, $component, $parameters = []) {
        if ($this->isBinding($component)) {
            $component = $component->bindTo($this);
            $this->bindings[$id] = true;
        }

        if ($this->isDefinition($component)) {
            $this->definitions[$id] = true;
        }

        if ($this->isInstance($component)) {
            $this->instances[$id] = true;
        }

        $parameters = $this->prepareParameters($parameters);

        $this->setParameters($id, $parameters);
        $this->setComponent($id, $component);
    }

    public function unregister($id) {
        unset(
            $this->bindings[$id],
            $this->definitions[$id],
            $this->instances[$id],
            $this->values[$id],
            $this->parameters[$id]
        );
    }

    public function resolve($id, $parameters = [], $callback = false) {
        if ($this->has($id)) {
            $component = $this->getCompotent($id);

            if ($callback) {
                if ($this->hasBinding($id)) {
                    $callback = $this->callBinding($component, $parameters);
                    return $callback;
                }

                if ($this->hasInstance($id) || is_array($component)) {
                    throw new \Exception("Сontainer '${id}' can not be called");
                }
            }

            if ($this->hasDefinition($id)) {
                $instance = $this->make($id, $component, $parameters);
                return $instance;
            }

            if ($this->hasBinding($id) || $this->hasInstance($id) || is_array($component)) {
                return $component;
            }
        }

        return false;
    }

    public function resolveDefinition($id, $parameters = []) {
        return $this->resolve($id, $parameters);
    }

    public function resolveCallback($id, $parameters = []) {
        return $this->resolve($id, $parameters, true);
    }

    public function build($component, $parameters = null) {
        $reflector = new ReflectionClass($component);

        if ($reflector->isInstantiable()) {
            $constructor = $reflector->getConstructor();

            if (is_null($constructor)) {
                return $reflector->newInstanceWithoutConstructor();
            }

            if (is_null($parameters)) {
                $component = $reflector->newInstance($this);
            } else {
                $component = $reflector->newInstanceArgs($parameters);
            }
        }

        return $component;
    }

    public function make($id, $component, $parameters = []) {
        $this->instances[$id] = true;
        unset($this->definitions[$id]);

        $component = $this->build($component, $parameters);
        $this->setComponent($id, $component);

        return $component;
    }

    public function has(string $id): bool {
        return $this->hasComponent($id);
    }

    public function hasComponent($id) {
        return isset($this->values[$id]);
    }

    public function hasBinding($id) {
        return isset($this->bindings[$id]);
    }

    public function hasDefinition($id) {
        return isset($this->definitions[$id]);
    }

    public function hasInstance($id) {
        return isset($this->instances[$id]);
    }

    public function isBinding($component) {
        return $component instanceof Closure;
    }

    public function isDefinition($component) {
        return is_string($component) &&
                class_exists($component);
    }

    public function isInstance($component) {
        return !$this->isBinding($component) &&
                is_object($component);
    }

    public function getCompotent($id) {
        return $this->values[$id];
    }

    public function setComponent($id, $component) {
        if ($component) {
            $this->values[$id] = $component;
        }
    }

    public function getParameters($id) {
        return $this->parameters[$id] ?? null;
    }

    public function setParameters($id, $parameters = []) {
        if ($parameters) {
            $this->parameters[$id] = $parameters;
        }
    }

    public function prepareParameters($parameters) {
        if (!is_array($parameters)) { $parameters = [$parameters]; }
        return $parameters;
    }

    public function callBinding(Closure $callback, array $parameters = []) {
        return call_user_func_array($callback, $parameters);
    }

    public function listComponents($verbose = true) {
        $result = "\nid\t\tcomponent\n--\t\t---------\n";

        foreach ($this->values as $id => $component) {
            $containerType = 'unknown';
            if ($this->isBinding($component)) { $containerType = 'binding'; }
            if ($this->isDefinition($component)) { $containerType = 'definition';}
            if ($this->isInstance($component)) { $containerType = 'instance'; }
            if (is_array($component)) { $containerType = 'array'; }
            $result .= $id . "\t\t" . $containerType . "\n";
        }

        if ($verbose) {
            echo '<pre>' . $result . '</pre>';
        }

        return $result;
    }

    public function test($flag = false) {
        if ($flag) {
            return true;
        }

        return '<pre>' . print_r(func_get_args(), true) . '</pre>';
    }
}