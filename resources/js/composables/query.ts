import type { QueryFilter, QueryFilters } from '@/types'
import { router, useForm } from '@inertiajs/vue3'
import type { SelectMenuItem } from '@nuxt/ui'
import { computed, toValue, type MaybeRefOrGetter } from 'vue'

const isFilterActive = (value?: QueryFilter) => value === true || value === 'true'

export function useQuery(options: {
  filters?: MaybeRefOrGetter<SelectMenuItem[] | undefined>
  filter?: MaybeRefOrGetter<QueryFilters | undefined>
  query?: MaybeRefOrGetter<QueryFilter | undefined>
  sort?: MaybeRefOrGetter<QueryFilter | undefined>
  only?: string[]
  reset?: string[]
}) {
  const filterKeys = computed<string[]>(
    () => toValue(options.filters)?.map((item) => (item && typeof item === 'object' ? item.value : item)) ?? [],
  )

  const form = useForm({
    filter: Object.fromEntries(
      filterKeys.value.map((key) => [key, isFilterActive(toValue(options.filter)?.[key]) ? true : undefined]),
    ) as Record<string, true | undefined>,
    query: toValue(options.query ?? null),
    sort: toValue(options.sort),
    page: { number: 1 },
  })

  const formFilters = computed<string[]>({
    get: () => filterKeys.value.filter((key) => form.filter[key]),
    set: (values) =>
      filterKeys.value.forEach((key) => {
        form.filter[key] = values.includes(key) ? true : undefined
      }),
  })

  const onSubmit = () => {
    router.reload({
      data: form.data(),
      only: options.only ?? ['page', 'items', 'filter', 'sort', 'query'],
      reset: options.reset ?? ['page', 'items'],
    })
  }

  return {
    filterKeys,
    form,
    formFilters,
    onSubmit,
  }
}
