<?php

declare(strict_types=1);

use Foundation\Http\Properties\ScoutBuilderProperties;
use Illuminate\Http\Request;
use Inertia\RenderContext;

function scoutBuilderPropertiesRequest(array $query): Request
{
    $request = Request::create('/tags', 'GET', $query);
    $request->setLaravelSession(app('session.store'));

    return $request;
}

function scoutBuilderProperties(array $query): array
{
    $request = scoutBuilderPropertiesRequest($query);

    return (new ScoutBuilderProperties('tags'))->toInertiaProperties(new RenderContext('Tags/TagIndex', $request));
}

it('remembers a non-empty query', function () {
    $properties = scoutBuilderProperties(['query' => 'remembered term']);

    expect($properties['search'])->toBe('remembered term');
});

it('forgets the remembered query when an empty query is explicitly submitted', function () {
    scoutBuilderProperties(['query' => 'remembered term']);

    $properties = scoutBuilderProperties(['query' => '']);

    expect($properties['search'])->toBeNull();
});

it('leaves the remembered query untouched on a plain navigation without a query param', function () {
    scoutBuilderProperties(['query' => 'remembered term']);

    $properties = scoutBuilderProperties([]);

    expect($properties['search'])->toBe('remembered term');
});
