<?php

declare(strict_types=1);

namespace Domain\Tags\Commands;

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CreateCommand extends Command implements Isolatable
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

        $tag = Tag::findOrCreate($name, $type);

        info("Tag `{$tag->name}` has been created successfully.");
    }
}
