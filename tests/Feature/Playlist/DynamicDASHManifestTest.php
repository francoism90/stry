<?php

declare(strict_types=1);

use Foxws\Streamer\Http\DynamicDASHManifest;
use Illuminate\Support\Facades\Storage;

it('expands SegmentTemplate with $Number$ into SegmentList with signed URLs', function () {
    Storage::fake('local');
    Storage::disk('local')->put('manifest.mpd', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<MPD xmlns="urn:mpeg:dash:schema:mpd:2011" profiles="urn:mpeg:dash:profile:isoff-live:2011" type="static" mediaPresentationDuration="PT10.066667S">
  <Period id="0">
    <AdaptationSet id="0" contentType="video" segmentAlignment="true">
      <Representation id="0" bandwidth="102489" codecs="avc1.4d400c" mimeType="video/mp4" width="256" height="144">
        <SegmentTemplate timescale="15360" initialization="video_144p_init.mp4" media="video_144p_$Number$.mp4" startNumber="1">
          <SegmentTimeline>
            <S t="0" d="154624"/>
          </SegmentTimeline>
        </SegmentTemplate>
      </Representation>
      <Representation id="1" bandwidth="226456" codecs="avc1.4d4015" mimeType="video/mp4" width="426" height="240">
        <SegmentTemplate timescale="15360" initialization="video_240p_init.mp4" media="video_240p_$Number$.mp4" startNumber="1">
          <SegmentTimeline>
            <S t="0" d="154624"/>
          </SegmentTimeline>
        </SegmentTemplate>
      </Representation>
    </AdaptationSet>
  </Period>
</MPD>
XML
    );

    $manifest = new DynamicDASHManifest('local');
    $manifest->open('manifest.mpd')
        ->setMediaUrlResolver(fn (string $file) => "https://s3.example.com/signed/{$file}?token=abc")
        ->setInitUrlResolver(fn (string $file) => "https://s3.example.com/signed/{$file}?token=def");

    $result = $manifest->get();

    // SegmentTemplate should be replaced with SegmentList
    expect($result)->not->toContain('SegmentTemplate')
        ->and($result)->toContain('SegmentList')
        // Init segments should be signed via init resolver
        ->and($result)->toContain('sourceURL="https://s3.example.com/signed/video_144p_init.mp4?token=def"')
        ->and($result)->toContain('sourceURL="https://s3.example.com/signed/video_240p_init.mp4?token=def"')
        // Media segments should have $Number$ expanded to 1 and signed via media resolver
        ->and($result)->toContain('media="https://s3.example.com/signed/video_144p_1.mp4?token=abc"')
        ->and($result)->toContain('media="https://s3.example.com/signed/video_240p_1.mp4?token=abc"')
        // Template variables should not appear
        ->and($result)->not->toContain('$Number$');
});

it('expands multiple segments from SegmentTimeline repeats', function () {
    Storage::fake('local');
    Storage::disk('local')->put('manifest.mpd', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<MPD xmlns="urn:mpeg:dash:schema:mpd:2011">
  <Period>
    <AdaptationSet>
      <Representation id="0">
        <SegmentTemplate timescale="15360" initialization="init.mp4" media="seg_$Number$.mp4" startNumber="1">
          <SegmentTimeline>
            <S t="0" d="30720"/>
            <S d="30720" r="2"/>
            <S d="15360"/>
          </SegmentTimeline>
        </SegmentTemplate>
      </Representation>
    </AdaptationSet>
  </Period>
</MPD>
XML
    );

    $manifest = new DynamicDASHManifest('local');
    $manifest->open('manifest.mpd')
        ->setMediaUrlResolver(fn (string $file) => "https://cdn.example.com/{$file}")
        ->setInitUrlResolver(fn (string $file) => "https://cdn.example.com/{$file}");

    $result = $manifest->get();

    // 1 + (1 + r=2) + 1 = 5 segments total
    expect($result)->toContain('media="https://cdn.example.com/seg_1.mp4"')
        ->and($result)->toContain('media="https://cdn.example.com/seg_2.mp4"')
        ->and($result)->toContain('media="https://cdn.example.com/seg_3.mp4"')
        ->and($result)->toContain('media="https://cdn.example.com/seg_4.mp4"')
        ->and($result)->toContain('media="https://cdn.example.com/seg_5.mp4"')
        ->and($result)->not->toContain('$Number$')
        ->and($result)->not->toContain('SegmentTemplate');
});

it('signs concrete SegmentTemplate URLs directly', function () {
    Storage::fake('local');
    Storage::disk('local')->put('manifest.mpd', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<MPD xmlns="urn:mpeg:dash:schema:mpd:2011">
  <Period>
    <AdaptationSet>
      <SegmentTemplate initialization="init.m4s" media="chunk.m4s"/>
    </AdaptationSet>
  </Period>
</MPD>
XML
    );

    $manifest = new DynamicDASHManifest('local');
    $manifest->open('manifest.mpd')
        ->setMediaUrlResolver(fn (string $file) => "https://cdn.example.com/{$file}")
        ->setInitUrlResolver(fn (string $file) => "https://cdn.example.com/{$file}");

    $result = $manifest->get();

    expect($result)->toContain('initialization="https://cdn.example.com/init.m4s"')
        ->and($result)->toContain('media="https://cdn.example.com/chunk.m4s"');
});

it('signs existing SegmentList sourceURL and media attributes', function () {
    Storage::fake('local');
    Storage::disk('local')->put('manifest.mpd', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<MPD xmlns="urn:mpeg:dash:schema:mpd:2011">
  <Period>
    <AdaptationSet>
      <SegmentList>
        <Initialization sourceURL="init.m4s"/>
        <SegmentURL media="segment-1.m4s"/>
        <SegmentURL media="segment-2.m4s"/>
      </SegmentList>
    </AdaptationSet>
  </Period>
</MPD>
XML
    );

    $manifest = new DynamicDASHManifest('local');
    $manifest->open('manifest.mpd')
        ->setMediaUrlResolver(fn (string $file) => "https://cdn.example.com/{$file}")
        ->setInitUrlResolver(fn (string $file) => "https://cdn.example.com/{$file}");

    $result = $manifest->get();

    expect($result)->toContain('sourceURL="https://cdn.example.com/init.m4s"')
        ->and($result)->toContain('media="https://cdn.example.com/segment-1.m4s"')
        ->and($result)->toContain('media="https://cdn.example.com/segment-2.m4s"');
});

it('does not double-resolve URLs added by template expansion', function () {
    Storage::fake('local');
    Storage::disk('local')->put('manifest.mpd', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<MPD xmlns="urn:mpeg:dash:schema:mpd:2011">
  <Period>
    <AdaptationSet>
      <Representation id="0">
        <SegmentTemplate timescale="1" initialization="init.mp4" media="seg_$Number$.mp4" startNumber="1">
          <SegmentTimeline>
            <S t="0" d="10"/>
          </SegmentTimeline>
        </SegmentTemplate>
      </Representation>
    </AdaptationSet>
  </Period>
</MPD>
XML
    );

    $initCallCount = 0;

    $manifest = new DynamicDASHManifest('local');
    $manifest->open('manifest.mpd')
        ->setMediaUrlResolver(fn (string $file) => "https://cdn.example.com/media/{$file}")
        ->setInitUrlResolver(function (string $file) use (&$initCallCount) {
            $initCallCount++;

            return "https://cdn.example.com/init/{$file}";
        });

    $result = $manifest->get();

    // Init should only be resolved once (not double-resolved by sourceURL loop)
    expect($initCallCount)->toBe(1)
        ->and($result)->toContain('sourceURL="https://cdn.example.com/init/init.mp4"')
        ->and($result)->not->toContain('https://cdn.example.com/init/https://');
});

it('leaves URLs unchanged when no resolvers are set', function () {
    Storage::fake('local');
    Storage::disk('local')->put('manifest.mpd', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<MPD xmlns="urn:mpeg:dash:schema:mpd:2011">
  <Period>
    <AdaptationSet>
      <Representation id="0">
        <SegmentTemplate timescale="1" initialization="init.mp4" media="seg_$Number$.mp4" startNumber="1">
          <SegmentTimeline>
            <S t="0" d="10"/>
          </SegmentTimeline>
        </SegmentTemplate>
      </Representation>
    </AdaptationSet>
  </Period>
</MPD>
XML
    );

    $manifest = new DynamicDASHManifest('local');
    $manifest->open('manifest.mpd');

    $result = $manifest->get();

    // Without resolvers, SegmentTemplate stays (no media resolver to trigger expansion)
    expect($result)->toContain('initialization="init.mp4"')
        ->and($result)->toContain('media="seg_$Number$.mp4"');
});
