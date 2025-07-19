<?php

declare(strict_types=1);

namespace Domain\Tags\Commands;

use Domain\Tags\Actions\CreateNewTag;
use Domain\Tags\Enums\TagType;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CreateTag extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'tags:create';

    /**
     * @var string
     */
    protected $description = 'Create a new tag';

    public function handle(): void
    {
        $name = text(
            label: 'Name',
            required: true,
        );

        $type = select(
            label: 'Type',
            options: collect(TagType::cases())->pluck('name', 'value'),
            required: true,
        );

        app(CreateNewTag::class)->execute(compact('name', 'type'));

        $this->components->info('Tag has been created successfully.');
    }
}
