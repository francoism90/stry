<?php

declare(strict_types=1);

namespace Domain\Groups\Enums;

enum GroupSet: string
{
    case All = 'all';
    case Discover = 'discover';
    case Favorite = 'favorite';
    case Ordered = 'ordered';
    case Longest = 'longest';
    case Shortest = 'shortest';
    case Newest = 'newest';
    case Oldest = 'oldest';
    case Recommended = 'recommended';
    case Relevant = 'relevant';
    case Saved = 'saved';
    case Tagged = 'tagged';
    case Viewed = 'viewed';

    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::Ordered => __('Ordered'),
            self::Discover => __('New to you'),
            self::Favorite => __('Favorite'),
            self::Longest => __('Longest'),
            self::Shortest => __('Shortest'),
            self::Newest => __('Newest'),
            self::Oldest => __('Oldest'),
            self::Recommended => __('Recommended'),
            self::Relevant => __('Relevant'),
            self::Saved => __('Saved'),
            self::Tagged => __('Tagged'),
            self::Viewed => __('History'),
        };
    }
}
