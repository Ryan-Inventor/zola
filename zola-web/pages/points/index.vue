<template>
  <div class="min-h-screen bg-mist p-6 font-sans text-ink">
    <main class="mx-auto max-w-lg">
      <h1 class="text-2xl font-extrabold">Mes points</h1>
      <p class="mt-2 text-slate">Dashboard owner/superviseur — pages complètes en PT-05/PT-06.</p>
      <p v-if="user" class="mt-4 text-sm">
        Connecté en tant que <strong>{{ user.name }}</strong> ({{ user.role }}).
      </p>
    </main>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth',
})

const { user } = useAuth()
</script>
