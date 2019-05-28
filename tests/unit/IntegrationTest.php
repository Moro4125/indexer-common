<?php

use Moro\Container7\Container;
use Moro\Container7\Parameters;
use Moro\Indexer\Common\Bus\ManagerInterface as BusManager;
use Moro\Indexer\Common\Dispatcher\Event\ExceptionRepairedEvent;
use Moro\Indexer\Common\Dispatcher\ManagerInterface as EventManager;
use Moro\Indexer\Common\Integration\Container7\CommonProvider;
use Moro\Indexer\Common\Integration\Container7\ConfigurationProvider;
use Moro\Indexer\Common\Integration\Container7\DoctrineDBALProvider;
use Moro\Indexer\Common\MonolithFacade;
use Moro\Indexer\Common\Scheduler\EntryInterface;
use Moro\Indexer\Common\Scheduler\ManagerInterface as SchedulerManagerInterface;
use Moro\Indexer\Common\Source\Exception\AdapterFailedException as SourceAdapterFailedException;
use Moro\Indexer\Common\Source\Exception\NotFoundException;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;
use Moro\Indexer\Common\Action\UpdateEntity\Decorator\SourceRepeatDecorator;
use Moro\Indexer\Common\Transaction\ManagerInterface as TransactionManager;
use Moro\Indexer\Test\Container7\ConfigurationTestProvider;
use Moro\Indexer\Test\Container7\DoctrineDBALTestProvider;
use Moro\Indexer\Test\Container7\MemoryTestProvider;
use Moro\Indexer\Test\FailedSourceAdapter;
use Moro\Indexer\Test\SimpleEntity;

/**
 * Class IntegrationTest
 */
class IntegrationTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    const SIMPLE    = 'simple';
    const INDEX_1   = 'first';
    const INDEX_2   = 'second';
    const INDEX_3   = 'third';
    const KIND_JSON = 'json';

    public function testCommonProvider()
    {
        $this->specify('Test good scenario', function () {
            $parameters = new Parameters([
                CommonProvider::P_SOURCE_REPEAT => true,
            ]);

            $container = new Container($parameters);
            $container->addProvider(CommonProvider::class);
            $container->addProvider(MemoryTestProvider::class);

            /** @var MonolithFacade $facade */
            $facade = $container->get(MonolithFacade::class);
            verify($facade)->isInstanceOf(MonolithFacade::class);

            $this->_testFacade($facade);
        });

        $this->specify('Test RepeatAction', function () {
            $parameters = new Parameters([
                CommonProvider::P_SOURCE_REPEAT          => true,
                CommonProvider::P_SOURCE_REPEAT_INTERVAL => -10,
                CommonProvider::P_SOURCE_ADAPTER_CLASS   => FailedSourceAdapter::class,
            ]);

            $container = new Container($parameters);
            $container->addProvider(CommonProvider::class);
            $container->addProvider(MemoryTestProvider::class);

            /** @var MonolithFacade $facade */
            $facade = $container->get(MonolithFacade::class);
            verify($facade)->isInstanceOf(MonolithFacade::class);

            /** @var SchedulerManagerInterface $scheduler */
            $scheduler = $container->get(SchedulerManagerInterface::class);
            verify($scheduler)->isInstanceOf(SchedulerManagerInterface::class);

            /** @var EventManager $dispatcher */
            $dispatcher = $container->get(EventManager::class);
            verify($dispatcher)->isInstanceOf(EventManager::class);
            $eventFlag = false;

            $dispatcher->attach(ExceptionRepairedEvent::class,
                function (ExceptionRepairedEvent $event) use (&$eventFlag) {
                    $eventFlag = true;
                    verify($event->getException())->isInstanceOf(SourceAdapterFailedException::class);
                    verify($event->getRepairedBy())->same(SourceRepeatDecorator::class);
                });

            $facade->updateEntity(self::SIMPLE, '1');
            verify($eventFlag)->true();

            $entry = $scheduler->derive();
            verify($entry)->isInstanceOf(EntryInterface::class);
            verify($entry->getAction())->same('update');
            verify($entry->getType())->same(self::SIMPLE);
            verify($entry->getId())->same('1');
        });
    }

    public function testDoctrineDBALProvider()
    {
        $parameters = new Parameters([
            CommonProvider::P_TYPES => [
                self::SIMPLE => SimpleEntity::class,
            ],
        ]);

        $container = new Container($parameters);
        $container->addProvider(CommonProvider::class);
        $container->addProvider(DoctrineDBALProvider::class);
        $container->addProvider(DoctrineDBALTestProvider::class);

        /** @var MonolithFacade $facade */
        $facade = $container->get(MonolithFacade::class);
        verify($facade)->isInstanceOf(MonolithFacade::class);

        $this->_testFacade($facade);

        /** @var BusManager $bus */
        $bus = $container->get(BusManager::class);
        verify($bus)->isInstanceOf(BusManager::class);
        $bus->setOwner('ya');
        $message = ['text' => 'hello'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertThrows(RuntimeException::class, function () use ($container, $message, $bus) {
            /** @var TransactionManager $transaction */
            $transaction = $container->get(TransactionManager::class);
            verify($transaction)->isInstanceOf(TransactionManager::class);

            $transaction->execute(function () use ($container, $message, $bus) {
                $bus->send($message, 'target');

                throw new RuntimeException();
            });
        });

        verify($bus->read('target'))->null();
    }

    public function testConfigurationProvider()
    {
        $container = new Container();
        $container->addProvider(CommonProvider::class);
        $container->addProvider(ConfigurationProvider::class);
        $container->addProvider(ConfigurationTestProvider::class);

        /** @var MonolithFacade $facade */
        $facade = $container->get(MonolithFacade::class);
        verify($facade)->isInstanceOf(MonolithFacade::class);

        $this->_testFacade($facade);
    }

    protected function _testFacade(MonolithFacade $facade)
    {
        $this->specify('Update all entities.', function () use ($facade) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $facade->updateEntity(self::SIMPLE, '1');
            /** @noinspection PhpUnhandledExceptionInspection */
            $facade->updateEntity(self::SIMPLE, '2');
            /** @noinspection PhpUnhandledExceptionInspection */
            $facade->updateEntity(self::SIMPLE, '3');
            /** @noinspection PhpUnhandledExceptionInspection */
            $facade->updateEntity(self::SIMPLE, '4');
            /** @noinspection PhpUnhandledExceptionInspection */
            $facade->updateEntity(self::SIMPLE, '5');
            /** @noinspection PhpUnhandledExceptionInspection */
            $facade->updateEntity(self::SIMPLE, '6');

            $this->assertThrows(WrongStructureException::class, function () use ($facade) {
                $facade->updateEntity(self::SIMPLE, '7');
            });

            $this->assertThrows(NotFoundException::class, function () use ($facade) {
                $facade->updateEntity(self::SIMPLE, '8');
            });
        });

        $this->specify('Check indexes.', function () use ($facade) {
            $GLOBALS['stop'] = 1;
            verify($facade->receiveIds(self::INDEX_1))->same(['1']);
            verify($facade->receiveIds(self::INDEX_2))->same(['1', '2']);
            verify($facade->receiveIds(self::INDEX_3))->same(['5', '2', '4']);
        });

        $this->specify('Receive entities.', function () use ($facade) {
            $r = ['{"id":"1","name":"Alpha","updated_at":"10"}'];
            verify($facade->receiveViews(self::INDEX_1, self::KIND_JSON))->same($r);
        });

        $this->specify('Drop entity.', function () use ($facade) {
            $facade->removeEntity(self::SIMPLE, '1');
            verify($facade->receiveIds(self::INDEX_1))->same([]);
            verify($facade->receiveIds(self::INDEX_2))->same(['2']);
            verify($facade->receiveViews(self::INDEX_1, self::KIND_JSON))->same([]);
        });
    }
}