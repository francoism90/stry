<?php

declare(strict_types=1);

namespace Foundation\Container\Attributes;

use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;

/**
 * Injects a query parameter from the request.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class ValidatedFormRequest implements ContextualAttribute
{
    public function __construct(public string $abstract) {}

    /**
     * Resolve the query parameter from the request.
     *
     * @param  self  $attribute
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return mixed
     */
    public static function resolve(self $attribute, Container $container): mixed
    {
        return $container->make($attribute->abstract)->safe()->all();
    }
}
