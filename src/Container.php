<?php

/**
 * Sumish Framework (https://sumish.xyz)
 *
 * @license https://sumish.mit-license.org (MIT License)
 */

declare(strict_types=1);

namespace Sumish;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

/**
 * Контейнер для управления зависимостями.
 * 
 * Этот класс реализует интерфейс PSR-11 ContainerInterface и предоставляет функционал
 * для регистрации, получения и управления зависимостями. Также включает поддержку
 * ленивой инициализации, кеширования и замены компонентов.
 */
class Container implements ContainerInterface {
    /**
     * Привязки компонентов.
     * @var array<string, array{component: callable|object|string, parameters: array<string, mixed>}>
     */
    private array $bindings = [];
    
    /**
     * Созданные экземпляры.
     * @var array<string, object|callable>
     */
    private array $instances = [];

    /**
     * Создаёт экземпляр контейнера с заданными компонентами.
     *
     * @param array<string, mixed> $config Массив конфигурации с компонентами.
     * @return self
     */
    public static function create(array $config): self
    {
        static $container = null;
        if (!$container instanceof self) {
            $container = new self();
            $container->set('config', new \ArrayObject($config));
            if (isset($config['components'])) {
                $container->push($config['components']);
            }
        }
        return $container;
    }

    /**
     * Добавляет несколько компонентов в контейнер.
     *
     * @param array<string, callable|object|string> $components
     * @return void
     */
    public function push(array $components): void
    {
        foreach ($components as $id => $component) {
            $this->register($id, $component);
        }
    }

    /**
     * Регистрирует компонент в контейнере, если он ещё не существует.
     *
     * @param string $id Идентификатор компонента.
     * @param callable|object|string $component Определение или экземпляр компонента.
     * @param array<string, mixed> $parameters Дополнительные параметры для компонента.
     * @return void
     */
    public function register(string $id, callable|object|string $component, array $parameters = []): void
    {
        if (!isset($this->bindings[$id])) {
            $this->set($id, $component, $parameters);
        }
    }

    /**
     * Устанавливает или заменяет компонент в контейнере.
     *
     * @param string $id Идентификатор компонента.
     * @param callable|object|string $component
     * @param array<string, mixed> $parameters Дополнительные параметры для компонента.
     * @return void
     */
    public function set(string $id, callable|object|string $component, array $parameters = []): void
    {
        $this->bindings[$id] = compact('component', 'parameters');
        unset($this->instances[$id]);
    }

    /**
     * Возвращает компонент по идентификатору.
     *
     * @param string $id Идентификатор компонента.
     * @return mixed
     * @throws NotFoundExceptionInterface Если компонент не найден.
     * @throws ContainerExceptionInterface Если компонент не удалось создать.
     */
    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new class("Component '{$id}' not found.") 
                extends \InvalidArgumentException implements NotFoundExceptionInterface {};
        }

        return $this->instances[$id] ??= $this->resolve($id);
    }

    /**
     * Разрешает компонент по его определению.
     *
     * @param string $id
     * @return mixed
     * @throws ContainerExceptionInterface
     */
    private function resolve(string $id): mixed
    {
        $definition = $this->bindings[$id];
        $component = $definition['component'];
        $parameters = $definition['parameters'];

        try {
            return match(true) {
                is_callable($component) => $component(...$parameters),
                is_string($component) => $this->build($component, $parameters),
                default => $component
            };
        } catch (\Throwable $e) {
            throw new class("Error creating component '{$id}': {$e->getMessage()}", 0, $e)
                extends \RuntimeException implements ContainerExceptionInterface {};
        }
    }

    /**
     * Проверяет наличие компонента в контейнере.
     *
     * @param string $id Идентификатор компонента.
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    /**
     * Возвращает список всех зарегистрированных компонентов.
     *
     * @return array<string>
     */
    public function list(): array
    {
        return array_keys($this->bindings);
    }

    /**
     * Удаляет компонент из контейнера.
     *
     * @param string $id Идентификатор компонента.
     * @return void
     */
    public function remove(string $id): void
    {
        unset($this->bindings[$id], $this->instances[$id]);
    }

    /**
     * Очищает все зарегистрированные компоненты и экземпляры.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }

/**
     * Кеширует результат вызова замыкания с учётом параметров.
     *
     * @param string $key Ключ кеша.
     * @param callable $callback Функция, результат которой будет кешироваться.
     * @param array<string, mixed> $parameters Параметры для передачи в callback.
     * @return mixed
     */
    public function cache(string $key, callable $callback, array $parameters = []): mixed
    {
        // Создаём уникальный ключ на основе базового ключа и параметров
        $cacheKey = empty($parameters) ? $key : $key . '_' . md5(serialize($parameters));
        
        if (!isset($this->instances[$cacheKey])) {
            $this->instances[$cacheKey] = $callback(...$parameters);
        }
        
        return $this->instances[$cacheKey];
    }

    /**
     * Создаёт экземпляр класса с учётом зависимостей.
     *
     * @param string $class Имя класса.
     * @param array<string, mixed>|null $parameters Параметры для конструктора.
     * @return object
     * @throws ContainerExceptionInterface
     */
    public function build(string $class, ?array $parameters = null): object
    {
        $reflector = new \ReflectionClass($class);
        
        if (!$reflector->isInstantiable()) {
            throw new class("Class '{$class}' is not instantiable.")
                extends \RuntimeException implements ContainerExceptionInterface {};
        }

        $constructor = $reflector->getConstructor();
        if (!$constructor) {
            return $reflector->newInstance();
        }

        $dependencies = $this->resolveDependencies($constructor, $parameters);
        
        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Разрешает зависимости для конструктора.
     *
     * @param \ReflectionMethod $constructor
     * @param array<string, mixed>|null $parameters
     * @return array<mixed>
     */
    private function resolveDependencies(\ReflectionMethod $constructor, ?array $parameters): array 
    {
        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();
            
            // Проверяем явно переданные параметры
            if (array_key_exists($paramName, $parameters ?? [])) {
                $dependencies[] = $parameters[$paramName];
                continue;
            }
            
            // Обработка union types
            if ($paramType instanceof \ReflectionUnionType) {
                $resolved = false;
                $lastException = null;
                
                foreach ($paramType->getTypes() as $type) {
                    // Пропускаем встроенные типы в union type
                    if ($type->isBuiltin()) {
                        continue;
                    }
                    
                    $typeName = $type->getName();
                    try {
                        if ($typeName === self::class) {
                            $dependencies[] = $this;
                            $resolved = true;
                            break;
                        }
                        
                        if (class_exists($typeName)) {
                            $dependencies[] = $this->get($typeName);
                            $resolved = true;
                            break;
                        }
                    } catch (NotFoundExceptionInterface $e) {
                        $lastException = $e;
                    }
                }
                
                if ($resolved) {
                    continue;
                }
                
                // Если ни один тип не удалось разрешить и нет значения по умолчанию
                if (!$param->isDefaultValueAvailable()) {
                    throw new class(
                        "Unable to resolve any type from union type for parameter '{$paramName}' in constructor of '{$constructor->getDeclaringClass()->getName()}'" . 
                        ($lastException ? ": " . $lastException->getMessage() : ".")
                    ) extends \RuntimeException implements ContainerExceptionInterface {};
                }
            } 
            // Обработка обычных типов
            elseif ($paramType instanceof \ReflectionNamedType && !$paramType->isBuiltin()) {
                $typeName = $paramType->getName();
                
                if ($typeName === self::class) {
                    $dependencies[] = $this;
                    continue;
                }
                
                if (class_exists($typeName)) {
                    $dependencies[] = $this->get($typeName);
                    continue;
                }
            }
            
            // Если есть значение по умолчанию
            if ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
                continue;
            }
            
            throw new class(
                "Unable to resolve parameter '{$paramName}' in constructor of '{$constructor->getDeclaringClass()->getName()}'."
            ) extends \RuntimeException implements ContainerExceptionInterface {};
        }
        
        return $dependencies;
    }
}
