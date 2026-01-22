<script setup lang="ts">
import { usePlayer } from '@/composables/player'
import { router } from '@inertiajs/vue3'
import type { ButtonProps } from '@nuxt/ui'
import shaka from 'shaka-player'
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'

const videoElement = ref<HTMLVideoElement>()
const shakaPlayer = ref<shaka.Player>()
const seeked = ref(false)

const { playlist, progress, store } = usePlayer()

const actions = ref<ButtonProps[]>([
  {
    icon: 'i-lucide-refresh-cw',
    label: 'Refresh',
    color: 'neutral',
    size: 'sm',
    variant: 'subtle',
    onClick: () => router.reload({ only: ['playlist'] }),
  },
  {
    icon: 'i-lucide-flag',
    label: 'Report',
    color: 'neutral',
    size: 'sm',
    variant: 'subtle',
  },
])

const initPlayer = async () => {
  if (!videoElement.value || !playlist.value?.asset) return

  // Destroy existing player if any
  if (shakaPlayer.value) {
    await shakaPlayer.value.destroy()
  }

  // Initialize Shaka Player
  shakaPlayer.value = new shaka.Player()
  await shakaPlayer.value.attach(videoElement.value)

  // Configure Clear Key DRM
  shakaPlayer.value.configure({
    drm: {
      servers: {
        'org.w3.clearkey': playlist.value.license || '',
      },
    },
  })

  // Load the manifest
  try {
    let src: string | undefined

    if (typeof playlist.value.asset === 'string') {
      src = playlist.value.asset
    } else if (playlist.value.asset && 'src' in playlist.value.asset) {
      const assetSrc = playlist.value.asset.src
      src = typeof assetSrc === 'string' ? assetSrc : undefined
    }

    if (src) {
      await shakaPlayer.value.load(src)

      // Start playback after loading
      if (videoElement.value) {
        videoElement.value.play()
      }
    }
  } catch (error) {
    console.error('Error loading video:', error)
  }
}

const handleTimeUpdate = () => {
  if (!videoElement.value) return

  const currentTime = videoElement.value.currentTime

  // Seek to saved progress on first play
  if (!seeked.value && videoElement.value.readyState >= 2 && progress.value && progress.value > 0) {
    videoElement.value.currentTime = progress.value
    seeked.value = true
  }

  // Store current time periodically (skip first second)
  if (seeked.value && currentTime > 0.1) {
    store(currentTime)
  }
}

onMounted(() => {
  shaka.polyfill.installAll()

  // Initialize player once the video element is mounted
  if (playlist.value?.asset) {
    initPlayer()
  }
})

watch(
  () => playlist.value?.asset,
  (newAsset) => {
    if (newAsset && videoElement.value) {
      initPlayer()
    }
  },
)

onBeforeUnmount(() => {
  shakaPlayer.value?.destroy()
})
</script>

<template>
  <div class="relative w-full flex-1">
    <UEmpty
      v-if="!playlist?.valid"
      title="Preparing your video..."
      :description="
        playlist?.failed ? 'There was an error processing your video.' : 'Please wait while we get everything ready.'
      "
      icon="i-lucide-hard-drive-download"
      :actions="actions"
    />

    <video
      ref="videoElement"
      v-show="playlist?.valid || false"
      class="h-full w-full"
      controls
      autoplay
      playsinline
      crossorigin="anonymous"
      @timeupdate="handleTimeUpdate"
    />
  </div>
</template>
