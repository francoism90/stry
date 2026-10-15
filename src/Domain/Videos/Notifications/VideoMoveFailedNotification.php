<?php

declare(strict_types=1);

namespace Domain\Videos\Notifications;

use Domain\Videos\Models\Video;
use Illuminate\Notifications\Notification;

class VideoMoveFailedNotification extends Notification
{
    public function __construct(
        public Video $video,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{title: string, message: string, level: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => (string) $this->video->name,
            'message' => __('The video could not be moved and has been kept on its disk.'),
            'level' => 'error',
            'url' => route('videos.show', $this->video),
        ];
    }
}
