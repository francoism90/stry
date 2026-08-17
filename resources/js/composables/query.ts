import type { QueryFilter, QueryValue } from '@/types'
import { router } from '@inertiajs/vue3'
import { reactive, toValue, watchEffect, type MaybeRefOrGetter } from 'vue'

export function useQuery(options: {
  filter?: MaybeRefOrGetter<QueryFilter | undefined>
  query?: MaybeRefOrGetter<QueryValue | undefined>
  sort?: MaybeRefOrGetter<QueryValue | undefined>
  only?: string[]
  reset?: string[]
}) {
  const form = reactive({
    filter: {} as QueryFilter,
    query: null as QueryValue,
    sort: null as QueryValue,
    page: { number: 1 },
  })

  // Layouts persist across visits, so re-sync the form whenever the
  // page-provided filter/sort/query change instead of only on first mount.
  watchEffect(() => {
    form.filter = toValue(options.filter) ?? {}
    form.query = toValue(options.query) ?? null
    form.sort = toValue(options.sort) ?? null
  })

  const onSubmit = () => {
    router.reload({
      data: form,
      reset: toValue(options.reset) ?? ['items', 'page'],
      only: toValue(options.only) ?? ['items', 'page', 'filter', 'sort', 'query'],
    })
  }

  return {
    form,
    onSubmit,
  }
}
