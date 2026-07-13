import type { AuthUser, UserRole } from '~/types/auth'

export function useAuth() {
  const authStore = useAuthStore()
  const router = useRouter()

  const user = computed(() => authStore.user)
  const token = computed(() => authStore.token)
  const isAuthenticated = computed(() => authStore.isAuthenticated)

  function getDashboardRoute(role: UserRole): string {
    switch (role) {
      case 'admin':
        return '/admin/comptes'
      case 'owner':
        return '/points'
      case 'superviseur':
        return '/points'
      default:
        return '/'
    }
  }

  async function login(identifier: string, password: string): Promise<AuthUser> {
    return authStore.login(identifier, password)
  }

  async function loginAndRedirect(identifier: string, password: string): Promise<AuthUser> {
    const authenticatedUser = await login(identifier, password)
    await router.push(getDashboardRoute(authenticatedUser.role))
    return authenticatedUser
  }

  function logout(): void {
    authStore.logout()
  }

  return {
    user,
    token,
    isAuthenticated,
    login,
    loginAndRedirect,
    logout,
    getDashboardRoute,
  }
}
