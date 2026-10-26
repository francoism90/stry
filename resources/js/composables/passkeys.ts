import { index } from '@/actions/Modules/Web/Account/Controllers/PasskeyController'
import { destroy } from '@/routes/passkey'
import { confirmation } from '@/routes/password'
import type { Passkey } from '@/types'
import { useHttp } from '@inertiajs/vue3'
import { PasskeyError, PasskeyExistsError, UserCancelledError } from '@laravel/passkeys'
import { readonly, ref } from 'vue'

export const passkeyErrorMessage = (error: unknown): string | null => {
  if (error instanceof UserCancelledError) {
    return null
  }

  if (error instanceof PasskeyExistsError) {
    return 'This device already has a passkey for your account.'
  }

  if (error instanceof PasskeyError || error instanceof Error) {
    return error.message
  }

  return 'Something went wrong, please try again.'
}

export function usePasskeys() {
  const passkeys = ref<Passkey[]>([])
  const loaded = ref(false)
  const list = useHttp<object, Passkey[]>({})
  const status = useHttp<object, { confirmed: boolean }>({})
  const remover = useHttp<object, unknown>({})

  const refresh = () =>
    list.get(index.url(), {
      onSuccess: (response) => {
        passkeys.value = response
        loaded.value = true
      },
    })

  const isPasswordConfirmed = async () => (await status.get(confirmation.url())).confirmed

  const remove = async (passkey: Passkey) => {
    await remover.delete(destroy.url(passkey.id))
    await refresh()
  }

  return {
    passkeys: readonly(passkeys),
    loaded: readonly(loaded),
    refresh,
    remove,
    isPasswordConfirmed,
  }
}
