<?php

declare(strict_types=1);

namespace Foundation\Container\Attributes;

use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;

/**
 * Injects an input parameter from the request.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestInputParameter implements ContextualAttribute
{
    public function __construct(public string $parameter) {}

    /**
     * Resolve the input parameter from the request.
     */
    public static function resolve(self $attribute, Container $container): mixed
    {
        return $container->make('request')->input($attribute->parameter);
    }
}
