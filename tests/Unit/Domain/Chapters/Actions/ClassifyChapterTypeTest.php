<?php

declare(strict_types=1);

use Domain\Chapters\Actions\ClassifyChapterType;
use Domain\Chapters\Enums\ChapterType;

it('classifies labels matching the intro pattern', function (string $label) {
    expect((new ClassifyChapterType)->handle($label))->toBe(ChapterType::Intro);
})->with([
    'Intro',
    'Introduction',
    'Leader',
    'Opening',
    'introduction (extended)',
]);

it('classifies labels matching the recap pattern', function (string $label) {
    expect((new ClassifyChapterType)->handle($label))->toBe(ChapterType::Recap);
})->with([
    'Recap',
    'Previously on...',
    'Catch-up',
]);

it('classifies labels matching the credits pattern', function (string $label) {
    expect((new ClassifyChapterType)->handle($label))->toBe(ChapterType::Credits);
})->with([
    'Credits',
    'End Credits',
    'Outro',
]);

it('is case insensitive', function () {
    expect((new ClassifyChapterType)->handle('INTRODUCTION'))->toBe(ChapterType::Intro);
});

it('falls back to the configured default type when nothing matches', function () {
    expect((new ClassifyChapterType)->handle('Scene Two'))->toBe(ChapterType::Scene);
});

it('classifies using overridden config patterns', function () {
    config(['chapters.patterns' => ['credits' => '/foo/i']]);

    expect((new ClassifyChapterType)->handle('foo bar'))->toBe(ChapterType::Credits)
        ->and((new ClassifyChapterType)->handle('Introduction'))->toBe(ChapterType::Scene);
});
