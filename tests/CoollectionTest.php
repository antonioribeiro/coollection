<?php

namespace PragmaRX\Coollection\Tests;

use ReflectionClass;
use JsonSerializable;
use BadMethodCallException;
use PragmaRX\Coollection\Package\Coollection;
use PragmaRX\Coollection\Tests\Support\Dummy;
use IlluminateAgnostic\Collection\Contracts\Support\Jsonable;
use IlluminateAgnostic\Collection\Contracts\Support\Arrayable;
use IlluminateAgnostic\Collection\Support\Collection as IlluminateExtractedCollection;

class CoollectionTest extends \PHPUnit\Framework\TestCase
{
    const DATA = [
        [
            'first_name' => 'Antonio Carlos',
            'last_name' => 'Ribeiro',
            'address' => [
                'city' => 'Rio de Janeiro',
                'street' => 'Praia de Copacabana',
            ],
            'other_address' => [
                'city' => 'New York',
                'street' => '5th Avenue',
            ],
            'skills' => [
                'php' => [
                    '5.6', '7.0', '7.1', '7.2'
                ],
                'laravel' => [
                    '3.2', '4.0', '4.2', '5.0', '5.1', '5.2', '5.3', '5.4', '5.5',
                ],
                'photography' => [
                    'pro' => true,
                    'landscape' => true,
                    'concerts' => true,
                    'wedding' => true,
                    'studio' => true,
                    'nature' => false,
                ],
            ],
            'grades' => [
                10, '9', 9, 10, 8, 6, 5
            ],
            'ages' => [
                1, 2, 3, 4, 5, 6, 7, 8, 9, 39
            ],
            'position' => 30,
            'both_have' => true,
            'spread' => [['John Doe', 35], ['Jane Doe', 33]],
            'accounting' => [
                ['account_id' => 'account-x10', 'product' => 'Chair'],
                ['account_id' => 'account-x10', 'product' => 'Bookcase'],
                ['account_id' => 'account-x11', 'product' => 'Desk'],
            ],
        ],

        [
            'first_name' => 'Mary',
            'last_name' => 'Blood',
            'address' => [
                'city' => 'Aruba'
            ],
            'skills' => [
                'cinema' => [
                    '1976'
                ],
            ],
            'grades' => [
                10, 10, 10, 1, 2, 3, 4, 5, 4, 4, 4, 4, 4, 4, 4, 4
            ],
            'ages' => [
                20, 21, 22, 23, 24, 25
            ],
            'position' => 40,
            'both_have' => true,
            'spread' => [['John Doe', 37], ['Jane Doe', 34]],
        ]
    ];

    const ACCOUNTING_GROUPED = [
        'account-x10' => [
            ['account_id' => 'account-x10', 'product' => 'Chair'],
            ['account_id' => 'account-x10', 'product' => 'Bookcase'],
        ],
        'account-x11' => [
            ['account_id' => 'account-x11', 'product' => 'Desk'],
        ],
    ];

    /**
     * @var Coollection
     */
    private $coollection;

    /**
     * @var Coollection
     */
    private $full;

    /**
     * @var array
     */
    private $array;

    /**
     *
     */
    public function setUp()
    {
        $this->full = coollect(static::DATA);

        $this->coollection = $this->full->where('last_name', 'Ribeiro')->first();

        $this->array =  $this->coollection->toArray();
    }

    public function testData()
    {
        $this->assertEquals($this->array, static::DATA[0]);
    }

    public function wrapIfArrayable()
    {
        $this->assertInstanceOf(Coollection::class, $this->coollection->get('skills'));

        $this->assertInstanceOf(Coollection::class, $this->coollection->get('skills')->photography);
    }

    public function testInstantiation()
    {
        $this->assertInstanceOf(Coollection::class, $this->coollection);
    }

    public function testObjectifiedProperties()
    {
        $this->assertEquals($this->coollection->first_name, $this->array['first_name']);

        $this->assertEquals($this->coollection->last_name, $this->array['last_name']);

        $this->assertEquals($this->coollection->address->city, $this->array['address']['city']);
    }

    public function testWrap()
    {
        $c = $this->coollection->wrap($this->array);

        $this->assertEquals($c->wrap($c)->address->city, $this->array['address']['city']);
    }

    public function testUnwrap()
    {
        $c = $this->coollection->wrap(static::DATA);

        $this->assertEquals($c->unwrap($c), static::DATA);
    }

    public function testMake()
    {
        $this->assertInstanceOf(Coollection::class, $c = $this->coollection->make($this->array));

        $this->assertEquals($c->address->city, $this->array['address']['city']);
    }

    public function testTimes()
    {
        $c = $this->coollection->times(2);

        $this->assertEquals($c->all()->toArray(), [1, 2]);

        $this->assertInstanceOf(Coollection::class, $c->map(function ($item) {
            return $item;
        }));
    }

    public function testAll()
    {
        $this->assertEquals($this->coollection->all()->toArray(), $this->array);
    }

    public function testAvg()
    {
        $this->assertEquals((int) $this->coollection->grades->avg(), 8);
    }

    public function testMedian()
    {
        $this->assertEquals(9, $this->coollection->grades->median());
    }

    public function testMode()
    {
        $this->assertEquals([10, 9], $this->coollection->grades->mode()->flatten()->toArray());
    }

    public function testCollapse()
    {
        $this->assertEquals($this->coollection->skills->collapse()->toArray(), array_merge($this->array['skills']['php'], $this->array['skills']['laravel'], $this->array['skills']['photography']));
    }

    public function testContains()
    {
        $this->assertTrue($this->coollection->skills->contains($this->array['skills']['php']));

        $this->assertFalse($this->coollection->skills->contains('rails'));

        $this->assertTrue($this->coollection->grades->contains(10));

        $this->assertTrue($this->coollection->grades->contains('10'));
    }

    public function testContainsStrict()
    {
        $this->assertTrue($this->coollection->grades->containsStrict(10));

        $this->assertFalse($this->coollection->grades->containsStrict('10'));
    }

    public function testCrossJoin()
    {
        $this->assertInstanceOf(Coollection::class, $this->coollection->skills->crossJoin($this->coollection->grades));
    }

    public function testDiff()
    {
        $this->assertInstanceOf(Coollection::class, $this->coollection->grades->diff($this->coollection->ages));
    }

    public function testDiffAssoc()
    {
        $this->assertInstanceOf(Coollection::class, $this->coollection->diffAssoc($this->coollection->skills));
    }

    public function testDiffKeys()
    {
        $this->assertInstanceOf(Coollection::class, $this->coollection->diffKeys($this->coollection->skills));
    }

    public function testEach()
    {
        $this->assertInstanceOf(Coollection::class, $this->coollection->skills->each(function ($item) {
            $this->assertInstanceOf(Coollection::class, $item);

            return $item;
        }));
    }

    public function testEachSpread()
    {
        $this->assertInstanceOf(Coollection::class, $this->coollection->spread->eachSpread(function ($field, $name) {
            return true;
        }));
    }

    public function testEvery()
    {
        $this->assertTrue($this->coollection->skills->every(function () {
            return true;
        }));
    }

    public function testExcept()
    {
        $compare = $this->array['skills'];

        unset($compare['php']);

        $this->assertEquals($this->coollection->skills->except(['php'])->toArray(), $compare);
    }

    public function testFilter()
    {
        $compare = $this->coollection->skills->filter(function ($item, $key) {
            $this->assertInstanceOf(Coollection::class, $item);

            return $key != 'php';
        });

        $this->assertFalse($compare->has('php'));

        $this->assertTrue($compare->has('laravel'));
    }

    public function testWhen()
    {
        $this->assertEquals($this->coollection->count(), 11);

        $this->coollection->skills->when(true, function () {
            return $this->coollection->push('void');
        });

        $this->assertEquals($this->coollection->count(), 12);
    }

    public function testUnless()
    {
        $this->assertEquals($this->coollection->count(), 11);

        $this->coollection->skills->unless(false, function () {
            return $this->coollection->push('void');
        });

        $this->coollection->skills->unless(true, function () {
            return $this->coollection->push('void');
        });

        $this->assertEquals($this->coollection->count(), 12);
    }

    public function testWhere()
    {
        $this->assertEquals(
            $this->full->where('last_name', 'Ribeiro')->first()->skills->laravel->toArray(),
            $this->array['skills']['laravel']
        );
    }

    public function testWhereStrict()
    {
        $this->assertTrue(
            $this->full->whereStrict('position', '30')->count() == 0
        );

        $this->assertEquals(
            $this->full->whereStrict('position', 30)->first()->last_name,
            $this->array['last_name']
        );
    }

    public function testWhereIn()
    {
        $this->assertEquals(
            $this->full->whereIn('last_name', ['Ribeiro'])->first()->last_name,
            $this->array['last_name']
        );
    }

    public function testWhereInStrict()
    {
        $this->assertTrue(
            $this->full->whereInStrict('position', ['30'])->count() == 0
        );

        $this->assertEquals(
            $this->full->whereInStrict('position', [30])->first()->last_name,
            $this->array['last_name']
        );
    }

    public function testWhereNotIn()
    {
        $this->assertEquals(
            $this->full->whereNotIn('last_name', ['Blood'])->first()->last_name,
            $this->array['last_name']
        );
    }

    public function testWhereNotInStrict()
    {
        $this->assertTrue(
            $this->full->whereNotInStrict('both_have', [true])->count() == 0
        );

        $this->assertEquals(
            $this->full->whereNotInStrict('position', [40])->first()->last_name,
            $this->array['last_name']
        );
    }

    public function testFirst()
    {
        $this->assertEquals(
            $this->full->first()->first_name,
            $this->array['first_name']
        );
    }

    public function testFlaten()
    {
        $this->assertGreaterThan(
            0,
            $this->coollection->skills->php->flatten()->search('7.2')
        );

        $this->assertFalse(
            $this->coollection->skills->php->flatten()->search('7.3')
        );
    }

    public function testFlip()
    {
        $this->assertEquals($this->coollection->address->flip()->rio_de_janeiro, 'city');
        $this->assertEquals($this->coollection->address->flip()->rio_de_janeiro, 'city');

        $collection = new Coollection([$column = 'first_name' => $name = 'Barak Obama']);

        $this->assertEquals($collection->first_name, $name);

        $this->assertEquals($collection->flip()->barak_obama, $column);
    }

    public function testForget()
    {
        Coollection::setRaiseExceptionOnNull(false);

        $this->assertNotNull($this->coollection->address->city);

        $this->assertNull($this->coollection->address->forget('city')->city);
    }

    public function testGet()
    {
        $this->assertEquals($this->coollection->address->get('city'), $this->array['address']['city']);
    }

    public function testGetException()
    {
        Coollection::setRaiseExceptionOnNull(true);

        $this->expectException(\Exception::class);

        $c = new Coollection(['a' => '1', 'b' => 2]);

        $c->inexistentPropertyOrItem;
    }

    public function testGetNoExceptionOnNull()
    {
        Coollection::setRaiseExceptionOnNull(false);

        $c = new Coollection(['a' => '1', 'b' => 2]);

        $this->assertNull($c->allowedItems);
    }

    public function testGroupBy()
    {
        $this->assertInstanceOf(Coollection::class, $c = $this->coollection->accounting->groupBy('account_id'));

        $this->assertEquals($c->toArray(), static::ACCOUNTING_GROUPED);
    }

    public function testKeyBy()
    {
        $this->assertEquals(
            $this->coollection->accounting->keyBy('account_id')->toArray(),
            (new IlluminateExtractedCollection($this->coollection->accounting))->keyBy('account_id')->toArray()
        );

        $this->assertInstanceOf(Coollection::class, $this->coollection->accounting->keyBy('account_id'));
    }

    public function testHas()
    {
        $this->assertTrue($this->coollection->address->has('city'));
    }

    public function testImplode()
    {
        $this->assertEquals($this->coollection->accounting->implode('product', '-'), "Chair-Bookcase-Desk");
    }

    public function testIntersect()
    {
        $values = $this->coollection->accounting->pluck('product')->values();

        $tco = $values->toArray();

        $this->assertEquals(
            $values->intersect($tco)->toArray(),
            $tco
        );

        $this->assertInstanceOf(Coollection::class, $values->intersect($tco));
    }

    public function testIntersectByKeys()
    {
        $values = $this->coollection->accounting->pluck('product')->values();

        $tco = $values->toArray();

        $this->assertEquals(
            $values->intersectByKeys($tco)->toArray(),
            $tco
        );

        $this->assertInstanceOf(Coollection::class, $values->intersect($tco));
    }

    public function testEmptyNotEmpty()
    {
        $this->assertFalse(
            $this->coollection->address->isEmpty()
        );

        $this->assertTrue($this->coollection->address->isNotEmpty());

        $this->assertFalse(
            $this->coollection->address->forget('city')->isEmpty()
        );

        $this->assertTrue(
            $this->coollection->address->forget('city')->forget('street')->isEmpty()
        );

        $this->assertFalse(
            $this->coollection->address->forget('city')->forget('street')->isNotEmpty()
        );
    }

    public function testKeys()
    {
        $this->assertEquals($this->coollection->skills->keys()->toArray(), ['php', 'laravel', 'photography']);

        $this->assertInstanceOf(Coollection::class, $this->coollection->skills->keys());
    }

    public function testLast()
    {
        $this->assertEquals('Praia de Copacabana', $this->coollection->address->last());

        $this->assertEquals(false, $this->coollection->skills->last()->nature);

        $this->assertEquals(true, $this->coollection->skills->last()->landscape);

        $this->assertInstanceOf(Coollection::class, $this->coollection->skills->last());
    }

    public function testPluck()
    {
        $this->assertEquals(
            ["Chair", "Bookcase", "Desk"],
            $this->coollection->accounting->pluck('product')->toArray()
        );
    }

    public function testMap()
    {
        $c = $this->coollection->skills->map(function ($item) {
            $this->assertInstanceOf(Coollection::class, $item);

            return 'mapped';
        })->flatten()->values();

        $this->assertEquals(['mapped', 'mapped', 'mapped'], $c->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testMapSpread()
    {
        $collection = coollect([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $chunks = $collection->chunk(2);

        $sequence = $chunks->mapSpread(function ($odd, $even) {
            return $odd + $even;
        });

        $sequence->all();

        $this->assertEquals([1, 5, 9, 13, 17], $sequence->all()->toArray());

        $this->assertInstanceOf(Coollection::class, $sequence);
    }

//    public function testMapToDictionary() // TODO --- broken
//    {
//        $c = collect($this->full[0]['skills'])->mapToDictionary(function ($item) {
//            return $item;
//        });
//
//        $o = $this->coollection->skills->mapToDictionary(function ($item) {
//            $this->assertInstanceOf(Coollection::class, $item);
//
//            return $item;
//        });
//
//        $this->assertEquals(
//            $c->flatten()->values()->toArray(),
//            $o->flatten()->values()->toArray()
//        );
//
//        $this->assertInstanceOf(Coollection::class, $c);
//    }

    public function testMapToGroups()
    {
        $c = $this->coollection->accounting->mapToGroups(function ($item) {
            return [$item['account_id'] => $item['product']];
        });

        $this->assertEquals(
            [
                "account-x10" => [
                    "Chair",
                    "Bookcase",
                ],
                "account-x11" => [
                    "Desk",
                ],
            ],
            $c->toArray()
        );

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testMapWithKeys()
    {
        $c = $this->coollection->accounting->mapWithKeys(function ($item) {
            return [$item['product'] => $item['product']];
        });

        $this->assertEquals(
            ['Chair', 'Bookcase', 'Desk'],
            $c->flatten()->values()->toArray()
        );

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testMapWithKeysChangeKey()
    {
        $data = new Coollection([
            'id1' => 1,
            'id2' => 2,
            'id3' => 3,
        ]);
        $data = $data->mapWithKeys(function ($item, $key) {
            return ["{$key}_changed" => $item];
        });
        $this->assertSame(
            [
                'id1_changed' => 1,
                'id2_changed' => 2,
                'id3_changed' => 3,
            ],
            $data->toArray()
        );
    }

    public function testFlatMap()
    {
        $c = $this->coollection->accounting->flatMap(function ($item) {
            return [$item['product'] => $item['product']];
        });

        $this->assertEquals(
            ['Chair', 'Bookcase', 'Desk'],
            $c->flatten()->values()->toArray()
        );

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testMapInto()
    {
        $c = $this->coollection->accounting->mapInto(Dummy::class);

        $this->assertInstanceOf(Dummy::class, $c->first());

        $this->assertNotInstanceOf(Coollection::class, $c->first());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testMax()
    {
        $this->assertEquals(39, $this->coollection->ages->max());
    }

    public function testMerge()
    {
        $result = [
            0 => "5.6",
            1 => "7.0",
            2 => "7.1",
            3 => "7.2",
            "pro" => true,
            "landscape" => true,
            "concerts" => true,
            "wedding" => true,
            "studio" => true,
            "nature" => false,
        ];

        $c = $this->coollection->skills->php->merge($this->coollection->skills->photography);

        $this->assertEquals($result, $c->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testCombine()
    {
        $result = [
            'John Doe' => 'Jane Doe',
            35 => 33
        ];

        $c = $this->coollection->spread->first()->combine($this->coollection->spread->last());

        $this->assertEquals($result, $c->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testUnion()
    {
        $result = [
            '5.6', '7.0', '7.1', '7.2', '5.1', '5.2', '5.3', '5.4', '5.5',
        ];

        $c = $this->coollection->skills->php->union($this->coollection->skills->laravel);

        $this->assertEquals($result, $c->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testMin()
    {
        $this->assertEquals(1, $this->coollection->ages->min());
    }

    public function testNth()
    {
        $collection = coollect(['a', 'b', 'c', 'd', 'e', 'f']);

        $this->assertEquals(['a', 'e'], $collection->nth(4)->toArray());

        $this->assertInstanceOf(Coollection::class, $collection->nth(4));
    }

    public function testOnly()
    {
        $c = $this->coollection->skills->only('php')->php;

        $this->assertEquals($this->array['skills']['php'], $c->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testForPage()
    {
        $c = $this->coollection->skills->laravel->forPage(2, 2);

        $this->assertEquals(['4.2', '5.0'], $c->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testPartition()
    {
        list($belowFive, $aboveFive) = $this->coollection->skills->laravel->partition(function ($i) {
            return $i < '4.9';
        });

        $this->assertEquals(['3.2', '4.0', '4.2'], $belowFive->flatten()->toArray());

        $this->assertEquals(['5.0', '5.1', '5.2', '5.3', '5.4', '5.5'], $aboveFive->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $belowFive);

        $this->assertInstanceOf(Coollection::class, $aboveFive);
    }

    public function testPipe()
    {
        $this->assertEquals('piped', $this->coollection->skills->laravel->pipe(function () {
            return 'piped';
        }));
    }

    public function testPop()
    {
        $this->assertEquals('5.5', $this->coollection->skills->laravel->pop());
    }

    public function testPrepend()
    {
        $c = $this->coollection->skills->laravel->prepend(['bash' => 'all']);

        $this->assertEquals('all', $c->first()->flatten()->toArray()[0]);

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testPush()
    {
        $c = $this->coollection->skills->laravel->push('all');

        $this->assertEquals('3.2', $c->first());

        $this->assertEquals('all', $c->last());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testConcat()
    {
        $c = $this->coollection->address->values()->concat($this->coollection->other_address->values());

        $concat = [
            'Rio de Janeiro',
            'Praia de Copacabana',
            'New York',
            '5th Avenue',
        ];

        $this->assertEquals($concat, $c->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testPull()
    {
        $laravel = $this->coollection->skills->pull('laravel');

        $pulled = [
            '3.2', '4.0', '4.2', '5.0', '5.1', '5.2', '5.3', '5.4', '5.5',
        ];

        $this->assertEquals($laravel->toArray(), $pulled);

        $this->assertFalse($this->coollection->has('laravel'));

        $this->assertInstanceOf(Coollection::class, $this->coollection);
    }

    public function testPut()
    {
        $c = $this->coollection->skills->put('bash', ['all']);

        $this->assertEquals('all', $c->last()->values()[0]);

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testRandom()
    {
        $c = $this->coollection->skills->random();

        $this->assertTrue(in_array($c->first(), ['5.6', '3.2', true], true));

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testReduce()
    {
        $return = $this->coollection->skills->reduce(function ($carry, $value) {
            $this->assertInstanceOf(Coollection::class, $value);
            return $carry + 100 + 1;
        }, 100);

        $this->assertEquals($return, 403);
    }

    public function testReject()
    {
        $c = $this->coollection->skills->reject(function ($skill, $index) {
            return $index == 'php';
        });

        $this->assertEquals(['laravel', 'photography'], $c->keys()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testReverse()
    {
        $c = $this->coollection->skills->reverse();

        $this->assertEquals(['photography', 'laravel', 'php'], $c->keys()->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testSearch()
    {
        $this->assertEquals(2, $this->coollection->skills->laravel->search('4.2', true));
    }

    public function testShift()
    {
        $c = $this->coollection->skills->php;

        $php = $c->shift();

        $this->assertEquals('5.6', $php);

        $this->assertInstanceOf(Coollection::class, $c);


        $c = $this->coollection->skills;

        $php = $c->shift();

        $this->assertEquals('5.6', $php->first());

        $this->assertInstanceOf(Coollection::class, $php);
    }

    public function testShuffle()
    {
        $this->assertInstanceOf(Coollection::class, $this->coollection->skills->shuffle());
    }

    public function testSlice()
    {
        $c = $this->coollection->skills->laravel->slice(2, 2);

        $this->assertEquals(['4.2', '5.0'], $c->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testSplit()
    {
        $c = $this->coollection->skills->laravel->split(2);

        $this->assertEquals(['3.2', '4.0', '4.2', '5.0', '5.1'], $c[0]->flatten()->toArray());

        $this->assertEquals(['5.2', '5.3', '5.4', '5.5'], $c[1]->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testChunk()
    {
        $c = $this->coollection->skills->laravel->chunk(5);

        $this->assertEquals(['3.2', '4.0', '4.2', '5.0', '5.1'], $c[0]->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testSort()
    {
        $c = $this->coollection->grades->sort();

        $this->assertEquals([5,6,8,9,9,10,10], $c->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testSortBy()
    {
        $this->assertEquals(['Chair', 'Bookcase', 'Desk'], $this->coollection->accounting->pluck('product')->toArray());

        $c = $this->coollection->accounting->sortBy('product')->pluck('product');

        $this->assertEquals(['Bookcase', 'Chair', 'Desk'], $c->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testSortByDesc()
    {
        $c = $this->coollection->accounting->sortByDesc('product')->pluck('product');

        $this->assertEquals(['Desk', 'Chair', 'Bookcase'], $c->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testSplice()
    {
        $c = $this->coollection->skills->laravel->splice(5);

        $this->assertEquals(['5.2', '5.3', '5.4', '5.5'], $c->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testSum()
    {
        $this->assertEquals($this->coollection->grades->sum(), 57);
    }

    public function testTake()
    {
        $c = $this->coollection->skills->laravel->take(3);

        $this->assertEquals(['3.2', '4.0', '4.2'], $c->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testTap()
    {
        $c = $this->coollection->skills->laravel->tap(function ($collection) {
            return $collection;
        });

        $this->assertEquals($this->array['skills']['laravel'], $c->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testTransform()
    {
        $c = $this->coollection->skills->laravel->transform(function ($collection) {
            return $collection;
        });

        $this->assertEquals($c->toArray(), $c->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testUnique()
    {
        $c = $this->coollection->skills->photography->unique();

        $this->assertEquals(2, $c->count());

        $c = $this->coollection->skills->laravel->unique();

        $this->assertEquals(9, $c->count());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testUniqueStrict()
    {
        $c = $this->coollection->grades->unique();

        $this->assertEquals(5, $c->count());

        $c = $this->coollection->grades->uniqueStrict();

        $this->assertEquals(6, $c->count());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testValueRetriever()
    {
        $c = $this->coollection->grades->unique(function($v1, $v2) {
            return $v1 !== $v2;
        });

        $this->assertEquals(1, $c->count());
    }

    public function testUniqueValues()
    {
        $this->testIntersect();
    }

    public function testZip()
    {
        $this->assertEquals(
            [
                ["Rio de Janeiro", "New York"],
                ["Praia de Copacabana", "5th Avenue"]
            ],
            $this->coollection->address->zip($this->coollection->other_address)->toArray()
        );
    }

    public function testPad()
    {
        $c = $this->coollection->skills->laravel->take(1)->pad(2, '6.0');

        $this->assertEquals(['3.2', '6.0'], $c->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testJsonSerialize()
    {
        $this->assertEquals(json_encode(["5.6","7.0","7.1","7.2"]), json_encode($this->coollection->skills->php->jsonSerialize()));
    }

    public function testToJson()
    {
        $this->assertEquals('["5.6","7.0","7.1","7.2"]', $this->coollection->skills->php->toJson());
    }

    public function testToArray()
    {
        $this->assertEquals(["5.6","7.0","7.1","7.2"], $this->coollection->skills->php->toArray());
    }

    public function testToCannotCreateCoolectionOfCoolection()
    {
        $a = ['a' => 2, 'b' => 3];

        $this->assertEquals($a, (new Coollection(new Coollection($a)))->toArray());
    }

    public function testOverwrite()
    {
        $a = [
            'a' => 2,
            'b' => 3,
            'c' => [
                'd' => 4,
            ],
            'e' => [
                'f' => [
                    'g' => 5,
                ],
            ],
        ];

        $b = [
            'c' => [
                'd' => 6,
                'h' => 7,
                'i' => [
                    'j' => 8,
                ],
            ],
            'e' => [
                'f' => [
                    'g' => [
                        'k' => 9
                    ],
                ],
            ],
        ];

        $c = [
            'a' => 2,
            'b' => 3,
            'c' => [
                'd' => 6,
                'h' => 7,
                'i' => [
                    'j' => 8,
                ],
            ],
            'e' => [
                'f' => [
                    'g' => [
                        'k' => 9
                    ],
                ],
            ],
        ];

        $this->assertEquals($c, coollect($a)->overwrite($b)->toArray());
    }

    public function testCanGetPropertyWithAnyCase()
    {
        $currency = coollect([
            'BR' => [ 'symbol' => 'R$' ]
        ]);

        $this->assertEquals('R$', $currency->br->symbol);

        $this->assertEquals('R$', $currency->BR->symbol);
    }

    /**
     * Different from Laravel
     */
    public function testCanGetDottedProperties()
    {
        $currency = coollect([
            'BR' => [ 'symbol' => 'R$' ]
        ]);

        $this->assertEquals('R$', $currency->br->symbol);

        $this->assertEquals('R$', $currency->get('BR.symbol'));
    }

    /**
     * Different from Laravel
     */
    public function testHelpers()
    {
        $this->assertEquals('laravel_framework', with('laravel_framework'));

        $this->assertEquals('laravel_framework', snake('LaravelFramework'));

        $this->assertEquals('LARAVEL FRAMEWORK É A MELHOR', upper('laravel framework é a melhor'));

        $this->assertEquals('laravel framework é a melhor', lower('LARAVEL FRAMEWORK É A MELHOR'));

        $this->assertEquals(true, starts_with('laravel framework', 'l'));

        $this->assertEquals(false, starts_with('Laravel framework', 'l'));

        $c = (new Coollection($array = ['laravel framework']));

        $this->assertEquals($c->first(), coollect($c->toArray())->first());

        $this->assertEquals($c->first(), coollect($c)->first());
    }

    public function testMacro()
    {
        Coollection::macro('testMacro', function () {
            return 'macro is working';
        });

        $this->assertEquals('macro is working', Coollection::testMacro('is it?'));
        $this->assertEquals('macro is working', coollect()->testMacro('is it?'));

        Coollection::macro('testMacroLower', 'lower');

        $this->assertEquals('macro is lower', Coollection::testMacroLower('MACRO IS LOWER'));
        $this->assertEquals('macro is lower', coollect()->testMacroLower('MACRO IS LOWER'));

        Coollection::mixin($mixin = new class() {
            public function testMacroUpper()
            {
                return function() {
                    return 'MACRO IS UPPER';
                };
            }
        });

        $this->assertEquals('MACRO IS UPPER', coollect()->testMacroUpper('macro is upper'));

        $this->expectException(BadMethodCallException::class);

        $this->assertEquals('macro is lower', Coollection::testMacroDoesNotExists());
    }

    public function testCollectionExcept()
    {
        $this->assertSame(['a', 'c'], collect(['a', 'b', 'c'])->except(1)->values()->toArray());

        $this->assertSame(['a', 'c'], collect(['a', 'b', 'c'])->except(collect([1]))->values()->toArray());
    }

    public function testIsEmpty()
    {
        $this->assertFalse(coollect([1,2,3,4])->isEmpty());

        $this->assertTrue(coollect([])->isEmpty());
    }

    public function testIsCount()
    {
        $this->assertEquals(4, coollect([1,2,3,4])->count());

        $this->assertEquals(0, coollect([])->count());
    }

    public function testMagicCall()
    {
        $c = new Coollection([
            ['a' => '1', 'b' => 2],
            ['a' => '2', 'b' => 3],
            ['a' => '3', 'b' => 4],
            ['a' => '5', 'b' => 6],
        ]);

        $this->assertEquals(4, $c->count());

        $this->assertEquals(4, $c->pluck('b')->count());
    }

    public function testNonArrayable()
    {
        $this->assertEquals($string = 'not an array', $this->coollection->__toArray($string));
    }

    public function testArrayAccess()
    {
        $c = new Coollection(['a' => '1', 'b' => 2]);

        $this->assertFalse(empty($c));

        $this->assertTrue(isset($c['a']));

        $this->assertTrue($c['a'] === '1');

        $c['a'] = '2';

        $this->assertTrue($c['a'] === '2');

        $this->assertEquals(2, $c->count());

        $c[] = '2';

        $this->assertEquals(3, $c->count());

        unset($c['a']);

        $this->assertEquals(2, $c->count());
    }

    public function testHighOrder()
    {
        $person1 = (object) ['name' => 'Taylor'];
        $person2 = (object) ['name' => 'Yaz'];

        $collection = coollect([$person1, $person2]);

        $this->assertEquals(['Taylor', 'Yaz'], $collection->map->name->toArray());

        $collection = coollect([new TestSupportCollectionHigherOrderItem, new TestSupportCollectionHigherOrderItem]);

        $this->assertEquals(['TAYLOR', 'TAYLOR'], $collection->each->uppercase()->map->name->toArray());
    }

    public function testAliases()
    {
        require __DIR__.'/../src/package/Support/alias.php';

        $this->assertTrue(!false);
    }

    public function testGetArrayableItems()
    {
        $coollection = new Coollection;

        $class = new ReflectionClass($coollection);
        $method = $class->getMethod('getArrayableItems');
        $method->setAccessible(true);

        $items = new TestArrayableObject;
        $array = $method->invokeArgs($coollection, [$items]);
        $this->assertSame(['foo' => 'bar'], $array);

        $items = new TestJsonableObject;
        $array = $method->invokeArgs($coollection, [$items]);
        $this->assertSame(['foo' => 'bar'], $array);

        $items = new TestJsonSerializeObject;
        $array = $method->invokeArgs($coollection, [$items]);
        $this->assertSame(['foo' => 'bar'], $array);

        $items = new Coollection(['foo' => 'bar']);
        $array = $method->invokeArgs($coollection, [$items]);
        $this->assertSame(['foo' => 'bar'], $array);

        $items = ['foo' => 'bar'];
        $array = $method->invokeArgs($coollection, [$items]);
        $this->assertSame(['foo' => 'bar'], $array);

        $items = ['foo' => 'bar'];
        $array = $method->invokeArgs($coollection, [$items]);
        $this->assertSame(['foo' => 'bar'], $array);

        $items = new \ArrayIterator(['foo' => 'bar']);
        $array = $method->invokeArgs($coollection, [$items]);
        $this->assertSame(['foo' => 'bar'], $array);
    }

    // public function map(callable $callback) TODO
    // public function mapSpread(callable $callback) TODO
    // public function mapToDictionary(callable $callback) TODO
    // public function mapToGroups(callable $callback) TODO
    // public function mapWithKeys(callable $callback) TODO
    // public function flatMap(callable $callback) TODO
    // public function max($callback = null) TODO
    // public function min($callback = null) TODO
    // public function partition($callback) TODO
    // public function pipe(callable $callback) TODO
    // public function reduce(callable $callback, $initial = null) TODO
    // public function reject($callback) TODO
    // public function sort(callable $callback = null) TODO
    // public function sortBy($callback, $options = SORT_REGULAR, $descending = false) TODO
    // public function sortByDesc($callback, $options = SORT_REGULAR) TODO
    // public function sum($callback = null) TODO
    // public function tap(callable $callback) TODO
    // public function transform(callable $callback) TODO
}

class TestSupportCollectionHigherOrderItem
{
    public $name = 'taylor';

    public function uppercase()
    {
        $this->name = strtoupper($this->name);
    }
}

class TestArrayableObject implements Arrayable
{
    public function toArray()
    {
        return ['foo' => 'bar'];
    }
}

class TestJsonableObject implements Jsonable
{
    public function toJson($options = 0)
    {
        return '{"foo":"bar"}';
    }
}

class TestJsonSerializeObject implements JsonSerializable
{
    public function jsonSerialize()
    {
        return ['foo' => 'bar'];
    }
}

