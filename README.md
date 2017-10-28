# Coollection

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what
PSRs you support to avoid any confusion with users and contributors.

https://github.com/tightenco/collect/archive/v5.5.16.zip

## Install

Via Composer

``` bash
$ composer require pragmarx/coollection
```

## Usage

``` php
$collection = coollection(['first_name' => 'Barak Obama']);

echo $collection->first_name;

// Barak Obama

echo $collection->flip()->barak_obama == 'first_name';

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

- [Antonio Carlos Ribeiro][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/pragmarx/coollection.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/pragmarx/coollection/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/pragmarx/coollection.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/pragmarx/coollection.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/pragmarx/coollection.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/pragmarx/coollection
[link-travis]: https://travis-ci.org/pragmarx/coollection
[link-scrutinizer]: https://scrutinizer-ci.com/g/pragmarx/coollection/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/pragmarx/coollection
[link-downloads]: https://packagist.org/packages/pragmarx/coollection
[link-author]: https://github.com/antonioribeiro
[link-contributors]: ../../contributors
