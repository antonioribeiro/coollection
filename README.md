# Coollection

<p align="center">
    <a href="https://packagist.org/packages/pragmarx/coollection"><img alt="Latest Stable Version" src="https://img.shields.io/packagist/v/pragmarx/coollection.svg?style=flat-square"></a>
    <a href="LICENSE"><img alt="License" src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square"></a>
    <a href="https://scrutinizer-ci.com/g/antonioribeiro/coollection/?branch=master"><img alt="Code Quality" src="https://img.shields.io/scrutinizer/g/antonioribeiro/coollection.svg?style=flat-square"></a>
    <a href="https://travis-ci.org/antonioribeiro/coollection"><img alt="Build" src="https://img.shields.io/travis/antonioribeiro/coollection.svg?style=flat-square"></a>
    <a href="https://packagist.org/packages/pragmarx/coollection"><img alt="Downloads" src="https://img.shields.io/packagist/dt/pragmarx/coollection.svg?style=flat-square"></a>
</p>
<p align="center">
    <a href="https://scrutinizer-ci.com/g/antonioribeiro/coollection/?branch=master"><img alt="Coverage" src="https://img.shields.io/scrutinizer/coverage/g/antonioribeiro/coollection.svg?style=flat-square"></a>
    <a href="https://styleci.io/repos/108602178"><img alt="StyleCI" src="https://styleci.io/repos/108602178/shield"></a>
    <!-- <a href="https://insight.sensiolabs.com/projects/156fbef1-b03f-4fca-ba97-57874b7a35bf"><img alt="SensioLabsInsight" src="https://img.shields.io/sensiolabs/i/156fbef1-b03f-4fca-ba97-57874b7a35bf.svg?style=flat-square"></a> -->
    <a href="https://travis-ci.org/antonioribeiro/coollection"><img alt="PHP" src="https://img.shields.io/badge/PHP-7.0%20--%207.3-brightgreen.svg?style=flat-square"></a>
</p>

#### Access collection items as objects properties

Coollection is [Laravel's Illuminate\Collection](https://laravel.com/docs/5.5/collections) repackaged to be used as all properties were objects: 

``` php
$collection->name

$collection->addresses->first()->street_name

$collection->flatten()->cars->filter(function($car) { return $car->name == 'ferrari' })->last()->model

$countries->where('name.common', 'United States')->first()->currency->name->english;
```

#### Tightenco\Collect

To be agnostic and have complete idependence from Laravel's, but also to allow it to be used in Laravel applications, this package extends [Tightenco\Collect](https://github.com/tightenco/collect), developed by [Matt Stauffer](https://twitter.com/stauffermatt) from [Tighten](https://twitter.com/tightenco). 

## Why?

#### Answering with a question: which one is easier to look at?

``` php
collect(
    collect(
        collect(
            collect(
                $collection['cars']
            )->unique('constructor')['models']
        )->last()['model']
    )['colors']
)->first()['rgb']
```

or

``` php
$collection->cars->unique('constructor')->last()->model->colors->first()->rgb
```

#### Isn't this easier on the eyes?

``` php
collect($vimeo)->body->data->first()->metadata->connections->likes->total;
```

Or you prefer this one?


``` php
collect($vimeo)['body']['data'][0]['metadata']['connections']['likes']['total'];
```

#### Used with Laravel request it is useful, if you receive:
 
``` json
{"pagination":{"perPage":100,"pageNumber":1}}
```

You `collect()` it:

``` php
$input = coollect($request->all());
```

And you can just:

``` php
$input->pagination->perPage
```

Instead of:

``` php
$input->get('pagination')['perPage']
```

You can also use "dot notation" to get your items:

``` php
$input->get('pagination.perPage')
```

## PHP Agnostic

This is an agnostic PHP package, an extraction of Illuminate\Support\Collection with all needed classes, interfaces and traits. For that if you wish just to use Illuminate's Collection, you just have to import the class Collection:

``` php
$collection = new IlluminateExtracted\Support\Collection(['my collection']); 
```

or use the usual helper:

``` php
$collection = collect(['my collection']); 
```

## Documentation

It's Laravel's Collection, at full power, so you can check [its docs](https://laravel.com/docs/5.6/collections). The only difference is that you can access items (array keys) as properties, like any other PHP object:

``` php
$collection->map($mapper)->reduce($reducer)->random()->address->street

$this->sendThanks(
    $collection->where('full_name', 'Barack Obama')->addresses->random()
);    

$countries->where('name.common', 'United States')->first()->currency->symbol;
```

## Install

Via Composer

``` bash
$ composer require pragmarx/coollection
```

## Usage

Instantiate, the class directly or using the helper:

``` php
$c = new Coollection(['first_name' => 'Barack Obama']);

$c = coollect(['first_name' => 'Barack Obama']);
``` 

Then you use it as an object:

``` php
echo $c->first_name;

// Barack Obama


echo $c->flip()->barak_obama == 'first_name' 
    ? 'true' 
    : 'false';

// true
```

One word keys are case insensitive:

``` php
echo $c->rio;
echo $c->RIO;
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email acr@antoniocarlosribeiro.com instead of using the issue tracker.

## Credits

- This package is an extension of [Tightenco\Collect](https://github.com/tightenco/collect), developed by [Matt Stauffer](https://twitter.com/stauffermatt) from [Tighten](https://twitter.com/tightenco).
- [Tightenco\Collect](https://github.com/tightenco/collect) is an extraction of The Laravel Framework's Collection, created by [Taylor Otwell](https://twitter.com/taylorotwell).
- Package creator [Antonio Carlos Ribeiro](https://twitter.com/iantonioribeiro)
- [Contributors](https://github.com/antonioribeiro/ia-str/graphs/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
