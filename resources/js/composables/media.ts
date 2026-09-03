import type { Media } from '@/types'

export function useMedia() {
  const getStreams = (media: Media) => media.custom_properties?.streams

  const getVideoStream = (media: Media) => getStreams(media)?.find((stream) => stream.codec_type === 'video')

  const getStreamInfo = (media: Media) => {
    const stream = getVideoStream(media)

    return [
      media.file_size,
      media.disk,
      media.codec,
      media.resolution,
      media.bitrate,
      stream ? `${parseFloat(stream.duration).toFixed(1)}s` : null,
      stream && stream.closed_captions > 0 ? 'CC' : null,
    ].filter(Boolean)
  }

  return {
    getStreams,
    getVideoStream,
    getStreamInfo,
  }
}
