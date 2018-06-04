<?php

use Moro\Indexer\Common\Source\Normalizer\UniversalNormalizer;

/**
 * Class UniversalNormalizerTest
 */
class UniversalNormalizerTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    public function test()
    {
        $record = [
            'id'          => 1,
            'name'        => 'universal',
            'active_to'   => null,
            'content'     => [
                'announce' => [
                    'lead' => 'Hello, tester!',
                    'lock' => true,
                ],
                'tags'     => [
                    'tag1',
                    'tag2',
                ],
                'authors'  => [
                    ['name' => 'Alpha', 'gender' => 'M'],
                    ['name' => 'Echo', 'gender' => 'W', 'unique' => true, 'years' => 25],
                    ['name' => 'Victor', 'gender' => 'M', 'flag', 'years' => 30],
                ],
            ],
            'white-list'  => [
                'Victor'
            ],
            'empty-list'  => [],
        ];

        $normalizer = new UniversalNormalizer();
        $normalizer->addCondition('[id="2"]');

        verify($normalizer->normalize($record))->null();

        $normalizer = new UniversalNormalizer();
        $normalizer->addCondition('[id="1"]');
        $normalizer->addRule('content/announce', '');

        verify($normalizer->normalize($record))->same([
            'lead' => 'Hello, tester!',
            'lock' => true,
        ]);

        $normalizer = new UniversalNormalizer();
        $normalizer->addCondition('[id="1"]');
        $normalizer->addCondition('[name="universal"]');

        $normalizer->addRule('id', 'id');
        $normalizer->addRule('name', 'title');
        $normalizer->addRule('|keys("active_to")', 'meta');
        $normalizer->addRule('content/authors/*[gender="W"]', 'authors[]');
        $normalizer->addRule('content/authors/*[gender="M"]', 'authors[]');
        $normalizer->addRule('"27"|int', 'authors/1/years');
        $normalizer->addRule('|null', 'authors/*/unique');
        $normalizer->addRule('|null', 'authors/*/0');

        $normalizer->addRule('id', 'title?');
        $normalizer->addRule('id', 'ids?');

        verify($normalizer->normalize($record))->same([
            'id' => 1,
            'title' => 'universal',
            'meta' => [
                'active_to' => null,
            ],
            'authors' => [
                ['name' => 'Echo', 'gender' => 'W', 'years' => 25],
                ['name' => 'Alpha', 'gender' => 'M', 'years' => 27],
                ['name' => 'Victor', 'gender' => 'M', 'years' => 30],
            ],
            'ids' => 1
        ]);
    }
}