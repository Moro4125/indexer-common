<?php

use Moro\Indexer\Common\View\Exception\DuplicateKindException;
use Moro\Indexer\Common\View\Exception\DuplicateTypeException;
use Moro\Indexer\Common\View\Exception\KindFailedException;
use Moro\Indexer\Common\View\Exception\UnknownKindException;
use Moro\Indexer\Common\View\Exception\UnknownTypeException;
use Moro\Indexer\Common\View\Manager\LazyManager;
use Moro\Indexer\Common\View\Manager\ViewManager;
use Moro\Indexer\Common\View\ManagerInterface;
use Moro\Indexer\Common\View\Storage\MemoryStorage;
use Moro\Indexer\Common\View\Type\LazyType;
use Moro\Indexer\Common\View\Type\ViewType;
use Moro\Indexer\Common\View\TypeInterface;
use Moro\Indexer\Test\FailedKind;
use Moro\Indexer\Test\SimpleContainer;
use Moro\Indexer\Test\SimpleEntity;
use Moro\Indexer\Test\SimpleKind;

/**
 * Class ViewTest
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    const SIMPLE = 'simple';
    const FIRST  = 'first';
    const SECOND = 'second';
    const THIRD  = 'third';

    public function testMemoryStorage()
    {
        $storage = new MemoryStorage();

        $this->specify('Test empty storage', function () use ($storage) {
            verify($storage->find(self::SIMPLE, '1'))->same([]);
            verify($storage->load(self::SIMPLE, self::FIRST, '1'))->null();
        });

        $this->specify('Add first item to first kind', function () use ($storage) {
            $storage->save(self::SIMPLE, self::FIRST, '1', 'content1');
            verify($storage->find(self::SIMPLE, '1'))->same([self::FIRST]);
            verify($storage->load(self::SIMPLE, self::FIRST, '1'))->same('content1');
        });

        $this->specify('Add first item to second kind', function () use ($storage) {
            $storage->save(self::SIMPLE, self::SECOND, '1', 'content2');
            verify($storage->find(self::SIMPLE, '1'))->same([self::FIRST, self::SECOND]);
            verify($storage->load(self::SIMPLE, self::SECOND, '1'))->same('content2');
        });

        $this->specify('Update first item', function () use ($storage) {
            $storage->save(self::SIMPLE, self::FIRST, '1', 'content3');
            verify($storage->find(self::SIMPLE, '1'))->same([self::FIRST, self::SECOND]);
            verify($storage->load(self::SIMPLE, self::FIRST, '1'))->same('content3');
        });

        $this->specify('Drop first item from first kind', function () use ($storage) {
            verify($storage->drop(self::SIMPLE, self::FIRST, '1'))->true();
            verify($storage->find(self::SIMPLE, '1'))->same([self::SECOND]);
            verify($storage->load(self::SIMPLE, self::FIRST, '1'))->null();
            verify($storage->drop(self::SIMPLE, self::FIRST, '1'))->false();
        });

        $this->specify('Drop first item from second kind', function () use ($storage) {
            verify($storage->drop(self::SIMPLE, self::SECOND, '1'))->true();
            verify($storage->find(self::SIMPLE, '1'))->same([]);
            verify($storage->load(self::SIMPLE, self::SECOND, '1'))->null();
            verify($storage->drop(self::SIMPLE, self::SECOND, '1'))->false();
        });
    }

    public function testViewType(TypeInterface $type = null)
    {
        /** @var TypeInterface $type */
        $type = $type ?? new ViewType();

        $this->specify('Test empty type', function () use ($type) {
            verify($type->getCode())->same(ViewType::class);

            $this->assertThrows(UnknownKindException::class, function () use ($type) {
                $type->handle(self::FIRST, new SimpleEntity('1', 'Alpha'));
            });

            try {
                $type->handle(self::FIRST, new SimpleEntity('1', 'Alpha'));
            } catch (UnknownKindException $exception) {
                verify($exception->getKind())->same(self::FIRST);
            }
        });

        $this->specify('Set code', function () use ($type) {
            verify($type->setCode(self::SIMPLE))->same($type);
        });

        $this->specify('Add first kind', function () use ($type) {
            verify($type->addKind(new SimpleKind(self::FIRST)))->same($type);
        });

        $this->specify('Add entity to first kind', function () use ($type) {
            $entity = new SimpleEntity('1', 'Alpha');
            verify($type->handle(self::FIRST, $entity))->same('{"id":"1","name":"Alpha"}');
        });

        $this->specify('Add second kind', function () use ($type) {
            verify($type->addKind(new SimpleKind(self::SECOND)))->same($type);
        });

        $this->specify('Add entity to second kind', function () use ($type) {
            $entity = new SimpleEntity('1', 'Echo');
            verify($type->handle(self::SECOND, $entity))->same('{"id":"1","name":"Echo"}');
        });

        $this->specify('Add first kind twice', function () use ($type) {
            $this->assertThrows(DuplicateKindException::class, function () use ($type) {
                $type->addKind(new SimpleKind(self::FIRST));
            });
        });

        $this->specify('Work with third kind, that is bad', function () use ($type) {
            verify($type->addKind(new FailedKind(self::THIRD)))->same($type);

            $this->assertThrows(KindFailedException::class, function () use ($type) {
                $type->handle(self::THIRD, new SimpleEntity('2', 'Echo'));
            });
        });
    }

    public function testLazyType()
    {
        $container = new SimpleContainer();
        $container->set('type', new ViewType());

        $lazy = new LazyType($container, 'type');
        $this->testViewType($lazy);
    }

    public function testViewManager(ManagerInterface $manager = null)
    {
        /** @var ManagerInterface $manager */
        $manager = $manager ?? new ViewManager();

        $this->specify('Test empty manager', function () use ($manager) {
            verify($manager->getTypes())->same([]);

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->findKinds(self::SIMPLE, '1');
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->save(self::SIMPLE, self::FIRST, new SimpleEntity('1', 'Alpha'));
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->load(self::SIMPLE, self::FIRST, '1');
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->drop(self::SIMPLE, self::FIRST, '1');
            });
        });

        $this->specify('Set storage', function () use ($manager) {
            verify($manager->setStorage(new MemoryStorage()))->same($manager);
        });

        $this->specify('Test manager without types', function () use ($manager) {
            verify($manager->getTypes())->same([]);

            verify($manager->findKinds(self::SIMPLE, '1'))->same([]);

            $this->assertThrows(UnknownTypeException::class, function () use ($manager) {
                $manager->save(self::SIMPLE, self::FIRST, new SimpleEntity('1', 'Alpha'));
            });

            verify($manager->load(self::SIMPLE, self::FIRST, '1'))->null();
            verify($manager->drop(self::SIMPLE, self::FIRST, '1'))->false();
        });

        $this->specify('Add type', function () use ($manager) {
            $type = new ViewType();
            $type->setCode(self::SIMPLE);
            $type->addKind(new SimpleKind(self::FIRST));
            $type->addKind(new SimpleKind(self::SECOND));

            verify($manager->addType($type))->same($manager);
            verify($manager->getTypes())->same([self::SIMPLE]);

            $this->assertThrows(DuplicateTypeException::class, function () use ($manager, $type) {
                $manager->addType($type);
            });
        });

        $this->specify('Add first entity to first kind', function () use ($manager) {
            $entity = new SimpleEntity('1', 'Alpha');
            $manager->save(self::SIMPLE, self::FIRST, $entity);
            $result = $manager->load(self::SIMPLE, self::FIRST, '1');
            verify($result)->same('{"id":"1","name":"Alpha"}');
            verify($manager->findKinds(self::SIMPLE, '1'))->same([self::FIRST]);
        });

        $this->specify('Add first entity to second kind', function () use ($manager) {
            $entity = new SimpleEntity('1', 'Echo');
            $manager->save(self::SIMPLE, self::SECOND, $entity);
            $result = $manager->load(self::SIMPLE, self::SECOND, '1');
            verify($result)->same('{"id":"1","name":"Echo"}');
            verify($manager->findKinds(self::SIMPLE, '1'))->same([self::FIRST, self::SECOND]);
        });

        $this->specify('Drop first entity from first kind', function () use ($manager) {
            $manager->drop(self::SIMPLE, self::FIRST, '1');
            verify($manager->load(self::SIMPLE, self::FIRST, '1'))->null();
            verify($manager->findKinds(self::SIMPLE, '1'))->same([self::SECOND]);
        });
    }

    public function testLazyManager()
    {
        $container = new SimpleContainer();
        $container->set('manager', new ViewManager());

        $lazy = new LazyManager($container, 'manager');
        $this->testViewManager($lazy);
    }
}