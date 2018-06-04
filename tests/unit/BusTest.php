<?php

use Moro\Indexer\Common\Bus\Manager\BusManager;
use Moro\Indexer\Common\Bus\Manager\LazyManager;
use Moro\Indexer\Common\Bus\ManagerInterface;
use Moro\Indexer\Test\DummyAdapter;
use Moro\Indexer\Test\SimpleContainer;
use Moro\Indexer\Test\FailedBusAdapter;
use Moro\Indexer\Common\Bus\Exception\AdapterFailedException;

/**
 * Class BusTest
 */
class BusTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    public function testBusManager(ManagerInterface $manager = null)
    {
        $manager = $manager ?? new BusManager();
        $adapter = new DummyAdapter();

        $this->specify('Test empty manager', function () use ($manager) {
            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->read();
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->send([]);
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->call([]);
            });
        });

        $this->specify('Set bus adapter', function () use ($manager, $adapter) {
            verify($manager->setAdapter($adapter))->same($manager);
        });

        $this->specify('Test manager without identifiers', function () use ($manager) {
            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->read();
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->send([]);
            });

            $this->assertThrows(AssertionError::class, function () use ($manager) {
                $manager->call([]);
            });
        });

        $this->specify('Set bus identifiers', function () use ($manager) {
            verify($manager->setOwner('client'))->same($manager);
            verify($manager->setTarget('server'))->same($manager);
            verify($manager->setIdentifier('unique'))->same($manager);
        });

        $senderClient = ['sender' => ['client', 'unique']];
        $senderServer = ['sender' => ['server', 'unique']];

        $this->specify('Test "send" and "read" methods', function () use ($manager, $senderClient) {
            $manager->send(['hello', 'timestamp' => 1]);
            verify($manager->read())->same(array_merge(['hello', 'timestamp' => 1], $senderClient));
        });

        $callMessage = ['action' => 'get', 'timestamp' => 2];
        $callResult = ['action' => 'receive', 'timestamp' => 3];

        $adapter->setCallback(function () use ($manager, $callMessage, $callResult, $senderClient) {
            try {
                $manager->setOwner('server');

                $message = $manager->read('server');
                verify($message)->same(array_merge($callMessage, $senderClient));
                list($target, $identifier) = $message['sender'];
                $manager->send($callResult, $target, $identifier);
            }
            finally {
                $manager->setOwner('client');
            }
        });

        $callResult = array_merge($callResult, $senderServer);

        $this->specify('Test "call" method', function () use ($manager, $callMessage, $callResult) {
            verify($manager->call($callMessage))->same($callResult);
        });

        $this->specify('Test exceptions', function () use ($manager) {
            $manager->setAdapter(new FailedBusAdapter());

            $this->assertThrows(AdapterFailedException::class, function() use ($manager) {
                $manager->read();
            });

            $this->assertThrows(AdapterFailedException::class, function() use ($manager) {
                $manager->send([]);
            });

            $this->assertThrows(AdapterFailedException::class, function() use ($manager) {
                $manager->call([]);
            });
        });
    }

    public function testLazyManager()
    {
        $container = new SimpleContainer();
        $container->set('manager', new BusManager());

        $lazy = new LazyManager($container, 'manager');
        $this->testBusManager($lazy);
    }
}