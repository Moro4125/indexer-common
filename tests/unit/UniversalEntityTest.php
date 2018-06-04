<?php

use Moro\Indexer\Common\Source\Entity\UniversalEntity;
use Moro\Indexer\Common\Source\Exception\WrongStructureException;

/**
 * Class UniversalEntityTest
 */
class UniversalEntityTest extends \PHPUnit\Framework\TestCase
{
    use Codeception\Specify;
    use Codeception\AssertThrows;

    public function testWrongStructure()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertThrows(WrongStructureException::class, function () {
            $entity = new UniversalEntity();
            $entity->load([]);
        });

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertThrows(WrongStructureException::class, function () {
            $entity = new UniversalEntity();
            $entity->load(['name' => 'universal']);
        });
    }

    public function testGetFieldsByPath()
    {
        $updatedAt = mktime(12, 42, 0, 4, 28, 2018);
        $timestamp = time();

        $entity = new UniversalEntity();
        verify(json_encode($entity))->same('[]');

        $self = $entity->load([
            'id'          => 1,
            'name'        => 'universal',
            'updated_at'  => $updatedAt,
            'active_from' => $timestamp,
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
            'author'      => '%content/authors|first%',
            'producer'    => [
                '@path' => 'content/authors|nth("2")',
            ],
            'quot\'s'     => [
                'hello, "guest"'
            ],
        ]);

        verify($self)->same($entity);

        $this->specify('Check simple fields.', function () use ($entity, $updatedAt, $timestamp) {
            verify(isset($entity['id']))->true();

            verify($entity['id'])->same(1);
            verify($entity['name'])->same('universal');
            verify($entity['updated_at'])->same($updatedAt);
            verify($entity['active_from'])->same($timestamp);
            verify($entity['active_to'])->null();

            verify($entity->getId())->same('1'); // string :-(
            verify($entity->getName())->same('universal');
            verify($entity->getUpdatedAt())->same($updatedAt);
            verify($entity->getActiveFrom())->same($timestamp);
            verify($entity->getActiveTo())->null();

            verify(isset($entity['unknown_field']))->false();
            verify($entity['unknown_field'])->null();

        });

        $this->specify('Check simple path to fields.', function () use ($entity) {
            verify(isset($entity['content/announce/lead']))->true();
            verify($entity['content/announce/lead'])->same('Hello, tester!');

            verify(isset($entity['content/announce/unknown_field']))->false();
            verify($entity['content/announce/unknown_field'])->null();

            verify(isset($entity['content/announce/lead/unknown_field']))->false();
            verify($entity['content/announce/lead/unknown_field'])->null();

            verify(isset($entity['content/tags/0']))->true();
            verify($entity['content/tags/0'])->same('tag1');

            verify(isset($entity['content/tags/1']))->true();
            verify($entity['content/tags/1'])->same('tag2');

            verify(isset($entity['content/tags/2']))->false();
            verify($entity['content/tags/2'])->null();

            verify(isset($entity['content/authors/0/name']))->true();
            verify($entity['content/authors/0/name'])->same('Alpha');
        });

        $this->specify('Check extended path with wildcard.', function () use ($entity) {
            verify(isset($entity['content/announce/l*']))->true();
            verify($entity['content/announce/l*'])->same(['Hello, tester!', true]);

            verify(isset($entity['content/authors/*/name']))->true();
            verify($entity['content/authors/*/name'])->same(['Alpha', 'Echo', 'Victor']);

            verify(isset($entity['content/authors/*/*']))->true();
            $result = ['Alpha', 'M', 'Echo', 'W', true, 25, 'Victor', 'M', 'flag', 30];
            verify($entity['content/authors/*/*'])->same($result);
        });

        $this->specify('Check extended path with condition.', function () use ($entity) {
            verify(isset($entity['content/authors/0[name="Alpha"]']))->true();
            verify($entity['content/authors/0[name="Alpha"]'])->same(['name' => 'Alpha', 'gender' => 'M']);

            verify(isset($entity['content/authors/0[name="Alpha"]/gender']))->true();
            verify($entity['content/authors/0[name="Alpha"]/gender'])->same('M');

            verify(isset($entity['content/authors/1[name="Alpha"]/gender']))->false();
            verify($entity['content/authors/1[name="Alpha"]/gender'])->null();

            verify(isset($entity['content/authors/1[name="Echo"]/gender']))->true();
            verify($entity['content/authors/1[name="Echo"]/gender'])->same('W');
        });

        $this->specify('Check extended path with condition and wildcard.', function () use ($entity) {
            verify(isset($entity['content/authors/*[name="Alpha"]']))->true();
            verify($entity['content/authors/*[name="Alpha"]'])->same([['name' => 'Alpha', 'gender' => 'M']]);

            verify(isset($entity['content/authors/*[name="Alpha"]/gender']))->true();
            verify($entity['content/authors/*[name="Alpha"]/gender'])->same(['M']);

            verify(isset($entity['content/authors/*[name="Echo"]/gender']))->true();
            verify($entity['content/authors/*[name="Echo"]/gender'])->same(['W']);

            verify(isset($entity['content/authors/*[name="November"]/gender']))->false();
            verify($entity['content/authors/*[name="November"]/gender'])->null();

            verify(isset($entity['content/authors/*[gender="M"]/name']))->true();
            verify($entity['content/authors/*[gender="M"]/name'])->same(['Alpha', 'Victor']);

            verify(isset($entity['content/authors/*["M"=gender]/name']))->true();
            verify($entity['content/authors/*["M"=gender]/name'])->same(['Alpha', 'Victor']);

            verify(isset($entity['content/authors/*[gender!="M"]/name']))->true();
            verify($entity['content/authors/*[gender!="M"]/name'])->same(['Echo']);

            verify(isset($entity['content/authors/*["M"!=gender]/name']))->true();
            verify($entity['content/authors/*["M"!=gender]/name'])->same(['Echo']);

            verify(isset($entity['content/authors/*[unique]/name']))->true();
            verify($entity['content/authors/*[unique]/name'])->same(['Echo']);

            verify(isset($entity['content/authors/*[0="flag"]/name']))->true();
            verify($entity['content/authors/*[0="flag"]/name'])->same(['Victor']);

            verify(isset($entity['content/authors/*["flag"=0]/name']))->true();
            verify($entity['content/authors/*["flag"=0]/name'])->same(['Victor']);

            verify(isset($entity['content/authors/*[*="flag"]/name']))->true();
            verify($entity['content/authors/*[*="flag"]/name'])->same(['Victor']);

            verify(isset($entity['content/authors/*["flag"=*]/name']))->true();
            verify($entity['content/authors/*["flag"=*]/name'])->same(['Victor']);

            verify(isset($entity['content/content/announce/l*[name="N"]']))->false();
            verify($entity['content/content/announce/l*[name="N"]'])->null();
        });

        $this->specify('Test not equal conditions.', function () use ($entity) {
            verify(isset($entity['content/authors/*[years>"25"]/name']))->true();
            verify($entity['content/authors/*[years>"25"]/name'])->same(['Victor']);

            verify(isset($entity['content/authors/*["25"<years]/name']))->true();
            verify($entity['content/authors/*["25"<years]/name'])->same(['Victor']);

            verify(isset($entity['content/authors/*[years<"31"]/name']))->true();
            verify($entity['content/authors/*[years<"31"]/name'])->same(['Echo', 'Victor']);

            verify(isset($entity['content/authors/*["31">years]/name']))->true();
            verify($entity['content/authors/*["31">years]/name'])->same(['Echo', 'Victor']);

            verify(isset($entity[' content /  authors  /  * [ years > "25" ]  / name ']))->true();
        });

        $this->specify('Test nested conditions.', function () use ($entity) {
            verify(isset($entity['content[authors/*[name="Echo"]]/announce/lead']))->true();
            verify($entity['content[authors/*[name="Echo"]]/announce/lead'])->same('Hello, tester!');

            verify(isset($entity['content[authors/*[name="November"]]/announce/lead']))->false();
            verify($entity['content[authors/*[name="November"]]/announce/lead'])->null();

            verify(isset($entity['content[authors/*[name="Alpha"]][authors/*[name="Echo"]]/announce/lock']))->true();
            verify($entity['content[authors/*[name="Alpha"]][authors/*[name="Echo"]]/announce/lock'])->true();

            verify(isset($entity['content[authors/*[name="Alpha"]][authors/*[name="November"]]/announce/lock']))->false();
            verify($entity['content[authors/*[name="Alpha"]][authors/*[name="November"]]/announce/lock'])->null();

            verify(isset($entity['content[authors/*[name="Echo"]]/tags[*="tag2"]/0']))->true();
            verify($entity['content[authors/*[name="Echo"]]/tags[*="tag2"]/0'])->same('tag1');

            verify(isset($entity['content[authors/*[name="Echo"]]/tags[*="tag3"]/0']))->false();
            verify($entity['content[authors/*[name="Echo"]]/tags[*="tag3"]/0'])->null();

            verify(isset($entity['[content/authors/*[name="Echo"]]/content/announce/lead']))->true();
            verify($entity['[content/authors/*[name="Echo"]]/content/announce/lead'])->same('Hello, tester!');

            verify(isset($entity['[content/authors/*[name="Echo"]]']))->true();
            verify(isset($entity['[content/authors/*[name="November"]]']))->false();
        });

        $this->specify('Test root paths', function () use ($entity) {
            verify(isset($entity['/content/authors/*[name=/white-list/*]/years']))->true();
            verify($entity['/content/authors/*[name=/white-list/*]/years'])->same([30]);
        });

        $this->specify('Test filters', function () use ($entity) {
            verify(isset($entity['content/tags/*|count']))->true();
            verify($entity['content/tags/*|count'])->same(2);

            verify(isset($entity['content/tags|count']))->true();
            verify($entity['content/tags|count'])->same(2);

            verify(isset($entity['content/tags/*|first']))->true();
            verify($entity['content/tags/*|first'])->same('tag1');

            verify(isset($entity['content/tags|first']))->true();
            verify($entity['content/tags|first'])->same('tag1');

            verify(isset($entity['content/tags/*|last']))->true();
            verify($entity['content/tags/*|last'])->same('tag2');

            verify(isset($entity['content/tags|last']))->true();
            verify($entity['content/tags|last'])->same('tag2');

            verify(isset($entity['content/authors/*/name|count']))->true();
            verify($entity['content/authors/*/name|count'])->same(3);

            verify(isset($entity['empty-list|count']))->true();
            verify($entity['empty-list|count'])->same(0);

            verify(isset($entity['empty-list|first']))->false();
            verify($entity['empty-list|first'])->null();

            verify(isset($entity['empty-list|last']))->false();
            verify($entity['empty-list|last'])->null();

            verify(isset($entity['content/authors/*/name|nth("2")']))->true();
            verify($entity['content/authors/*/name|nth("2")'])->same('Echo');

            verify(isset($entity['content/authors/*/name|nth("20")']))->false();
            verify($entity['content/authors/*/name|nth("20")'])->null();

            verify(isset($entity['content/authors/*/name|nth("0")']))->false();
            verify($entity['content/authors/*/name|nth("0")'])->null();

            verify(isset($entity['content/authors/*/gender|unique']))->true();
            verify($entity['content/authors/*/gender|unique'])->same(['M', 'W']);

            verify(isset($entity['content/authors/*/name|intersect(white-list/*)']))->true();
            verify($entity['content/authors/*/name|intersect(white-list/*)'])->same(['Victor']);

            verify(isset($entity['content/authors/*[gender="W"]/name|intersect(white-list/*)']))->false();
            verify($entity['content/authors/*[gender="W"]/name|intersect(white-list/*)'])->null();

            verify(isset($entity['content/authors/*/name|different(white-list/*)']))->true();
            verify($entity['content/authors/*/name|different(white-list/*)'])->same(['Alpha', 'Echo']);

            verify(isset($entity['content/authors/*[gender=\'W\']/name|merge(white-list/*)']))->true();
            verify($entity['content/authors/*[gender=\'W\']/name|merge(white-list/*)'])->same(['Echo', 'Victor']);

            verify(isset($entity['content/authors/*|diff(content/authors/*[years])']))->true();
            $result = [['name' => 'Alpha', 'gender' => 'M']];
            verify($entity['content/authors/*|diff(content/authors/*[years])'])->same($result);

            verify(isset($entity['content/authors/*|diff(content/authors/*[years])|path(name)']))->true();
            verify($entity['content/authors/*|diff(content/authors/*[years])|path(name)'])->same(['Alpha']);

            verify(isset($entity['content/authors/*|path(*)']))->true();
            $result = ['Alpha', 'M', 'Echo', 'W', true, 25, 'Victor', 'M', 'flag', 30];
            verify($entity['content/authors/*|path(*)'])->same($result);
        });

        $this->specify('Test filters (for not exists paths)', function () use ($entity) {
            verify(isset($entity['content/unknown/*|count']))->true();
            verify($entity['content/unknown/*|count'])->same(0);

            verify(isset($entity['white-list/0|count']))->true();
            verify($entity['white-list/0|count'])->same(1);

            verify(isset($entity['white-list/0/a|count']))->true();
            verify($entity['white-list/0/a|count'])->same(0);
        });

        $this->specify('Test _replaceNode', function () use ($entity) {
            verify(isset($entity['author']))->true();
            verify($entity['author'])->same(['name' => 'Alpha', 'gender' => 'M']);

            verify(isset($entity['author/name']))->true();
            verify($entity['author/name'])->same('Alpha');

            verify(isset($entity['producer']))->true();
            verify($entity['producer'])->same(['name' => 'Echo', 'gender' => 'W', 'unique' => true, 'years' => 25]);

            verify(isset($entity['producer/name']))->true();
            verify($entity['producer/name'])->same('Echo');
        });

        $this->specify('Quot\'s', function () use ($entity) {
            verify(isset($entity['quot\\\'s[*="hello, \\"guest\\""]']))->true();
            verify($entity['quot\\\'s[*="hello, \\"guest\\""]'])->same(['hello, "guest"']);
        });

        $this->specify('Test filter "keys"', function () use ($entity) {
            verify(isset($entity['content/authors/*[name="Echo"]|first|keys']))->true();
            verify($entity['content/authors/*[name="Echo"]|first|keys'])->same(['name', 'gender', 'unique', 'years']);

            verify(isset($entity['producer|keys']))->true();
            verify($entity['producer|keys'])->same(['name', 'gender', 'unique', 'years']);
        });

        $this->specify('Test filter "join"', function()use ($entity) {
            verify(isset($entity['content/authors/*/name|join(",")']))->true();
            verify($entity['content/authors/*/name|join(",")'])->same('Alpha,Echo,Victor');
        });

        $this->specify('Test filter "now"', function()use ($entity) {
            verify(isset($entity['"now"|timestamp']))->true();
            verify($entity['"now"|timestamp'])->internalType('int');
            verify($entity['"now"|timestamp'])->lessOrEquals(time());
            verify($entity['"now"|timestamp'])->greaterThan(time() - 5);
        });

        $this->specify('Test filter "is_*"', function()use ($entity) {
            verify(isset($entity['id|is_int']))->true();
            verify($entity['id|is_int'])->same(true);

            verify(isset($entity['id|is_string']))->true();
            verify($entity['id|is_string'])->same(false);

            verify(isset($entity['[id|is_int]']))->true();
            verify(isset($entity['[id|is_string]']))->false();
        });
    }

    public function testDeniedMethods()
    {
        $entity = new UniversalEntity();
        $entity->load(['id' => 1, 'name' => 'universal']);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertThrows(RuntimeException::class, function () use ($entity) {
            $entity->offsetSet('id', 2);
        });

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertThrows(RuntimeException::class, function () use ($entity) {
            $entity->offsetUnset('id');
        });
    }
}