<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Spatie\LaravelTypeScriptTransformer\LaravelData\LaravelDataTypeScriptTransformerExtension;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider;
use Spatie\TypeScriptTransformer\Transformers\AttributedClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;

/**
 * Writes the classes and enums marked with #[TypeScript] in src/ to resources/js/types/generated.ts.
 * Run `php artisan typescript:transform` after changing them.
 */
class TypeScriptTransformerServiceProvider extends TypeScriptTransformerApplicationServiceProvider
{
    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        $config
            ->extension(new LaravelDataTypeScriptTransformerExtension)
            ->transformer(AttributedClassTransformer::class)
            ->transformer(EnumTransformer::class)
            ->transformDirectories(base_path('src'))
            ->outputDirectory(resource_path('js/types'))
            ->writer(new FlatModuleWriter('generated.ts'))
            ->withoutManifest();
    }
}
