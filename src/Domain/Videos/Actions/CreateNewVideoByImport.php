<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;
use SplFileInfo;

class CreateNewVideoByImport
{
    public function handle(User $user, string $disk, string $path): mixed
    {
        return DB::transaction(function () use ($user, $disk, $path) {
            $file = new SplFileInfo($path);

            /** @var Video $video */
            $video = $user->videos()->create([
                'name' => $file->getBasename(".{$file->getExtension()}"),
            ]);

            $video
                ->addMediaFromDisk($path, $disk)
                ->withResponsiveImages()
                ->toMediaCollection('clips');

            return $video;
        });
    }
}
