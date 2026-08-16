import type { QueryFilter, QueryValue } from '@/types'
import { router } from '@inertiajs/vue3'
import { reactive, toValue, type MaybeRefOrGetter } from 'vue'

export function useQuery(options: {
  filter?: MaybeRefOrGetter<QueryFilter | undefined>
  query?: MaybeRefOrGetter<QueryValue | undefined>
  sort?: MaybeRefOrGetter<QueryValue | undefined>
  only?: string[]
  reset?: string[]
}) {
  const form = reactive({
    filter: toValue(options.filter) ?? {},
    query: toValue(options.query) ?? null,
    sort: toValue(options.sort) ?? null,
    page: { number: 1 },
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
