<?php

use Moro\Indexer\Common\Transaction\Exception\DuplicateDriverException;
use Moro\Indexer\Common\Transaction\Exception\UnknownFacadeException;
use Moro\Indexer\Common\Transaction\Manager\TransactionManager;
use Moro\Indexer\Common\Transaction\Manager\LazyManager;
use Moro\Indexer\Common\Transaction\TransactionFacade;
use Moro\Indexer\Common\Transaction\ManagerInterface;
use Moro\Indexer\Test\DummyDriver;
use Moro\Indexer\Test\SimpleContainer;

/**
 * Class TransactionTest
 */
class TransactionTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    public function testTransactionManager(ManagerInterface $manager = null)
    {
        /** @var ManagerInterface $manager */
        $manager = $manager ?? new TransactionManager();
        /** @var DummyDriver $driver */
        $driver = null;
        /** @var \Moro\Indexer\Common\Transaction\TransactionFacade $facade */
        $facade = null;

        $this->specify('Test empty transaction manager', function() use ($manager) {
            verify($manager->execute(function() { return true; }))->true();
        });

        $this->specify('Add driver to transaction manager', function () use ($manager, &$driver, &$facade) {
            $driver = new DummyDriver();
            $facade = $manager->register($driver);

            verify($facade)->isInstanceOf(TransactionFacade::class);
            verify($facade->activate())->false();
        });

        $this->specify('Run transaction (good end)', function () use ($manager, $driver, $facade) {
            $driver->clear();
            $manager->execute(function() use ($facade) {
                verify($facade->activate())->true();
            });
            verify($driver->getActions())->same(['init', 'begin', 'commit', 'free']);
            $driver->clear();
            $manager->execute(function() use ($facade) {
                verify($facade->activate())->true();
                verify($facade->activate())->true();
            });
            verify($driver->getActions())->same(['init', 'begin', 'commit', 'free']);
        });

        $this->specify('Run transaction (bad end)', function () use ($manager, $driver, $facade) {
            $driver->clear();
            $this->assertThrows(\RuntimeException::class, function() use ($manager, $facade) {
                $manager->execute(function() use ($facade) {
                    $facade->activate();
                    throw new \RuntimeException();
                });
            });
            verify($driver->getActions())->same(['init', 'begin', 'rollback', 'free']);
            $driver->clear();
            $this->assertThrows(\RuntimeException::class, function() use ($manager, $facade) {
                $manager->execute(function() use ($facade) {
                    verify($facade->activate())->true();
                    verify($facade->activate())->true();
                    throw new \RuntimeException();
                });
            });
            verify($driver->getActions())->same(['init', 'begin', 'rollback', 'free']);
        });

        $this->specify('Test DuplicateDriver exception', function() use ($manager, $driver) {
            $this->assertThrows(DuplicateDriverException::class, function() use ($manager, $driver) {
                $manager->register($driver);
            });
        });

        $this->specify('Test UnknownFacade exception', function() use ($manager) {
            $this->assertThrows(UnknownFacadeException::class, function() use ($manager) {
                $manager->execute(function() use ($manager) {
                    $facade = new TransactionFacade($manager);
                    $facade->activate();
                });
            });
        });

        $this->specify('Test nested transactions.', function () use ($manager, $facade) {
            $result = $manager->execute(function() use ($manager, $facade) {
                verify($facade->activate())->true();
                return $manager->execute(function() use ($manager) {
                    return 1;
                });
            });

            verify($result)->same(1);
        });
    }

    public function testLazyManager()
    {
        $container = new SimpleContainer();
        $container->set('manager', new TransactionManager());

        $lazy = new LazyManager($container, 'manager');
        $this->testTransactionManager($lazy);
    }
}