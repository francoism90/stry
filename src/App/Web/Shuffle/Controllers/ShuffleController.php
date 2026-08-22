<?php

declare(strict_types=1);

namespace App\Web\Shuffle\Controllers;

use App\Web\Shuffle\Enums\ShuffleType;
use Domain\Tags\Actions\GetRandomTag;
use Domain\Videos\Actions\GetRandomVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ShuffleController implements HasMiddleware
{
    /**
     * Bounds how many recently shown IDs are remembered per type, so the
     * session payload stays small and old picks eventually become eligible
     * again instead of requiring every candidate to be exhausted first.
     */
    private const int HISTORY_LIMIT = 50;

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request, ShuffleType $type): RedirectResponse
    {
        $key = "shuffle.{$type->value}";

        $excludeIds = (array) $request->session()->get($key, []);

        $model = match ($type) {
            ShuffleType::Videos => app(GetRandomVideo::class)->handle($excludeIds),
            ShuffleType::Tags => app(GetRandomTag::class)->handle($excludeIds),
        };

        if (! $model) {
            $request->session()->forget($key);

            return redirect()->route('home');
        }

        $request->session()->put(
            $key,
            array_slice([...$excludeIds, $model->getKey()], -self::HISTORY_LIMIT),
        );

        return match ($type) {
            ShuffleType::Videos => redirect()->route('videos.show', $model),
            ShuffleType::Tags => redirect()->route('tags.show', $model),
        };
    }
}
