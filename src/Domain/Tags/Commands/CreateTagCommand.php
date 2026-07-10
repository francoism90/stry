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

class CreateTagCommand extends Command implements Isolatable
{
    /**
     * @var string
     */
    protected $signature = 'tags:create {--locale= : The locale for the tag (default: app locale)}';

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

        $tag = Tag::findOrCreate($name, $type, $this->option('locale') ?? app()->getLocale());

        info("Tag `{$tag->name}` has been created successfully.");
    }
}
