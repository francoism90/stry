<?php

declare(strict_types=1);

use Domain\Chapters\Actions\ClassifyChapterType;
use Domain\Chapters\Enums\ChapterType;
use Domain\Chapters\Settings\ChapterSettings;

it('classifies labels matching the intro pattern', function (string $label) {
    expect(app(ClassifyChapterType::class)->handle($label))->toBe(ChapterType::Intro);
})->with([
    'Intro',
    'Introduction',
    'Leader',
    'Opening',
    'introduction (extended)',
    'OP',
    'Teaser',
]);

it('classifies labels matching the recap pattern', function (string $label) {
    expect(app(ClassifyChapterType::class)->handle($label))->toBe(ChapterType::Recap);
})->with([
    'Recap',
    'Previously on...',
    'Catch-up',
    'Preview',
    'Story so far',
]);

it('classifies labels matching the credits pattern', function (string $label) {
    expect(app(ClassifyChapterType::class)->handle($label))->toBe(ChapterType::Credits);
})->with([
    'Credits',
    'End Credits',
    'Outro',
    'ED',
    'Closing',
]);

it('classifies labels matching the sponsor pattern', function (string $label) {
    expect(app(ClassifyChapterType::class)->handle($label))->toBe(ChapterType::Sponsor);
})->with([
    'Sponsor',
    'Sponsored segment',
    'Advertisement',
    'Promo',
]);

it('classifies labels matching the filler pattern', function (string $label) {
    expect(app(ClassifyChapterType::class)->handle($label))->toBe(ChapterType::Filler);
})->with([
    'Filler',
    'Filler tangent',
]);

it('classifies labels matching the interaction reminder pattern', function (string $label) {
    expect(app(ClassifyChapterType::class)->handle($label))->toBe(ChapterType::InteractionReminder);
})->with([
    'Like and subscribe',
    'Subscribe reminder',
    'Interaction reminder',
]);

it('is case insensitive', function () {
    expect(app(ClassifyChapterType::class)->handle('INTRODUCTION'))->toBe(ChapterType::Intro);
});

it('falls back to the configured default type when nothing matches', function () {
    expect(app(ClassifyChapterType::class)->handle('Scene Two'))->toBe(ChapterType::Scene);
});

it('classifies using overridden settings patterns', function () {
    ChapterSettings::fake([
        'patterns' => ['credits' => '/foo/i'],
    ]);

    expect(app(ClassifyChapterType::class)->handle('foo bar'))->toBe(ChapterType::Credits)
        ->and(app(ClassifyChapterType::class)->handle('Introduction'))->toBe(ChapterType::Scene);
});

it('falls back to the configured default type setting', function () {
    ChapterSettings::fake([
        'default_type' => ChapterType::Credits,
    ]);

    expect(app(ClassifyChapterType::class)->handle('Scene Two'))->toBe(ChapterType::Credits);
});
