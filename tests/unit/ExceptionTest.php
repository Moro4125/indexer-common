<?php

use Moro\Indexer\Common\Regulation\Exception\UnknownTypeException as RegulationUnknownTypeException;
use Moro\Indexer\Common\Source\Exception\UnknownTypeException as SourceUnknownTypeException;

/**
 * Class ExceptionTest
 */
class ExceptionTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    const SIMPLE = 'simple';

    public function testUnknownTypeException()
    {
        $this->specify('Exception for "Source".', function () {
            $exception = new SourceUnknownTypeException();
            verify($exception->setType(self::SIMPLE))->same($exception);
            verify($exception->getType())->same(self::SIMPLE);
        });

        $this->specify('Exception for "Regulation".', function () {
            $exception = new RegulationUnknownTypeException();
            verify($exception->setType(self::SIMPLE))->same($exception);
            verify($exception->getType())->same(self::SIMPLE);
        });
    }
}