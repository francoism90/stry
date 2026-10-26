<script setup lang="ts">
import PasskeyCreateModal from '@/components/Passkeys/PasskeyCreateModal.vue'
import PasskeyDeleteModal from '@/components/Passkeys/PasskeyDeleteModal.vue'
import PasswordConfirmModal from '@/components/Passkeys/PasswordConfirmModal.vue'
import { usePasskeys } from '@/composables/passkeys'
import type { Passkey } from '@/types'
import { Passkeys } from '@laravel/passkeys'
import { formatTimeAgo } from '@vueuse/core'
import { onMounted, ref } from 'vue'

const { passkeys, loaded, refresh, remove, isPasswordConfirmed } = usePasskeys()

const supported = ref(false)
const confirmOpen = ref(false)
const createOpen = ref(false)
const deleteOpen = ref(false)
const deleteTarget = ref<Passkey>()

let pendingAction: (() => void) | null = null

const withConfirmedPassword = async (action: () => void) => {
  if (await isPasswordConfirmed()) {
    action()

    return
  }

  pendingAction = action
  confirmOpen.value = true
}

const onConfirmed = () => {
  pendingAction?.()
  pendingAction = null
}

const onCreate = () => withConfirmedPassword(() => (createOpen.value = true))

const onDelete = (passkey: Passkey) =>
  withConfirmedPassword(() => {
    deleteTarget.value = passkey
    deleteOpen.value = true
  })

const timeAgo = (value: string) => formatTimeAgo(new Date(value.replace(' ', 'T')))

onMounted(() => {
  supported.value = Passkeys.isSupported()

  void refresh()
})
</script>

<template>
  <div>
    <UPageCard
      title="Passkeys"
      description="Sign in with your fingerprint, face, screen lock or a security key instead of your password."
      variant="naked"
      orientation="vertical"
      :ui="{
        body: 'flex w-full flex-col gap-3',
      }"
    >
      <template #body>
        <div
          v-if="!loaded"
          class="flex flex-col gap-3"
        >
          <USkeleton
            v-for="i in 2"
            :key="i"
            class="h-12 w-full rounded-md"
          />
        </div>

        <template v-else>
          <p
            v-if="!passkeys.length"
            class="text-sm text-muted"
          >
            You haven't added any passkeys yet.
          </p>

          <ul
            v-else
            class="flex flex-col divide-y divide-default"
          >
            <li
              v-for="passkey in passkeys"
              :key="passkey.id"
              class="flex items-center gap-3 py-2"
            >
              <UIcon
                name="i-lucide-key-round"
                class="size-5 shrink-0 text-muted"
              />

              <div class="flex min-w-0 flex-1 flex-col">
                <span class="truncate text-sm font-medium text-highlighted">{{ passkey.name }}</span>
                <span class="truncate text-xs text-muted">
                  <template v-if="passkey.authenticator">{{ passkey.authenticator }} · </template>
                  Added {{ timeAgo(passkey.created_at) }}
                  <template v-if="passkey.last_used_at"> · Last used {{ timeAgo(passkey.last_used_at) }}</template>
                </span>
              </div>

              <UButton
                icon="i-lucide-trash-2"
                color="error"
                variant="ghost"
                size="sm"
                aria-label="Delete passkey"
                @click="onDelete(passkey)"
              />
            </li>
          </ul>

          <UButton
            v-if="supported"
            label="Add passkey"
            icon="i-lucide-plus"
            color="neutral"
            variant="link"
            size="sm"
            class="self-start px-0"
            @click="onCreate"
          />

          <p
            v-else
            class="text-sm text-muted"
          >
            This browser doesn't support passkeys.
          </p>
        </template>
      </template>
    </UPageCard>

    <PasswordConfirmModal
      v-model:open="confirmOpen"
      :has-passkeys="supported && passkeys.length > 0"
      @confirmed="onConfirmed"
    />

    <PasskeyCreateModal
      v-model:open="createOpen"
      @created="refresh"
    />

    <PasskeyDeleteModal
      v-model:open="deleteOpen"
      :item="deleteTarget"
      :remove="remove"
    />
  </div>
</template>
