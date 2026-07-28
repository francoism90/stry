<?php

declare(strict_types=1);

namespace Foundation\Http\Properties;

use Illuminate\Database\Eloquent\Model;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class ModelProperties implements ProvidesInertiaProperty
{
    public function __construct(
        protected Model|string|null $model = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        /** @var Model|null $model */
        $model = $this->getModel();

        return [
            'id' => $model?->getRouteKey(),
            'subject' => $model?->getMorphClass(),
        ];
    }

    protected function getModel(): ?Model
    {
        return is_a($this->model, Model::class, true)
            ? app($this->model)
            : $this->model;
    }
}
