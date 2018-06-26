<?php

use Moro\Indexer\Common\Source\Adapter\MemoryAdapter;
use Moro\Indexer\Common\Source\Exception\AdapterFailedException;
use Moro\Indexer\Common\Source\Exception\DuplicateTypeException;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\Exception\UnknownTypeException;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;
use Moro\Indexer\Common\Source\Factory\ClassFactory;
use Moro\Indexer\Common\Source\Factory\ContainerFactory;
use Moro\Indexer\Common\Source\Manager\LazyManager;
use Moro\Indexer\Common\Source\Manager\SourceManager;
use Moro\Indexer\Common\Source\ManagerInterface;
use Moro\Indexer\Common\Source\Type\LazyType;
use Moro\Indexer\Common\Source\Type\SourceType;
use Moro\Indexer\Common\Source\TypeInterface;
use Moro\Indexer\Test\DummyNormalizer;
use Moro\Indexer\Test\FailedSourceAdapter;
use Moro\Indexer\Test\SimpleContainer;
use Moro\Indexer\Test\SimpleEntity;

/**
 * Class SourceTest
 */
class SourceTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    const SIMPLE  = 'simple';
    const UNKNOWN = 'unknown';

    protected function _initMemoryAdapter(): MemoryAdapter
    {
        $adapter = new MemoryAdapter();
        $adapter->addEntityRecord('1', 10, ['id' => 1, 'name' => 'Alpha']);
        $adapter->addEntityRecord('2', 30, ['id' => 2, 'name' => 'Echo']);
        $adapter->addEntityRecord('3', 40, ['id' => 3, 'name' => 'Victor']);
        $adapter->addEntityRecord('4', 50, ['id' => 4, 'name' => 'Sierra']);
        $adapter->addEntityRecord('5', 20, ['id' => 5, 'name' => 'Whiskey']);
        $adapter->addEntityRecord('6', 60, ['id' => 6, 'name' => 'November']);
        $adapter->addEntityRecord('7', 11, ['id' => 7]); // test wrong structure.

        return $adapter;
    }

    protected function _initSourceType(): SourceType
    {
        $factory = new ClassFactory();
        $factory->setEntityClass(SimpleEntity::class);

        $type = new SourceType();
        $type->setAdapter($this->_initMemoryAdapter());
        $type->setFactory($factory);
        $type->setCode(self::SIMPLE);

        return $type;
    }

    public function testMemoryAdapter()
    {
        $this->specify('Test empty memory adapter.', function () {
            $adapter = new MemoryAdapter();

            verify($adapter->receiveIdList(0, 10))->same([]);

            $this->assertThrows(NotFoundException::class, function () use ($adapter) {
                $adapter->receiveEntityById('1');
            });

            $adapter->addEntityRecord('1', 10, ['id' => 1, 'name' => 'Alpha']);

            verify($adapter->receiveIdList(0, 10))->same(['1' => 10]);
            verify($adapter->receiveEntityById('1'))->same(['id' => 1, 'name' => 'Alpha']);
        });

        $this->specify('Test filled memory adapter.', function () {
            $adapter = $this->_initMemoryAdapter();

            $result = ['6' => 60, '4' => 50, '3' => 40, '2' => 30, '5' => 20, '7' => 11, '1' => 10];
            verify($adapter->receiveIdList(0, 10))->same($result);

            $result = ['4' => 50, '3' => 40, '2' => 30, '5' => 20];
            verify($adapter->receiveIdList(1, 4))->same($result);

            $record = ['id' => 1, 'name' => 'Alpha'];
            verify($adapter->receiveEntityById('1'))->same($record);

            $record = ['id' => 2, 'name' => 'Echo'];
            verify($adapter->receiveEntityById('2'))->same($record);
        });
    }

    public function testSourceType(TypeInterface $type = null)
    {
        /** @var SourceType $type */
        $type || $type = new SourceType();

        $this->specify('Test empty source type.', function () use ($type) {
            $this->assertThrows(TypeError::class, function () use ($type) {
                $type->getCode();
            });

            $this->assertThrows(AssertionError::class, function () use ($type) {
                $type->getIdList(0, 10);
            });

            $this->assertThrows(AssertionError::class, function () use ($type) {
                $type->getEntityById('1');
            });
        });

        $this->specify('Test methods "setCode" and "getCode".', function () use ($type) {
            verify($type->setCode(self::SIMPLE))->same($type);
            verify($type->getCode())->same(self::SIMPLE);
        });

        $this->specify('Test methods "setAdapter".', function () use ($type) {
            verify($type->setAdapter($this->_initMemoryAdapter()))->same($type);
        });

        $this->specify('Test methods "addNormalizer".', function () use ($type) {
            verify($type->addNormalizer(new DummyNormalizer()))->same($type);
        });

        $this->specify('Test methods "setFactory".', function () use ($type) {
            $factory = new ClassFactory();
            $factory->setEntityClass(SimpleEntity::class);

            verify($type->setEntityFactory($factory))->same($type);
        });

        $this->specify('Check error for bad structure of entity record.', function () use ($type) {
            $msg = 'Errors in structure of source for "simple" with ID "7".' . PHP_EOL . 'Keys "id" and "name" required.';
            $this->assertThrows([WrongStructureException::class, $msg], function () use ($type) {
                $type->getEntityById('7');
            });
        });

        $this->specify('Check errors in adapter.', function () use ($type) {
            $type->setAdapter(new FailedSourceAdapter());
            $this->assertThrows(AdapterFailedException::class, function () use ($type) {
                $type->getEntityById('1');
            });
        });
    }

    public function testLazyType()
    {
        $container = new SimpleContainer();
        $container->set('type', new SourceType());

        $lazy = new LazyType($container, 'type');
        $this->testSourceType($lazy);
    }

    public function testSourceManager(ManagerInterface $manager = null)
    {
        $manager || $manager = new SourceManager();

        $this->specify('Test empty source manager.', function () use ($manager) {
            verify($manager->getTypes())->same([]);

            $this->assertThrows(UnknownTypeException::class, function () use ($manager) {
                $manager->getIdList(self::SIMPLE, 0, 10);
            });

            $this->assertThrows(UnknownTypeException::class, function () use ($manager) {
                $manager->getEntity(self::SIMPLE, '1');
            });
        });

        $this->specify('Test method "addType.', function () use ($manager) {
            verify($manager->addType($this->_initSourceType()))->same($manager);

            $this->assertThrows(DuplicateTypeException::class, function () use ($manager) {
                verify($manager->addType($this->_initSourceType()))->same($manager);
            });
        });

        $this->specify('Get list of ID.', function () use ($manager) {
            $result = ['6' => 60, '4' => 50, '3' => 40, '2' => 30, '5' => 20, '7' => 11, '1' => 10];
            verify($manager->getIdList(self::SIMPLE, 0, 10))->same($result);

            $result = ['4' => 50, '3' => 40, '2' => 30, '5' => 20];
            verify($manager->getIdList(self::SIMPLE, 1, 4))->same($result);
        });

        $this->specify('Get exists entity.', function () use ($manager) {
            $entity = $manager->getEntity(self::SIMPLE, '1');
            verify($entity->getId())->same('1');
            verify($entity->getName())->same('Alpha');

            $entity = $manager->getEntity(self::SIMPLE, '5');
            verify($entity->getId())->same('5');
            verify($entity->getName())->same('Whiskey');
        });
    }

    public function testLazyManager()
    {
        $container = new SimpleContainer();
        $container->set('manager', new SourceManager());

        $lazy = new LazyManager($container, 'manager');
        $this->testSourceManager($lazy);

        $factory = new ClassFactory();
        $factory->setEntityClass(SimpleEntity::class);

        $type = new SourceType();
        $type->setFactory($factory);
        $type->setAdapter($this->_initMemoryAdapter());
        $type->setCode('second type');

        verify($lazy->addType($type))->same($lazy);
        verify($lazy->getTypes())->same([self::SIMPLE, 'second type']);
    }

    public function testContainerFactory()
    {
        $container = new SimpleContainer();
        $container->set('entity', new SimpleEntity());

        $factory = new ContainerFactory($container);
        $factory->setEntityKey('entity');
        /** @noinspection PhpUnhandledExceptionInspection */
        verify($factory->newEntity())->isInstanceOf(SimpleEntity::class);
    }
}