<?php

declare(strict_types=1);

namespace Domain\Videos\Notifications;

use Domain\Videos\Models\Video;
use Illuminate\Notifications\Notification;

class VideoExportedNotification extends Notification
{
    public function __construct(
        public Video $video,
        public bool $deleted = false,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{title: string, message: string, level: string, url: ?string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => (string) $this->video->name,
            'message' => $this->deleted
                ? __('The video has been exported and deleted.')
                : __('The video has been exported.'),
            'level' => 'success',
            'url' => $this->deleted ? null : route('videos.show', $this->video),
        ];
    }
}
