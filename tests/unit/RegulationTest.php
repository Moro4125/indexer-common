<?php

use Moro\Indexer\Common\Regulation\Exception\DuplicateTypeException;
use Moro\Indexer\Common\Regulation\Exception\InstructionFailedException;
use Moro\Indexer\Common\Regulation\Exception\UnknownTypeException;
use Moro\Indexer\Common\Regulation\Factory\ClassFactory;
use Moro\Indexer\Common\Regulation\Factory\ContainerFactory;
use Moro\Indexer\Common\Regulation\Manager\LazyManager;
use Moro\Indexer\Common\Regulation\Manager\RegulationManager;
use Moro\Indexer\Common\Regulation\ManagerInterface;
use Moro\Indexer\Common\Regulation\Result\RegulationResult;
use Moro\Indexer\Common\Regulation\ResultInterface;
use Moro\Indexer\Common\Regulation\Type\LazyType;
use Moro\Indexer\Common\Regulation\Type\RegulationType;
use Moro\Indexer\Common\Regulation\TypeInterface;
use Moro\Indexer\Test\FailedEntity;
use Moro\Indexer\Test\SimpleContainer;
use Moro\Indexer\Test\SimpleEntity;
use Moro\Indexer\Test\SimpleInstruction;

/**
 * Class RegulationTest
 */
class RegulationTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    const SIMPLE = 'simple';

    public function testResult()
    {
        $result = new RegulationResult();

        $this->specify('Collection is empty.', function () use ($result) {
            verify($result->getIndexListCount())->same(0);
            verify($iterator = $result->getIndexListIterator())->isInstanceOf(\Iterator::class);
            verify($iterator->valid())->false();

            verify($result->getEntryListCount())->same(0);
            verify($iterator = $result->getEntryListIterator())->isInstanceOf(\Iterator::class);
            verify($iterator->valid())->false();

            verify($result->getKindListCount())->same(0);
            verify($iterator = $result->getKindListIterator())->isInstanceOf(\Iterator::class);
            verify($iterator->valid())->false();
        });

        $this->specify('Add first index to result', function () use ($result) {
            verify($result->addToIndex('first', '2316'))->same($result);
            verify($iterator = $result->getIndexListIterator())->isInstanceOf(\Iterator::class);
            verify($iterator->valid())->true();
            verify($iterator->key())->same('first');
            verify($iterator->current())->same('2316');
            $iterator->next();
            verify($iterator->valid())->false();
        });

        $this->specify('Add second index to result', function () use ($result) {
            verify($result->addToIndex('second', '2317'))->same($result);
            verify($iterator = $result->getIndexListIterator())->isInstanceOf(\Iterator::class);
            verify($iterator->valid())->true();
            verify($iterator->key())->same('first');
            verify($iterator->current())->same('2316');
            $iterator->next();
            verify($iterator->valid())->true();
            verify($iterator->key())->same('second');
            verify($iterator->current())->same('2317');
            $iterator->next();
            verify($iterator->valid())->false();
        });

        $this->specify('Add first entry to scheduler', function () use ($result) {
            verify($result->addToScheduler(1000))->same($result);
            verify($iterator = $result->getEntryListIterator())->isInstanceOf(\Iterator::class);
            verify($iterator->valid())->true();
            verify($iterator->current())->same(1000);
            $iterator->next();
            verify($iterator->valid())->false();
        });

        $this->specify('Add second entry to scheduler', function () use ($result) {
            verify($result->addToScheduler(2000))->same($result);
            verify($iterator = $result->getEntryListIterator())->isInstanceOf(\Iterator::class);
            verify($iterator->valid())->true();
            verify($iterator->current())->same(1000);
            $iterator->next();
            verify($iterator->valid())->true();
            verify($iterator->current())->same(2000);
            $iterator->next();
            verify($iterator->valid())->false();
        });

        $this->specify('Add third entry, that same as first, to scheduler', function () use ($result) {
            verify($result->addToScheduler(1000))->same($result);
            verify($iterator = $result->getEntryListIterator())->isInstanceOf(\Iterator::class);
            verify($iterator->valid())->true();
            verify($iterator->current())->same(1000);
            $iterator->next();
            verify($iterator->valid())->true();
            verify($iterator->current())->same(2000);
            $iterator->next();
            verify($iterator->valid())->false();
        });

        $this->specify('Add first kind', function () use ($result) {
            verify($result->addUsedKind('first'))->same($result);
            verify($iterator = $result->getKindListIterator())->isInstanceOf(\Iterator::class);
            verify($iterator->valid())->true();
            verify($iterator->current())->same('first');
            $iterator->next();
            verify($iterator->valid())->false();
        });

        $this->specify('Add second kind', function () use ($result) {
            verify($result->addUsedKind('second'))->same($result);
            verify($iterator = $result->getKindListIterator())->isInstanceOf(\Iterator::class);
            verify($iterator->valid())->true();
            verify($iterator->current())->same('first');
            $iterator->next();
            verify($iterator->valid())->true();
            verify($iterator->current())->same('second');
            $iterator->next();
            verify($iterator->valid())->false();
        });

        $this->specify('Add third kind, that same as first', function () use ($result) {
            verify($result->addUsedKind('first'))->same($result);
            verify($iterator = $result->getKindListIterator())->isInstanceOf(\Iterator::class);
            verify($iterator->valid())->true();
            verify($iterator->current())->same('first');
            $iterator->next();
            verify($iterator->valid())->true();
            verify($iterator->current())->same('second');
            $iterator->next();
            verify($iterator->valid())->false();
        });
    }

    public function testRegulationType(TypeInterface $type = null)
    {
        /** @var TypeInterface $type */
        $type = $type ?? new RegulationType();

        $this->specify('Type is empty.', function () use ($type) {
            $this->assertThrows(TypeError::class, function() use ($type) {
                $type->getCode();
            });

            $this->assertThrows(AssertionError::class, function () use ($type) {
                $type->handle(new SimpleEntity());
            });
        });

        $this->specify('Call method "setCode".', function () use ($type) {
            verify($type->setCode(self::SIMPLE))->same($type);
        });

        $this->specify('Call method "setResultFactory".', function () use ($type) {
            $factory = new ClassFactory();
            $factory->setResultClass(RegulationResult::class);
            verify($type->setResultFactory($factory))->same($type);
        });

        $this->specify('Call method "addInstruction".', function () use ($type) {
            verify($type->addInstruction(new SimpleInstruction(['Echo'], ['first'])))->same($type);
            verify($type->addInstruction(new SimpleInstruction(['Echo', 'Sierra'], ['second'])))->same($type);
            verify($type->addInstruction(new SimpleInstruction(['Whiskey'], ['second', 'third'])))->same($type);
        });

        $this->specify('Call method "handle".', function () use ($type) {
            $collection = $type->handle(new SimpleEntity(1, 'Echo'));
            verify($collection)->isInstanceOf(ResultInterface::class);
            verify(iterator_to_array($collection->getIndexListIterator()))->same(['first' => '0', 'second' => '0']);

            $collection = $type->handle(new SimpleEntity(2, 'Sierra'));
            verify($collection)->isInstanceOf(ResultInterface::class);
            verify(iterator_to_array($collection->getIndexListIterator()))->same(['second' => '0']);

            $collection = $type->handle(new SimpleEntity(3, 'Whiskey'));
            verify($collection)->isInstanceOf(ResultInterface::class);
            verify(iterator_to_array($collection->getIndexListIterator()))->same(['second' => '0', 'third' => '1']);

            $collection = $type->handle(new SimpleEntity(4, 'November'));
            verify($collection)->isInstanceOf(ResultInterface::class);
            verify(iterator_to_array($collection->getIndexListIterator()))->same([]);

            $this->assertThrows(InstructionFailedException::class, function() use ($type) {
                verify($type->handle(new FailedEntity()))->isInstanceOf(ResultInterface::class);
            });
        });
    }

    public function testLazyType()
    {
        $container = new SimpleContainer();
        $container->set('type', new RegulationType());

        $lazy = new LazyType($container, 'type');
        $this->testRegulationType($lazy);
    }

    public function testRegulationManager(ManagerInterface $manager = null)
    {
        $manager = $manager ?? new RegulationManager();

        $this->specify('Empty manager.', function() use ($manager) {
            verify($manager->getTypes())->same([]);

            $this->assertThrows(UnknownTypeException::class, function () use ($manager) {
                $manager->handle(self::SIMPLE, new SimpleEntity(1, 'Alpha'));
            });
        });

        $type = new RegulationType();
        $type->setCode(self::SIMPLE);
        $type->setResultFactory((new ClassFactory())->setResultClass(RegulationResult::class));
        $type->addInstruction(new SimpleInstruction(['Echo'], ['first']));
        $type->addInstruction(new SimpleInstruction(['Echo', 'Sierra'], ['second']));
        $type->addInstruction(new SimpleInstruction(['Whiskey'], ['second', 'third']));

        $this->specify('Call method "addType".', function() use ($manager, $type) {
            verify($manager->addType($type))->same($manager);

            $this->assertThrows(DuplicateTypeException::class, function() use ($manager, $type) {
                $manager->addType($type);
            });
        });

        $this->specify('Call method "handle".', function () use ($manager) {
            $collection = $manager->handle(self::SIMPLE, new SimpleEntity(1, 'Echo'));
            verify($collection)->isInstanceOf(ResultInterface::class);
            verify(iterator_to_array($collection->getIndexListIterator()))->same(['first' => '0', 'second' => '0']);

            $collection = $manager->handle(self::SIMPLE, new SimpleEntity(2, 'Sierra'));
            verify($collection)->isInstanceOf(ResultInterface::class);
            verify(iterator_to_array($collection->getIndexListIterator()))->same(['second' => '0']);

            $collection = $manager->handle(self::SIMPLE, new SimpleEntity(3, 'Whiskey'));
            verify($collection)->isInstanceOf(ResultInterface::class);
            verify(iterator_to_array($collection->getIndexListIterator()))->same(['second' => '0', 'third' => '1']);

            $collection = $manager->handle(self::SIMPLE, new SimpleEntity(4, 'November'));
            verify($collection)->isInstanceOf(ResultInterface::class);
            verify(iterator_to_array($collection->getIndexListIterator()))->same([]);

            $this->assertThrows(InstructionFailedException::class, function() use ($manager) {
                $result = $manager->handle(self::SIMPLE, new FailedEntity());
                verify($result)->isInstanceOf(ResultInterface::class);
            });
        });
    }

    public function testLazyManager()
    {
        $container = new SimpleContainer();
        $container->set('manager', new RegulationManager());

        $lazy = new LazyManager($container, 'manager');
        $this->testRegulationManager($lazy);

        $type = new RegulationType();
        $type->setResultFactory((new ClassFactory())->setResultClass(SimpleEntity::class));
        $type->addInstruction(new SimpleInstruction(['November'], ['none']));
        $type->setCode('second type');

        verify($lazy->addType($type))->same($lazy);
        verify($lazy->getTypes())->same([self::SIMPLE, 'second type']);
    }

    public function testContainerFactory()
    {
        $container = new SimpleContainer();
        $container->set('collection', new RegulationResult());

        $factory = new ContainerFactory($container);
        $factory->setResultKey('collection');
        /** @noinspection PhpUnhandledExceptionInspection */
        verify($factory->newResult())->isInstanceOf(RegulationResult::class);
    }
}