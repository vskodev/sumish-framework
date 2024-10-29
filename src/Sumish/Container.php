<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.xyz/LICENSE (MIT License)
 */

namespace Sumish;

use Closure;
use ReflectionClass;
use Psr\Container\ContainerInterface;

/**
 * Контейнер зависимостей для управления компонентами приложения.
 *
 * Этот класс реализует паттерн "Контейнер зависимостей", который 
 * позволяет регистрировать, разрешать и управлять компонентами 
 * приложения. Контейнер поддерживает различные типы компонентов, 
 * такие как синглтоны, привязки и определения, а также позволяет 
 * динамически создавать экземпляры классов с учетом зависимостей.
 *
 * @package Sumish
 */
class Container implements ContainerInterface {
    /**
     * Массив привязок компонентов.
     *
     * @var array
     */
    private array $bindings = [];

    /**
     * Массив определений компонентов.
     *
     * @var array
     */
    private array $definitions = [];

    /**
     * Массив экземпляров компонентов.
     *
     * @var array
     */
    private array $instances = [];

    /**
     * Массив значений компонентов.
     *
     * @var array
     */
    private array $values = [];

    /**
     * Массив параметров для компонентов.
     *
     * @var array
     */
    private array $parameters = [];

    /**
     * Конструктор класса Container.
     *
     * Этот метод инициализирует контейнер зависимостей с 
     * переданными компонентами.
     *
     * @param array $components Ассоциативный массив компонентов для контейнера.
     * @return void
     */
    public function __construct(array $components = []) {
        $this->push($components);
    }

    /**
     * Магический метод для получения значения по идентификатору.
     *
     * Этот метод позволяет получать значения из контейнера, 
     * используя синтаксис свойства.
     *
     * @param string $id Идентификатор компонента, который нужно получить.
     * @return mixed Возвращает компонент с указанным идентификатором, 
     *               или null, если компонент не найден.
     */
    public function __get(string $id) {
        return $this->get($id);
    }

    /**
     * Магический метод для установки значения по идентификатору.
     *
     * Этот метод позволяет устанавливать значения в контейнер, 
     * используя синтаксис свойства.
     *
     * @param string $id Идентификатор компонента, который нужно установить.
     * @param mixed $component Компонент, который нужно сохранить в контейнере.
     * @return void
     */
    public function __set(string $id, $component) {
        $this->set($id, $component);
    }

    /**
     * Магический метод для вызова недоступных методов.
     *
     * Этот метод позволяет вызывать методы контейнера, 
     * используя синтаксис метода. Если метод не существует, 
     * будет вызван этот метод, который делегирует вызов 
     * другому методу.
     *
     * @param string $id Идентификатор метода, который нужно вызвать.
     * @param array $parameters Массив параметров, передаваемых в метод.
     * @return mixed Возвращает результат вызова метода, 
     *               или false, если метод не найден.
     */
    public function __call(string $id, array $parameters) {
        return $this->resolveCallback($id, $parameters);
    }

    /**
     * Создает экземпляр контейнера с переданными компонентами.
     *
     * Этот метод использует шаблон одиночки (singleton) для 
     * создания единственного экземпляра контейнера и 
     * инициализирует его с заданными компонентами.
     *
     * @param array $components Ассоциативный массив компонентов для инициализации контейнера.
     * @return Container Возвращает экземпляр контейнера.
     */
    public static function create(array $components = []): Container {
        static $container = null;

        if (is_null($container)) {
            $container = new self($components);
        }

        return $container;
    }

    /**
     * Получает компонент по идентификатору.
     *
     * Этот метод извлекает параметры для указанного идентификатора 
     * и возвращает соответствующий компонент из контейнера.
     *
     * @param string $id Идентификатор компонента, который нужно получить.
     * @return mixed Возвращает компонент с указанным идентификатором 
     *               или false, если компонент не найден.
     */
    public function get(string $id) {
        $parameters = $this->getParameters($id);
        return $this->resolveDefinition($id, $parameters);
    }

    /**
     * Устанавливает компонент в контейнер.
     *
     * Этот метод регистрирует компонент под заданным идентификатором, 
     * если такой идентификатор еще не существует в контейнере.
     *
     * @param string $id Идентификатор компонента, который нужно установить.
     * @param mixed $component Компонент, который нужно сохранить в контейнере.
     * @param array $parameters Параметры для компонента (по умолчанию пустой массив).
     * @return void
     */
    public function set(string $id, $component, array $parameters = []) {
        if (!$this->has($id)) {
            $this->register($id, $component, $parameters);
        }
    }

    /**
     * Сбрасывает контейнер к начальному состоянию.
     *
     * Этот метод очищает все привязки, определения, экземпляры, 
     * значения и параметры в контейнере, возвращая его к состоянию 
     * после инициализации.
     *
     * @return $this Возвращает текущий экземпляр контейнера для 
     *               поддержки цепочки вызовов.
     */
    public function reset(): self {
        $this->bindings = [];
        $this->definitions = [];
        $this->instances = [];
        $this->values = [];
        $this->parameters = [];

        return $this; // Возвращает текущий экземпляр
    }

    /**
     * Добавляет компоненты в контейнер.
     *
     * Этот метод принимает ассоциативный массив компонентов и 
     * регистрирует каждый из них в контейнере, используя их 
     * идентификаторы.
     *
     * @param array $components Ассоциативный массив компонентов, 
     *                          где ключи представляют идентификаторы, 
     *                          а значения — соответствующие компоненты.
     *                          которые нужно добавить в контейнер.
     * @return void
     */
    public function push(array $components = []) {
        foreach ($components as $id => $component) {
            $this->set($id, $component);
        }
    }

    /**
     * Регистрирует компонент в контейнере.
     *
     * Этот метод сохраняет компонент под указанным идентификатором 
     * и управляет его типом (привязка, определение или экземпляр).
     *
     * @param string $id Идентификатор компонента для регистрации.
     * @param mixed $component Компонент, который нужно зарегистрировать.
     * @param array $parameters Параметры для компонента (по умолчанию пустой массив).
     * @return void
     */
    public function register(string $id, $component, array $parameters = []) {
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

    /**
     * Удаляет компонент из контейнера по идентификатору.
     *
     * Этот метод удаляет все связанные данные компонента, включая 
     * привязки, определения, экземпляры, значения и параметры.
     *
     * @param string $id Идентификатор компонента, который нужно удалить.
     * @return void
     */
    public function unregister(string $id) {
        unset(
            $this->bindings[$id],
            $this->definitions[$id],
            $this->instances[$id],
            $this->values[$id],
            $this->parameters[$id]
        );
    }

    /**
     * Разрешает компонент по идентификатору и параметрам.
     *
     * Этот метод возвращает компонент, соответствующий указанному 
     * идентификатору. В зависимости от типа компонента 
     * (привязка, определение, экземпляр) он может создавать новый 
     * экземпляр или возвращать существующий.
     *
     * @param string $id Идентификатор компонента, который нужно разрешить.
     * @param array $parameters Параметры для компонента (по умолчанию пустой массив).
     * @param bool $callback Указывает, должен ли метод вызывать привязку (по умолчанию false).
     * @return mixed Возвращает разрешенный компонент или false, 
     *               если компонент не найден.
     */
    public function resolve(string $id, $parameters = [], bool $callback = false) {
        if ($this->has($id)) {
            $component = $this->getComponent($id);

            if ($callback) {
                if ($this->hasBinding($id)) {
                    $callback = $this->callBinding($component, $parameters);
                    return $callback;
                }

                if ($this->hasInstance($id) || is_array($component)) {
                    throw new \Exception("Сontainer '{$id}' can not be called");
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

    /**
     * Разрешает определение компонента по идентификатору и параметрам.
     *
     * Этот метод вызывает метод разрешения для получения 
     * экземпляра компонента, который соответствует заданному 
     * идентификатору.
     *
     * @param string $id Идентификатор компонента, который нужно разрешить.
     * @param array $parameters Параметры для компонента (по умолчанию пустой массив).
     * @return mixed Возвращает разрешенный компонент или false, 
     *               если компонент не найден.
     */
    public function resolveDefinition(string $id, $parameters = []) {
        return $this->resolve($id, $parameters);
    }

    /**
     * Разрешает вызов метода по идентификатору и параметрам.
     *
     * Этот метод вызывает метод разрешения для получения 
     * экземпляра компонента, который соответствует заданному 
     * идентификатору, с указанием, что это вызов колбека.
     *
     * @param string $id Идентификатор компонента или метода, который нужно разрешить.
     * @param array $parameters Параметры для компонента (по умолчанию пустой массив).
     * @return mixed Возвращает результат разрешения колбека или false, 
     *               если компонент не найден.
     */
    public function resolveCallback(string $id, array $parameters = []) {
        return $this->resolve($id, $parameters, true);
    }

    /**
     * Создает экземпляр компонента с указанными параметрами.
     *
     * Этот метод использует рефлексию для создания нового экземпляра 
     * класса, передавая параметры, если они заданы.
     *
     * @param string $component Имя класса компонента, который нужно создать.
     * @param array|null $parameters Параметры для передачи в конструктор компонента (по умолчанию null).
     * @return object Возвращает новый экземпляр компонента.
     * @throws ReflectionException Если не удается создать экземпляр компонента.
     */
    public function build(string $component, ?array $parameters = null): object {
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

    /**
     * Создает и регистрирует экземпляр компонента по идентификатору.
     *
     * Этот метод создает новый экземпляр компонента и 
     * регистрирует его в контейнере, если он еще не зарегистрирован.
     *
     * @param string $id Идентификатор компонента, который нужно создать.
     * @param mixed $component Компонент, который нужно создать.
     * @param array $parameters Параметры для передачи в конструктор компонента (по умолчанию пустой массив).
     * @return object Возвращает новый экземпляр компонента.
     * @throws Exception Если не удается создать экземпляр компонента.
     */
    public function make(string $id, $component, $parameters = []): object {
        $this->instances[$id] = true;
        unset($this->definitions[$id]);

        $component = $this->build($component, $parameters);
        $this->setComponent($id, $component);

        return $component;
    }

    /**
     * Проверяет, существует ли компонент с данным идентификатором.
     *
     * Этот метод является оберткой для метода hasComponent.
     * Этот метод определяет, зарегистрирован ли компонент в контейнере 
     * по указанному идентификатору. Он предоставляет простой интерфейс 
     * для проверки наличия компонента без получения его значения.
     * Этот метод является оберткой для метода hasComponent.
     *
     * @param string $id Идентификатор компонента, который нужно проверить.
     * @return bool Возвращает true, если компонент существует, иначе false.
     */
    public function has(string $id): bool {
        return $this->hasComponent($id);
    }

    /**
     * Проверяет, существует ли компонент с заданным идентификатором.
     *
     * Этот метод возвращает true, если компонент зарегистрирован 
     * в контейнере, и false в противном случае.
     *
     * @param string $id Идентификатор компонента для проверки.
     * @return bool Возвращает true, если компонент существует, иначе false.
     */
    public function hasComponent(string $id): bool {
        return isset($this->values[$id]);
    }

    /**
     * Проверяет, существует ли привязка с данным идентификатором.
     *
     * Этот метод определяет, зарегистрирована ли привязка 
     * в контейнере по указанному идентификатору.
     *
     * @param string $id Идентификатор привязки, который нужно проверить.
     * @return bool Возвращает true, если привязка существует, иначе false.
     */
    public function hasBinding(string $id): bool {
        return isset($this->bindings[$id]);
    }

    /**
     * Проверяет, существует ли определение с данным идентификатором.
     *
     * Этот метод определяет, зарегистрировано ли определение 
     * в контейнере по указанному идентификатору.
     *
     * @param string $id Идентификатор определения, которое нужно проверить.
     * @return bool Возвращает true, если определение существует, иначе false.
     */
    public function hasDefinition(string $id): bool {
        return isset($this->definitions[$id]);
    }

    /**
     * Проверяет, существует ли экземпляр с данным идентификатором.
     *
     * Этот метод определяет, зарегистрирован ли экземпляр 
     * в контейнере по указанному идентификатору.
     *
     * @param string $id Идентификатор экземпляра, который нужно проверить.
     * @return bool Возвращает true, если экземпляр существует, иначе false.
     */
    public function hasInstance(string $id): bool {
        return isset($this->instances[$id]);
    }

    /**
     * Проверяет, является ли компонент привязкой.
     *
     * Этот метод определяет, является ли переданный компонент 
     * экземпляром Closure (замыкания), что указывает на то, 
     * что он может быть использован как привязка в контейнере.
     * Проверяет, является ли компонент привязкой (Closure).
     *
     * @param mixed $component Компонент для проверки.
     * @return bool Возвращает true, если компонент является привязкой, иначе false.
     */
    public function isBinding($component): bool {
        return $component instanceof Closure;
    }

    /**
     * Проверяет, является ли компонент определением.
     *
     * Этот метод определяет, является ли переданный компонент 
     * строкой, представляющей имя класса, который существует.
     *
     * @param mixed $component Компонент для проверки.
     * @return bool Возвращает true, если компонент является определением, иначе false.
     */
    public function isDefinition($component): bool {
        return is_string($component) && class_exists($component);
    }

    /**
     * Проверяет, является ли компонент экземпляром.
     *
     * Этот метод определяет, является ли переданный компонент 
     * объектом и не является ли он привязкой.
     *
     * @param mixed $component Компонент для проверки.
     * @return bool Возвращает true, если компонент является экземпляром, иначе false.
     */
    public function isInstance($component): bool {
        return !$this->isBinding($component) && is_object($component);
    }

    /**
     * Получает компонент по идентификатору.
     *
     * Этот метод возвращает компонент, зарегистрированный в контейнере 
     * по указанному идентификатору.
     *
     * @param string $id Идентификатор компонента, который нужно получить.
     * @return mixed Возвращает компонент с указанным идентификатором 
     *               или null, если компонент не найден.
     */
    public function getComponent(string $id) {
        return $this->values[$id] ?? null; // null-объединение для обработки отсутствия значения
    }

    /**
     * Устанавливает компонент в контейнер по заданному идентификатору.
     *
     * Этот метод сохраняет переданный компонент под указанным идентификатором 
     * в массиве значений контейнера, если компонент не является пустым.
     *
     * @param string $id Идентификатор компонента, который нужно установить.
     * @param mixed $component Компонент, который нужно сохранить в контейнере.
     * @return void
     */
    public function setComponent(string $id, $component) {
        if ($component) {
            $this->values[$id] = $component;
        }
    }

    /**
     * Перечисляет все компоненты в контейнере.
     *
     * Этот метод проходит по всем зарегистрированным компонентам в контейнере
     * и создает ассоциативный массив, где ключами являются идентификаторы компонентов,
     * а значениями — их типы (привязка, определение, экземпляр или массив).
     *
     * @return array Возвращает ассоциативный массив с идентификаторами компонентов и их типами.
     */
    public function listComponents(): array {
        $components = [];

        foreach ($this->values as $id => $component) {
            $type = 'unknown';
            if ($this->isBinding($component)) { $type = 'binding'; }
            if ($this->isDefinition($component)) { $type = 'definition'; }
            if ($this->isInstance($component)) { $type = 'instance'; }
            if (is_array($component)) { $type = 'array'; }

            $components[$id] = $type;
        }

        return $components;
    }

    /**
     * Получает параметры компонента по идентификатору.
     *
     * Этот метод возвращает массив параметров, зарегистрированных 
     * для указанного идентификатора, или null, если параметры не найдены.
     *
     * @param string $id Идентификатор компонента, для которого нужно получить параметры.
     * @return array|null Возвращает массив параметров или null, 
     *                    если параметры не найдены.
     */
    public function getParameters(string $id): ?array {
        return $this->parameters[$id] ?? null;
    }

    /**
     * Устанавливает параметры для компонента по заданному идентификатору.
     *
     * Этот метод сохраняет переданные параметры в массиве параметров 
     * контейнера, если параметры не пустые.
     *
     * @param string $id Идентификатор компонента, для которого нужно установить параметры.
     * @param array $parameters Ассоциативный массив параметров для компонента (по умолчанию пустой массив).
     * @return void
     */
    public function setParameters(string $id, array $parameters = []) {
        if ($parameters) {
            $this->parameters[$id] = $parameters;
        }
    }

    /**
     * Подготавливает параметры для использования в контейнере.
     *
     * Этот метод принимает параметры и преобразует их в массив, 
     * если они не являются массивом. Это обеспечивает согласованность 
     * при передаче параметров в другие методы.
     *
     * @param mixed $parameters Параметры, которые нужно подготовить.
     * @return array Возвращает массив подготовленных параметров.
     */
    public function prepareParameters($parameters): array {
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }
        return $parameters;
    }

    /**
     * Вызывает замыкание с переданными параметрами.
     *
     * Этот метод принимает замыкание и массив параметров, 
     * затем вызывает замыкание с использованием 
     * call_user_func_array для передачи параметров.
     *
     * @param Closure $callback Замыкание, которое нужно вызвать.
     * @param array $parameters Параметры, которые нужно передать в замыкание (по умолчанию пустой массив).
     */
    public function callBinding(Closure $callback, array $parameters = []) {
        return call_user_func_array($callback, $parameters);
    }
}
