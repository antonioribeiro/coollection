<?php

namespace PragmaRX\Coollection\Tests;

use PragmaRX\Coollection\Package\Coollection;
use PragmaRX\Coollection\Tests\Support\Dummy;
use Tightenco\Collect\Support\Collection as TightencoCollect;

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

        $this->assertEquals($c->all(), [1, 2]);

        $this->assertInstanceOf(Coollection::class, $c->map(function ($item) {
            return $item;
        }));
    }

    public function testAll()
    {
        $this->assertEquals($this->coollection->all(), $this->array);
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
            return $key != 'php';
        });

        $this->assertFalse($compare->has('php'));

        $this->assertTrue($compare->has('laravel'));
    }

    public function testWhen()
    {
        $this->assertEquals($this->coollection->count(), 10);

        $this->coollection->skills->when(true, function () {
            return $this->coollection->push('void');
        });

        $this->assertEquals($this->coollection->count(), 11);
    }

    public function testUnless()
    {
        $this->assertEquals($this->coollection->count(), 10);

        $this->coollection->skills->when(true, function () {
            return $this->coollection->push('void');
        });

        $this->assertEquals($this->coollection->count(), 11);
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
        $this->assertFalse(
            $this->full->whereStrict('position', '30')->first()->has('last_name')
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
        $this->assertFalse(
            $this->full->whereInStrict('position', ['30'])->first()->has('last_name')
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
        $this->assertFalse(
            $this->full->whereNotInStrict('both_have', [true])->first()->has('last_name')
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

    public function testGroupBy()
    {
        $this->assertInstanceOf(Coollection::class, $c = $this->coollection->accounting->groupBy('account_id'));

        $this->assertEquals($c->toArray(), static::ACCOUNTING_GROUPED);
    }

    public function testKeyBy()
    {
        $this->assertEquals(
            $this->coollection->accounting->keyBy('account_id')->toArray(),
            (new TightencoCollect($this->coollection->accounting))->keyBy('account_id')->toArray()
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

        $tco = new TightencoCollect($values->toArray());

        $this->assertEquals(
            $values->intersect($tco)->toArray(),
            $tco->toArray()
        );

        $this->assertInstanceOf(Coollection::class, $values->intersect($tco));
    }

    public function testIntersectByKeys()
    {
        $values = $this->coollection->accounting->pluck('product')->values();

        $tco = new TightencoCollect($values->toArray());

        $this->assertEquals(
            $values->intersectByKeys($tco)->toArray(),
            $tco->toArray()
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
        $this->testIntersect();
    }

    public function testMap()
    {
        $c = $this->coollection->address->map(function () {
            return ['mapped'];
        })->flatten()->values();

        $this->assertEquals(['mapped', 'mapped'], $c->toArray());

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

        $this->assertEquals([1, 5, 9, 13, 17], $sequence->all());

        $this->assertInstanceOf(Coollection::class, $sequence);
    }

    public function testMapToDictionary()
    {
        $c = $this->coollection->skills->mapToDictionary(function ($item) {
            return $item;
        });

        $this->assertEquals(
            ['5.6', '3.2', true],
            $c->flatten()->values()->toArray()
        );

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testMapToGroups()
    {
        $c = $this->coollection->accounting->mapToGroups(function ($item) {
            return [$item['product'] => $item['product']];
        });

        $this->assertEquals(
            ['Chair', 'Bookcase', 'Desk'],
            $c->flatten()->values()->toArray()
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

        $this->assertInstanceOf(Dummy::class, $c->first()[0]);

        $this->assertNotInstanceOf(Coollection::class, $c->first()[0]);

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testMax()
    {
        $this->assertEquals(39, $this->coollection->ages->max()[0]);

        $this->assertInstanceOf(Coollection::class, $this->coollection->ages->max());
    }

    public function testMerge()
    {
        $result = [
            1, 2, 3, 4, 5, 6, 7, 8, 9, 39,
            10, 9, 9, 10, 8, 6, 5,
        ];

        $c = $this->coollection->ages->merge($this->coollection->grades);

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
        $this->assertEquals(1, $this->coollection->ages->min()[0]);

        $this->assertInstanceOf(Coollection::class, $this->coollection->ages->min());
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
        $c = $this->coollection->skills->laravel->pop();

        $this->assertEquals('5.5', $c->flatten()->toArray()[0]);

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testPrepend()
    {
        $c = $this->coollection->skills->laravel->prepend(['bash' => 'all']);

        $this->assertEquals('all', $c->first()->flatten()->toArray()[0]);

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testPush()
    {
        $c = $this->coollection->skills->laravel->push(['bash' => 'all']);

        $this->assertEquals('3.2', $c->first()->flatten()->toArray()[0]);

        $this->assertEquals('all', $c->last()->flatten()->toArray()[0]);

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testConcat()
    {
        $c = $this->coollection->skills->laravel->concat($this->coollection->skills->php);

        $concat = [
            '3.2', '4.0', '4.2', '5.0', '5.1', '5.2', '5.3', '5.4', '5.5',
            '5.6', '7.0', '7.1', '7.2',
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

        $this->assertEquals($laravel, $pulled);

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

        $this->assertTrue(in_array($c->first()[0], ['5.6', '3.2', true], true));

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testReduce()
    {
        $return = 'reduced';

        $c = $this->coollection->skills->reduce(function () use ($return) {
            return $return;
        });

        $this->assertEquals($return, $c->first()[0]);

        $this->assertInstanceOf(Coollection::class, $c);
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

        $this->assertEquals('5.6', $php->first()[0]);

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

    public function testUniqueValues()
    {
        $this->testIntersect();
    }

    public function testZip()
    {
        $c = $this->coollection->skills->laravel->take(3)->zip($this->coollection->skills->php->take(3));

        $this->assertEquals(['3.2', '5.6', '4.0', '7.0', '4.2', '7.1'], $c->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testPad()
    {
        $c = $this->coollection->skills->laravel->take(1)->pad(2, '6.0');

        $this->assertEquals(['3.2', '6.0'], $c->flatten()->toArray());

        $this->assertInstanceOf(Coollection::class, $c);
    }

    public function testJsonSerialize()
    {
        $this->assertEquals('["5.6","7.0","7.1","7.2"]', json_encode($this->coollection->skills->php->jsonSerialize()));
    }

    public function testToJson()
    {
        $this->assertEquals('["5.6","7.0","7.1","7.2"]', $this->coollection->skills->php->toJson());
    }
}
