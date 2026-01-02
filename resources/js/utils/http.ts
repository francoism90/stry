import { router } from '@inertiajs/vue3'
import axios from 'axios'

export type ApiResponse<T> = { data: T; success?: boolean; status?: number }

export const http = axios.create({
  baseURL: import.meta.env.VITE_APP_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
  },
})

export const back = () => (window && window.history?.length > 1 ? window.history.back() : router.visit('/'))
