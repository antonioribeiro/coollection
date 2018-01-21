<?php

$aliases = [
    Illuminate\Support\Collection::class => IlluminateExtracted\Support\Collection::class,
    Illuminate\Support\Arr::class => IlluminateExtracted\Support\Arr::class,
    Illuminate\Support\Carbon::class => IlluminateExtracted\Support\Carbon::class,
    Illuminate\Support\HigherOrderCollectionProxy::class => IlluminateExtracted\Support\HigherOrderCollectionProxy::class,
    Illuminate\Support\HigherOrderTapProxy::class => IlluminateExtracted\Support\HigherOrderTapProxy::class,
    Illuminate\Support\HtmlString::class => IlluminateExtracted\Support\HtmlString::class,
    Illuminate\Support\Optional::class => IlluminateExtracted\Support\Optional::class,
    Illuminate\Support\Str::class => IlluminateExtracted\Support\Str::class,
    Illuminate\Support\Debug\Dumper::class => IlluminateExtracted\Support\Debug\Dumper::class,
    Illuminate\Support\Debug\HtmlDumper::class => IlluminateExtracted\Support\Debug\HtmlDumper::class,
];

foreach ($aliases as $illuminate => $tighten) {
    if (class_exists($illuminate) && ! interface_exists($illuminate) && ! trait_exists($illuminate)) {
        class_alias($illuminate, $tighten);
    }
}
