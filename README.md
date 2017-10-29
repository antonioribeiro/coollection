# Coollection

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
<!-- [![Total Downloads][ico-downloads]][link-downloads] -->
<!-- [![Quality Score][ico-code-quality]][link-code-quality] -->

#### Access collection items as object properties

``` php
$collection->name

$collection->addresses->first()->street_name

$collection->flatten()->cars->filter(function($car) { return $car->name == 'ferrari' })->last()->model

$countries->where('name.common', 'United States')->first()->currency->name->english;
```

## Why?

Answering with a question: which one is easier to look at?

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

Isn't this easier on the eyes?

``` php
collect($vimeo)->body->data->first()->metadata->connections->likes->total;
```

Or you prefer this one?


``` php
collect($vimeo)['body']['data'][0]['metadata']['connections']['likes']['total'];
```

## PHP Agnostic

This is an agnostic PHP package, which uses an extracted version of Laravel's Illuminate Collection, it's actually [tightenco/collect](https://github.com/tightenco/collect), modified to access collection items as properties.

## Documentation

It's Laravel's Collection, at full power, so you can check [its docs](https://laravel.com/docs/5.5/collections). The only difference is that you can access items (array keys) as properties, like any other PHP object:

``` php
$collection->map($mapper)->reduce($reducer)->random()->address->street

$this->sendThanks(
    $collection->where('full_name', 'Barack Obama')->addresses->random()
);    

$countries->where('name.common', 'United States')->first()->currency->symbol;
```

## Changes to [tightenco/collect](https://github.com/tightenco/collect)

As it is still using [Illuminate's namespace](https://github.com/tightenco/collect/pull/56), which will conflict with **Illuminate\Support\Collection**, for those who need to use it in a Laravel project, this package has an [updater script](upgrade-collect.sh) which downloads tightenco/collect sources and change the namespace to [**Tightenco\Collect**](https://github.com/antonioribeiro/coolection/blob/master/src/package/Support/Tightenco/Collect/src/Tightenco/Collect/Support/Collection.php).  

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

- [Antonio Carlos Ribeiro](https://twitter.com/iantonioribeiro)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/pragmarx/coollection.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[ico-travis-waiting]: https://img.shields.io/travis/antonioribeiro/coollection/master.svg?style=flat-square
[ico-travis]: https://img.shields.io/badge/build-passing-green.svg?style=flat-square

[ico-scrutinizer-waiting]: https://img.shields.io/scrutinizer/coverage/g/antonioribeiro/coollection.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/badge/coverage-92%20%25-green.svg?style=flat-square

[ico-code-quality]: https://img.shields.io/scrutinizer/g/antonioribeiro/coollection.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/pragmarx/coollection.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/pragmarx/coollection
[link-travis]: https://travis-ci.org/antonioribeiro/coollection
[link-scrutinizer]: https://scrutinizer-ci.com/g/antonioribeiro/coollection/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/antonioribeiro/coollection
[link-downloads]: https://packagist.org/packages/pragmarx/coollection
[link-author]: https://github.com/antonioribeiro
[link-contributors]: ../../contributors
