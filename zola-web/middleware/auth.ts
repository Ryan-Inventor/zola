export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()

  if (!authStore.isAuthenticated) {
    return navigateTo({
      path: '/connexion',
      query: to.path !== '/connexion' ? { redirect: to.fullPath } : undefined,
    })
  }
})
