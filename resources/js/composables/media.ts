import type { Media, MediaCustomProperties } from '@/types'

export function useMedia(media: Media) {
  const getStreams = () => {
    const parsed = media.custom_properties as MediaCustomProperties | undefined

    return parsed?.streams
  }

  const getVideoStream = () => getStreams()?.find((stream) => stream.codec_type === 'video')

  const getAv1Stream = () => getStreams()?.find((stream) => stream.codec_name.toLowerCase() === 'av1')

  const getStreamInfo = () => {
    const stream = getVideoStream()

    if (!stream) return []

    return [
      {
        label: `${stream.width}×${stream.height}`,
        color: 'secondary' as const,
      },
      {
        label: `${Math.round(Number(stream.bit_rate) / 1000)}kbps`,
        color: 'secondary' as const,
      },
      {
        label: `${parseFloat(stream.duration).toFixed(1)}s`,
        color: 'secondary' as const,
      },
      {
        label: stream.codec_name.toUpperCase(),
        color: 'secondary' as const,
      },
      ...(stream.closed_captions > 0
        ? [
            {
              label: 'CC',
              color: 'primary' as const,
            },
          ]
        : []),
    ]
  }

  return {
    getStreams,
    getVideoStream,
    getAv1Stream,
    getStreamInfo,
  }
}
