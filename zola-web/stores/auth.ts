import { defineStore } from 'pinia'
import type { AuthUser, LoginSuccessResponse } from '~/types/auth'
import { getApiErrorMessage } from '~/utils/api-error'

export const useAuthStore = defineStore('auth', () => {
  const token = useCookie<string | null>('zola_token', {
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production',
    maxAge: 60 * 60 * 24 * 30,
  })

  const user = ref<AuthUser | null>(null)

  const isAuthenticated = computed(() => Boolean(token.value))

  async function login(identifier: string, password: string): Promise<AuthUser> {
    const config = useRuntimeConfig()

    try {
      const response = await $fetch<LoginSuccessResponse>(`${config.public.apiUrl}/auth/login`, {
        method: 'POST',
        body: { identifier, password },
      })

      token.value = response.data.token
      user.value = response.data.user

      return response.data.user
    } catch (error) {
      throw new Error(getApiErrorMessage(error, 'Impossible de se connecter pour le moment.'))
    }
  }

  function logout(): void {
    token.value = null
    user.value = null
  }

  function setUser(nextUser: AuthUser | null): void {
    user.value = nextUser
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    logout,
    setUser,
  }
})
