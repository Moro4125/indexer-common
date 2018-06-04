<?php

use Moro\Indexer\Common\Index\Manager\IndexManager;
use Moro\Indexer\Common\Index\Manager\LazyManager;
use Moro\Indexer\Common\Index\ManagerInterface;
use Moro\Indexer\Common\Index\Storage\MemoryStorage;
use Moro\Indexer\Common\Index\Exception\DuplicateIndexException;
use Moro\Indexer\Test\SimpleContainer;

/**
 * Class IndexTest
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    const SIMPLE = 'simple';
    const SECOND = 'second';

    const IDX_1  = 'index1';
    const IDX_2  = 'index2';
    const IDX_3  = 'index3';

    public function testIndexManager(ManagerInterface $manager = null)
    {
        /** @var IndexManager $manager */
        $manager = $manager ?? new IndexManager();

        $this->specify('Test empty index manager.', function () use ($manager) {
            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->hasIndex(self::IDX_1);
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->addIndex(self::IDX_1, self::SIMPLE);
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->findIndexes(self::SIMPLE);
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->dropIndex(self::IDX_1);
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->insert(self::IDX_1, '1', '1');
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->select(self::IDX_1, 0, 10);
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->remove(self::IDX_1, '1');
            });
        });

        $this->specify('Call method "setStorage".', function () use ($manager) {
            verify($manager->setStorage(new MemoryStorage()))->same($manager);
        });

        $this->specify('Add first index.', function() use ($manager) {
            verify($manager->hasIndex(self::IDX_1))->false();

            $manager->addIndex(self::IDX_1, self::SIMPLE);

            verify($manager->hasIndex(self::IDX_1))->true();
            verify($manager->findIndexes(self::SIMPLE))->same([self::IDX_1]);
        });

        $this->specify('Try add first index twice.', function() use ($manager) {
            $this->assertThrows(DuplicateIndexException::class, function() use ($manager) {
                $manager->addIndex(self::IDX_1, self::SIMPLE);
            });
        });

        $this->specify('Add second index.', function() use ($manager) {
            $manager->addIndex(self::IDX_2, self::SIMPLE);

            verify($manager->hasIndex(self::IDX_2))->true();
            verify($manager->findIndexes(self::SIMPLE))->same([self::IDX_1, self::IDX_2]);

        });

        $this->specify('Add third index.', function() use ($manager) {
            $manager->addIndex(self::IDX_3, self::SECOND);

            verify($manager->hasIndex(self::IDX_3))->true();
            verify($manager->findIndexes(self::SIMPLE))->same([self::IDX_1, self::IDX_2]);
            verify($manager->findIndexes(self::SECOND))->same([self::IDX_3]);
        });

        $this->specify('Add items to indexes.', function() use ($manager) {
            $manager->insert(self::IDX_1, '1', '02');
            $manager->insert(self::IDX_2, '2', '03');
            $manager->insert(self::IDX_1, '3', '01');
            $manager->insert(self::IDX_2, '3', '01');
            $manager->insert(self::IDX_3, '4', '04');
            $manager->insert(self::IDX_3, '2', '02');

            verify($manager->select(self::IDX_1))->same(['3', '1']);
            verify($manager->select(self::IDX_2))->same(['3', '2']);
            verify($manager->select(self::IDX_3))->same(['2', '4']);
            verify($manager->select('unknown'))->same([]);

            verify($manager->findIndexes(self::SIMPLE, '1'))->same([self::IDX_1]);
            verify($manager->findIndexes(self::SIMPLE, '2'))->same([self::IDX_2]);
            verify($manager->findIndexes(self::SIMPLE, '3'))->same([self::IDX_1, self::IDX_2]);
            verify($manager->findIndexes(self::SIMPLE, '4'))->same([]);
            verify($manager->findIndexes(self::SIMPLE, '5'))->same([]);
            verify($manager->findIndexes(self::SECOND, '4'))->same([self::IDX_3]);
            verify($manager->findIndexes(self::SECOND, '2'))->same([self::IDX_3]);

            verify($manager->remove(self::IDX_1, '1'))->true();
            verify($manager->select(self::IDX_1))->same(['3']);
            verify($manager->remove(self::IDX_1, '3'))->true();
            verify($manager->select(self::IDX_1))->same([]);
            verify($manager->remove(self::IDX_2, '3'))->true();
            verify($manager->select(self::IDX_2))->same(['2']);
            verify($manager->remove(self::IDX_3, '1'))->false();
        });

        $this->specify('Drop second index.', function() use ($manager) {
            verify($manager->dropIndex(self::IDX_2))->true();
            verify($manager->hasIndex(self::IDX_2))->false();

            verify($manager->findIndexes(self::SIMPLE))->same([self::IDX_1]);
            verify($manager->findIndexes(self::SIMPLE, '2'))->same([]);
        });

        $this->specify('Get type by index name.', function() use ($manager) {
            verify($manager->getTypeByIndex(self::IDX_1))->same(self::SIMPLE);
        });
    }

    public function testLazyManager()
    {
        $container = new SimpleContainer();
        $container->set('manager', new IndexManager());

        $lazy = new LazyManager($container, 'manager');
        $this->testIndexManager($lazy);
    }
}