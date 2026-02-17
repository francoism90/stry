import type { Media } from '@/types'

export function useMedia() {
  const getStreams = (media: Media) => media.custom_properties?.streams

  const getVideoStream = (media: Media) => getStreams(media)?.find((stream) => stream.codec_type === 'video')

  const getStreamInfo = (media: Media) => {
    const stream = getVideoStream(media)

    if (!stream) return []

    return [
      stream.codec_name?.toUpperCase(),
      `${stream.width}×${stream.height}`,
      `${Math.round(Number(stream.bit_rate) / 1000)}kbps`,
      `${parseFloat(stream.duration).toFixed(1)}s`,
      stream.closed_captions > 0 ? 'CC' : null,
    ].filter(Boolean)
  }

  return {
    getStreams,
    getVideoStream,
    getStreamInfo,
  }
}
