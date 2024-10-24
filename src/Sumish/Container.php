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
<<<<<<< HEAD
<<<<<<< HEAD
    private array $bindings = [];
=======
    private $bindings = [];
>>>>>>> 721b66b (Multiple improvements)
=======
    private array $bindings = [];
>>>>>>> dbef408 (Updated type hinting for Application and Container)

    /**
     * Массив определений компонентов.
     *
     * @var array
     */
<<<<<<< HEAD
<<<<<<< HEAD
    private array $definitions = [];
=======
    private $definitions = [];
>>>>>>> 721b66b (Multiple improvements)
=======
    private array $definitions = [];
>>>>>>> dbef408 (Updated type hinting for Application and Container)

    /**
     * Массив экземпляров компонентов.
     *
     * @var array
     */
<<<<<<< HEAD
<<<<<<< HEAD
    private array $instances = [];
=======
    private $instances = [];
>>>>>>> 721b66b (Multiple improvements)
=======
    private array $instances = [];
>>>>>>> dbef408 (Updated type hinting for Application and Container)

    /**
     * Массив значений компонентов.
     *
     * @var array
     */
<<<<<<< HEAD
<<<<<<< HEAD
    private array $values = [];
=======
    private $values = [];
>>>>>>> 721b66b (Multiple improvements)
=======
    private array $values = [];
>>>>>>> dbef408 (Updated type hinting for Application and Container)

    /**
     * Массив параметров для компонентов.
     *
     * @var array
     */
<<<<<<< HEAD
<<<<<<< HEAD
    private array $parameters = [];
=======
    private $parameters = [];
>>>>>>> 721b66b (Multiple improvements)
=======
    private array $parameters = [];
>>>>>>> dbef408 (Updated type hinting for Application and Container)

    /**
     * Конструктор класса Container.
     *
<<<<<<< HEAD
<<<<<<< HEAD
     * Этот метод инициализирует контейнер зависимостей с 
     * переданными компонентами.
     *
     * @param array $components Ассоциативный массив компонентов для контейнера.
     * @return void
=======
     * Инициализирует контейнер с заданными компонентами.
     *
     * @param array $components Массив компонентов для инициализации.
>>>>>>> 721b66b (Multiple improvements)
=======
     * Этот метод инициализирует контейнер зависимостей с 
     * переданными компонентами.
     *
     * @param array $components Ассоциативный массив компонентов для контейнера.
     * @return void
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     */
    public function __construct(array $components = []) {
        $this->push($components);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Получает компонент по идентификатору.
=======
     * Магический метод для получения значения по идентификатору.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод позволяет получать значения из контейнера, 
     * используя синтаксис свойства.
     *
     * @param string $id Идентификатор компонента, который нужно получить.
     * @return mixed Возвращает компонент с указанным идентификатором, 
     *               или null, если компонент не найден.
     */
<<<<<<< HEAD
    public function __get($id) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function __get(string $id) {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return $this->get($id);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Устанавливает компонент по идентификатору.
=======
     * Магический метод для установки значения по идентификатору.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод позволяет устанавливать значения в контейнер, 
     * используя синтаксис свойства.
     *
     * @param string $id Идентификатор компонента, который нужно установить.
     * @param mixed $component Компонент, который нужно сохранить в контейнере.
     * @return void
     */
<<<<<<< HEAD
    public function __set($id, $component) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function __set(string $id, $component) {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        $this->set($id, $component);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Вызывает замыкание или метод по идентификатору.
=======
     * Магический метод для вызова недоступных методов.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
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
<<<<<<< HEAD
    public function __call($id, $parameters) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function __call(string $id, array $parameters) {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return $this->resolveCallback($id, $parameters);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Создаёт контейнер зависимостей.
=======
     * Создает экземпляр контейнера с переданными компонентами.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод использует шаблон одиночки (singleton) для 
     * создания единственного экземпляра контейнера и 
     * инициализирует его с заданными компонентами.
     *
     * @param array $components Ассоциативный массив компонентов для инициализации контейнера.
     * @return Container Возвращает экземпляр контейнера.
     */
<<<<<<< HEAD
    public static function create(array $components = []) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public static function create(array $components = []): Container {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        static $container = null;

        if (is_null($container)) {
            $container = new self($components);
        }

        return $container;
    }

    /**
     * Получает компонент по идентификатору.
     *
<<<<<<< HEAD
<<<<<<< HEAD
     * Этот метод извлекает параметры для указанного идентификатора 
     * и возвращает соответствующий компонент из контейнера.
     *
     * @param string $id Идентификатор компонента, который нужно получить.
     * @return mixed Возвращает компонент с указанным идентификатором 
     *               или false, если компонент не найден.
     */
    public function get(string $id) {
=======
     * Этот метод ищет и возвращает экземпляр компонента, зарегистрированного в контейнере, 
     * используя указанный идентификатор.
=======
     * Этот метод извлекает параметры для указанного идентификатора 
     * и возвращает соответствующий компонент из контейнера.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * @param string $id Идентификатор компонента, который нужно получить.
     * @return mixed Возвращает компонент с указанным идентификатором 
     *               или false, если компонент не найден.
     */
<<<<<<< HEAD
    public function get($id) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function get(string $id) {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        $parameters = $this->getParameters($id);
        return $this->resolveDefinition($id, $parameters);
    }

<<<<<<< HEAD
    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Устанавливает компонент в контейнере.
=======
     * Устанавливает компонент в контейнер.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод регистрирует компонент под заданным идентификатором, 
     * если такой идентификатор еще не существует в контейнере.
     *
     * @param string $id Идентификатор компонента, который нужно установить.
     * @param mixed $component Компонент, который нужно сохранить в контейнере.
     * @param array $parameters Параметры для компонента (по умолчанию пустой массив).
     * @return void
     */
<<<<<<< HEAD
    public function set($id, $component, $parameters = []) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function set(string $id, $component, array $parameters = []): void {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        if (!$this->has($id)) {
            $this->register($id, $component, $parameters);
        }
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Сбрасывает состояние контейнера.
=======
     * Сбрасывает контейнер к начальному состоянию.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод очищает все привязки, определения, экземпляры, 
     * значения и параметры в контейнере, возвращая его к состоянию 
     * после инициализации.
     *
     * @return $this Возвращает текущий экземпляр контейнера для 
     *               поддержки цепочки вызовов.
     */
<<<<<<< HEAD
    public function reset() {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function reset(): self {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
=======
    public function reset() {
>>>>>>> a83d9dfe28745a5fb780ce5b33666b2d7e57012f
        $this->bindings = [];
        $this->definitions = [];
        $this->instances = [];
        $this->values = [];
        $this->parameters = [];

<<<<<<< HEAD
<<<<<<< HEAD
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
     *                          которые нужно добавить в контейнер.
     * @return void
=======
        return $this;
=======
        return $this; // Возвращает текущий экземпляр
>>>>>>> dbef408 (Updated type hinting for Application and Container)
    }

    /**
     * Добавляет компоненты в контейнер.
     *
     * Этот метод принимает ассоциативный массив компонентов и 
     * регистрирует каждый из них в контейнере, используя их 
     * идентификаторы.
     *
     * @param array $components Ассоциативный массив компонентов, 
<<<<<<< HEAD
     *                          где ключи представляют идентификаторы, 
     *                          а значения — соответствующие компоненты.
>>>>>>> 721b66b (Multiple improvements)
=======
     *                          которые нужно добавить в контейнер.
     * @return void
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     */
    public function push(array $components = []) {
        foreach ($components as $id => $component) {
            $this->set($id, $component);
        }
    }

    /**
     * Регистрирует компонент в контейнере.
     *
<<<<<<< HEAD
<<<<<<< HEAD
     * Этот метод сохраняет компонент под указанным идентификатором 
     * и управляет его типом (привязка, определение или экземпляр).
     *
     * @param string $id Идентификатор компонента для регистрации.
     * @param mixed $component Компонент, который нужно зарегистрировать.
     * @param array $parameters Параметры для компонента (по умолчанию пустой массив).
     * @return void
     */
    public function register(string $id, $component, array $parameters = []) {
=======
     * Этот метод добавляет компонент с указанным идентификатором в контейнер 
     * и определяет его тип (привязка, определение или экземпляр). 
     * Если компонент является замыканием, он связывается с текущим контейнером.
     * Метод также подготавливает и сохраняет параметры для компонента.
=======
     * Этот метод сохраняет компонент под указанным идентификатором 
     * и управляет его типом (привязка, определение или экземпляр).
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * @param string $id Идентификатор компонента для регистрации.
     * @param mixed $component Компонент, который нужно зарегистрировать.
     * @param array $parameters Параметры для компонента (по умолчанию пустой массив).
     * @return void
     */
<<<<<<< HEAD
    public function register($id, $component, $parameters = []) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function register(string $id, $component, array $parameters = []) {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
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
<<<<<<< HEAD
<<<<<<< HEAD
     * Удаляет компонент из контейнера по идентификатору.
     *
     * Этот метод удаляет все связанные данные компонента, включая 
     * привязки, определения, экземпляры, значения и параметры.
     *
     * @param string $id Идентификатор компонента, который нужно удалить.
     * @return void
     */
    public function unregister(string $id) {
=======
     * Удаляет компонент из контейнера.
=======
     * Удаляет компонент из контейнера по идентификатору.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод удаляет все связанные данные компонента, включая 
     * привязки, определения, экземпляры, значения и параметры.
     *
     * @param string $id Идентификатор компонента, который нужно удалить.
     * @return void
     */
<<<<<<< HEAD
    public function unregister($id) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function unregister(string $id) {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        unset(
            $this->bindings[$id],
            $this->definitions[$id],
            $this->instances[$id],
            $this->values[$id],
            $this->parameters[$id]
        );
    }

<<<<<<< HEAD
    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Разрешает компонент по идентификатору.
=======
     * Разрешает компонент по идентификатору и параметрам.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
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
<<<<<<< HEAD
    public function resolve($id, $parameters = [], $callback = false) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function resolve(string $id, array $parameters = [], bool $callback = false) {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
=======
    public function resolve($id, $parameters = [], $callback = false) {
>>>>>>> a83d9dfe28745a5fb780ce5b33666b2d7e57012f
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

<<<<<<< HEAD
    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Разрешает определение компонента по его идентификатору.
=======
     * Разрешает определение компонента по идентификатору и параметрам.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
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
<<<<<<< HEAD
    public function resolveDefinition($id, $parameters = []) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function resolveDefinition(string $id, array $parameters = []) {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
=======
    public function resolveDefinition($id, $parameters = []) {
>>>>>>> a83d9dfe28745a5fb780ce5b33666b2d7e57012f
        return $this->resolve($id, $parameters);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Разрешает вызов по идентификатору с параметрами.
=======
     * Разрешает вызов метода по идентификатору и параметрам.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
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
<<<<<<< HEAD
    public function resolveCallback($id, $parameters = []) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function resolveCallback(string $id, array $parameters = []) {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return $this->resolve($id, $parameters, true);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Создаёт экземпляр компонента.
=======
     * Создает экземпляр компонента с указанными параметрами.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод использует рефлексию для создания нового экземпляра 
     * класса, передавая параметры, если они заданы.
     *
     * @param string $component Имя класса компонента, который нужно создать.
     * @param array|null $parameters Параметры для передачи в конструктор компонента (по умолчанию null).
     * @return object Возвращает новый экземпляр компонента.
     * @throws ReflectionException Если не удается создать экземпляр компонента.
     */
<<<<<<< HEAD
    public function build($component, $parameters = null) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function build(string $component, ?array $parameters = null): object {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
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

<<<<<<< HEAD
    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Создаёт и регистрирует экземпляр компонента.
=======
     * Создает и регистрирует экземпляр компонента по идентификатору.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
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
<<<<<<< HEAD
    public function make($id, $component, $parameters = []) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function make(string $id, $component, array $parameters = []): object {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
=======
    public function make($id, $component, $parameters = []) {
>>>>>>> a83d9dfe28745a5fb780ce5b33666b2d7e57012f
        $this->instances[$id] = true;
        unset($this->definitions[$id]);

        $component = $this->build($component, $parameters);
        $this->setComponent($id, $component);

        return $component;
    }

    /**
     * Проверяет, существует ли компонент с данным идентификатором.
     *
<<<<<<< HEAD
<<<<<<< HEAD
     * Этот метод является оберткой для метода hasComponent.
=======
     * Этот метод определяет, зарегистрирован ли компонент в контейнере 
     * по указанному идентификатору. Он предоставляет простой интерфейс 
     * для проверки наличия компонента без получения его значения.
>>>>>>> 721b66b (Multiple improvements)
=======
     * Этот метод является оберткой для метода hasComponent.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * @param string $id Идентификатор компонента, который нужно проверить.
     * @return bool Возвращает true, если компонент существует, иначе false.
     */
    public function has(string $id): bool {
        return $this->hasComponent($id);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
     * Проверяет, существует ли компонент с заданным идентификатором.
     *
     * Этот метод возвращает true, если компонент зарегистрирован 
     * в контейнере, и false в противном случае.
     *
     * @param string $id Идентификатор компонента для проверки.
     * @return bool Возвращает true, если компонент существует, иначе false.
     */
    public function hasComponent(string $id): bool {
=======
     * Проверяет, существует ли компонент в контейнере.
=======
     * Проверяет, существует ли компонент с заданным идентификатором.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод возвращает true, если компонент зарегистрирован 
     * в контейнере, и false в противном случае.
     *
     * @param string $id Идентификатор компонента для проверки.
     * @return bool Возвращает true, если компонент существует, иначе false.
     */
<<<<<<< HEAD
    public function hasComponent($id) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function hasComponent(string $id): bool {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return isset($this->values[$id]);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
     * Проверяет, существует ли привязка с данным идентификатором.
     *
     * Этот метод определяет, зарегистрирована ли привязка 
     * в контейнере по указанному идентификатору.
     *
     * @param string $id Идентификатор привязки, который нужно проверить.
     * @return bool Возвращает true, если привязка существует, иначе false.
     */
    public function hasBinding(string $id): bool {
=======
     * Проверяет, существует ли привязка для данного идентификатора.
=======
     * Проверяет, существует ли привязка с данным идентификатором.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод определяет, зарегистрирована ли привязка 
     * в контейнере по указанному идентификатору.
     *
     * @param string $id Идентификатор привязки, который нужно проверить.
     * @return bool Возвращает true, если привязка существует, иначе false.
     */
<<<<<<< HEAD
    public function hasBinding($id) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function hasBinding(string $id): bool {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return isset($this->bindings[$id]);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
     * Проверяет, существует ли определение с данным идентификатором.
     *
     * Этот метод определяет, зарегистрировано ли определение 
     * в контейнере по указанному идентификатору.
     *
     * @param string $id Идентификатор определения, которое нужно проверить.
     * @return bool Возвращает true, если определение существует, иначе false.
     */
    public function hasDefinition(string $id): bool {
=======
     * Проверяет, существует ли определение для данного идентификатора.
=======
     * Проверяет, существует ли определение с данным идентификатором.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод определяет, зарегистрировано ли определение 
     * в контейнере по указанному идентификатору.
     *
     * @param string $id Идентификатор определения, которое нужно проверить.
     * @return bool Возвращает true, если определение существует, иначе false.
     */
<<<<<<< HEAD
    public function hasDefinition($id) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function hasDefinition(string $id): bool {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return isset($this->definitions[$id]);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
     * Проверяет, существует ли экземпляр с данным идентификатором.
     *
     * Этот метод определяет, зарегистрирован ли экземпляр 
     * в контейнере по указанному идентификатору.
     *
     * @param string $id Идентификатор экземпляра, который нужно проверить.
     * @return bool Возвращает true, если экземпляр существует, иначе false.
     */
    public function hasInstance(string $id): bool {
=======
     * Проверяет, существует ли экземпляр для данного идентификатора.
=======
     * Проверяет, существует ли экземпляр с данным идентификатором.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод определяет, зарегистрирован ли экземпляр 
     * в контейнере по указанному идентификатору.
     *
     * @param string $id Идентификатор экземпляра, который нужно проверить.
     * @return bool Возвращает true, если экземпляр существует, иначе false.
     */
<<<<<<< HEAD
    public function hasInstance($id) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function hasInstance(string $id): bool {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return isset($this->instances[$id]);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
     * Проверяет, является ли компонент привязкой.
     *
     * Этот метод определяет, является ли переданный компонент 
     * экземпляром Closure (замыкания), что указывает на то, 
     * что он может быть использован как привязка в контейнере.
=======
     * Проверяет, является ли компонент привязкой (Closure).
     *
     * Этот метод определяет, представляет ли данный компонент замыкание 
     * (Closure), что указывает на то, что он зарегистрирован как привязка 
     * в контейнере. Это полезно для различения между обычными компонентами 
     * и замыканиями, которые могут быть вызваны позже.
>>>>>>> 721b66b (Multiple improvements)
=======
     * Проверяет, является ли компонент привязкой.
     *
     * Этот метод определяет, является ли переданный компонент 
     * экземпляром Closure (замыкания), что указывает на то, 
     * что он может быть использован как привязка в контейнере.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * @param mixed $component Компонент для проверки.
     * @return bool Возвращает true, если компонент является привязкой, иначе false.
     */
<<<<<<< HEAD
<<<<<<< HEAD
    public function isBinding($component): bool {
=======
    public function isBinding($component) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function isBinding($component): bool {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return $component instanceof Closure;
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
     * Проверяет, является ли компонент определением.
     *
     * Этот метод определяет, является ли переданный компонент 
     * строкой, представляющей имя класса, который существует.
     *
     * @param mixed $component Компонент для проверки.
     * @return bool Возвращает true, если компонент является определением, иначе false.
     */
    public function isDefinition($component): bool {
=======
     * Проверяет, является ли компонент определением класса.
=======
     * Проверяет, является ли компонент определением.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод определяет, является ли переданный компонент 
     * строкой, представляющей имя класса, который существует.
     *
     * @param mixed $component Компонент для проверки.
     * @return bool Возвращает true, если компонент является определением, иначе false.
     */
<<<<<<< HEAD
    public function isDefinition($component) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function isDefinition($component): bool {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return is_string($component) && class_exists($component);
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
     * Проверяет, является ли компонент экземпляром.
     *
     * Этот метод определяет, является ли переданный компонент 
     * объектом и не является ли он привязкой.
     *
     * @param mixed $component Компонент для проверки.
     * @return bool Возвращает true, если компонент является экземпляром, иначе false.
     */
    public function isInstance($component): bool {
=======
     * Проверяет, является ли компонент экземпляром объекта.
=======
     * Проверяет, является ли компонент экземпляром.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод определяет, является ли переданный компонент 
     * объектом и не является ли он привязкой.
     *
     * @param mixed $component Компонент для проверки.
     * @return bool Возвращает true, если компонент является экземпляром, иначе false.
     */
<<<<<<< HEAD
    public function isInstance($component) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function isInstance($component): bool {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return !$this->isBinding($component) && is_object($component);
    }

<<<<<<< HEAD
    /**
     * Получает компонент по идентификатору.
     *
<<<<<<< HEAD
<<<<<<< HEAD
     * Этот метод возвращает компонент, зарегистрированный в контейнере 
     * по указанному идентификатору.
     *
     * @param string $id Идентификатор компонента, который нужно получить.
     * @return mixed Возвращает компонент с указанным идентификатором 
     *               или null, если компонент не найден.
     */
    public function getComponent(string $id) {
        return $this->values[$id] ?? null; // null-объединение для обработки отсутствия значения
=======
    public function getCompotent($id) {
        return $this->values[$id];
>>>>>>> a83d9dfe28745a5fb780ce5b33666b2d7e57012f
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
=======
     * Этот метод извлекает и возвращает значение компонента, 
     * зарегистрированного в контейнере, по указанному идентификатору.
     * Он полезен для получения экземпляров компонентов, 
     * которые уже были зарегистрированы в контейнере.
=======
     * Этот метод возвращает компонент, зарегистрированный в контейнере 
     * по указанному идентификатору.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * @param string $id Идентификатор компонента, который нужно получить.
     * @return mixed Возвращает компонент с указанным идентификатором 
     *               или null, если компонент не найден.
     */
    public function getCompotent(string $id) {
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
<<<<<<< HEAD
    public function setComponent($id, $component) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function setComponent(string $id, $component) {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        if ($component) {
            $this->values[$id] = $component;
        }
    }

<<<<<<< HEAD
    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Получает параметры для компонента по его идентификатору.
=======
     * Получает параметры компонента по идентификатору.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод возвращает массив параметров, зарегистрированных 
     * для указанного идентификатора, или null, если параметры не найдены.
     *
     * @param string $id Идентификатор компонента, для которого нужно получить параметры.
     * @return array|null Возвращает массив параметров или null, 
     *                    если параметры не найдены.
     */
<<<<<<< HEAD
    public function getParameters($id) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function getParameters(string $id): ?array {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        return $this->parameters[$id] ?? null;
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Устанавливает параметры для компонента.
=======
     * Устанавливает параметры для компонента по заданному идентификатору.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод сохраняет переданные параметры в массиве параметров 
     * контейнера, если параметры не пустые.
     *
     * @param string $id Идентификатор компонента, для которого нужно установить параметры.
     * @param array $parameters Ассоциативный массив параметров для компонента (по умолчанию пустой массив).
     * @return void
     */
<<<<<<< HEAD
    public function setParameters($id, $parameters = []) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function setParameters(string $id, array $parameters = []): void {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
=======
    public function getParameters($id) {
        return $this->parameters[$id] ?? null;
    }

    public function setParameters($id, $parameters = []) {
>>>>>>> a83d9dfe28745a5fb780ce5b33666b2d7e57012f
        if ($parameters) {
            $this->parameters[$id] = $parameters;
        }
    }

    /**
<<<<<<< HEAD
<<<<<<< HEAD
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
=======
     * Подготавливает параметры для использования.
=======
     * Подготавливает параметры для использования в контейнере.
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     *
     * Этот метод принимает параметры и преобразует их в массив, 
     * если они не являются массивом. Это обеспечивает согласованность 
     * при передаче параметров в другие методы.
     *
     * @param mixed $parameters Параметры, которые нужно подготовить.
     * @return array Возвращает массив подготовленных параметров.
     */
<<<<<<< HEAD
    public function prepareParameters($parameters) {
>>>>>>> 721b66b (Multiple improvements)
=======
    public function prepareParameters($parameters): array {
>>>>>>> dbef408 (Updated type hinting for Application and Container)
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }
        return $parameters;
    }

    /**
     * Вызывает замыкание с переданными параметрами.
     *
<<<<<<< HEAD
<<<<<<< HEAD
     * Этот метод принимает замыкание и массив параметров, 
     * затем вызывает замыкание с использованием 
     * call_user_func_array для передачи параметров.
     *
     * @param Closure $callback Замыкание, которое нужно вызвать.
     * @param array $parameters Параметры, которые нужно передать в замыкание (по умолчанию пустой массив).
=======
     * Этот метод принимает замыкание (Closure) и массив параметров, 
     * затем вызывает замыкание с использованием функции 
     * `call_user_func_array`. Это позволяет динамически передавать 
     * параметры в замыкание, обеспечивая гибкость при его вызове.
     *
     * @param Closure $callback Замыкание, которое нужно вызвать.
     * @param array $parameters Параметры для передачи в замыкание (по умолчанию пустой массив).
>>>>>>> 721b66b (Multiple improvements)
=======
     * Этот метод принимает замыкание и массив параметров, 
     * затем вызывает замыкание с использованием 
     * call_user_func_array для передачи параметров.
     *
     * @param Closure $callback Замыкание, которое нужно вызвать.
     * @param array $parameters Параметры, которые нужно передать в замыкание (по умолчанию пустой массив).
>>>>>>> dbef408 (Updated type hinting for Application and Container)
     * @return mixed Возвращает результат выполнения замыкания.
     */
    public function callBinding(Closure $callback, array $parameters = []) {
        return call_user_func_array($callback, $parameters);
    }
<<<<<<< HEAD
<<<<<<< HEAD
=======

    /**
     * Перечисляет все компоненты в контейнере.
     *
     * Этот метод выводит список всех зарегистрированных компонентов, 
     * их идентификаторов и типов. Может выводить в подробном режиме 
     * в зависимости от переданного параметра.
     *
     * @param bool $verbose Указывает, нужно ли выводить подробную информацию (по умолчанию true).
     * @return string Возвращает строку с информацией о компонентах.
     */
    public function listComponents(bool $verbose = true): string {
=======

    public function listComponents($verbose = true) {
>>>>>>> a83d9dfe28745a5fb780ce5b33666b2d7e57012f
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

<<<<<<< HEAD
    /**
     * Тестовый метод для проверки аргументов.
     *
     * Этот метод возвращает true, если передан флаг, 
     * в противном случае он возвращает строку с выводом 
     * аргументов, переданных в метод.
     *
     * @param bool $flag Указывает, нужно ли возвращать true (по умолчанию false).
     * @return mixed Возвращает true или строку с выводом аргументов.
     */
    public function test(bool $flag = false) {
=======
    public function test($flag = false) {
>>>>>>> a83d9dfe28745a5fb780ce5b33666b2d7e57012f
        if ($flag) {
            return true;
        }

        return '<pre>' . print_r(func_get_args(), true) . '</pre>';
    }
<<<<<<< HEAD
<<<<<<< HEAD

>>>>>>> 721b66b (Multiple improvements)
=======
>>>>>>> dbef408 (Updated type hinting for Application and Container)
}
=======
}
>>>>>>> a83d9dfe28745a5fb780ce5b33666b2d7e57012f
