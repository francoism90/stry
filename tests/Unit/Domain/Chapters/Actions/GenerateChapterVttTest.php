<?php

declare(strict_types=1);

use Domain\Chapters\Actions\GenerateChapterVtt;
use Domain\Chapters\Enums\ChapterType;
use Domain\Chapters\Models\Chapter;
use Domain\Videos\Models\Video;

it('produces a bare webvtt document for a video with no chapters', function () {
    $video = (new Video)->setRelation('chapters', collect());

    $vtt = (new GenerateChapterVtt)->handle($video);

    expect($vtt)->toBe('WEBVTT');
});

it('renders one cue per chapter in order with formatted timestamps', function () {
    $intro = Chapter::factory()->make([
        'ulid' => 'intro-ulid',
        'type' => ChapterType::Intro,
        'label' => 'Intro',
        'start_time' => 0,
        'end_time' => 90.5,
    ]);

    $recap = Chapter::factory()->make([
        'ulid' => 'recap-ulid',
        'type' => ChapterType::Recap,
        'label' => 'Recap',
        'start_time' => 90.5,
        'end_time' => 150,
    ]);

    $video = (new Video)->setRelation('chapters', collect([$intro, $recap]));

    $vtt = (new GenerateChapterVtt)->handle($video);

    expect($vtt)->toBe(<<<'VTT'
        WEBVTT

        intro-ulid
        00:00:00.000 --> 00:01:30.500
        Intro

        recap-ulid
        00:01:30.500 --> 00:02:30.000
        Recap
        VTT);
});
