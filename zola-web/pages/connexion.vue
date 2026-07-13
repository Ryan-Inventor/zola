<template>
  <div class="layout">
    <div class="brand-panel">
      <div style="margin-bottom: 8px">
        <img class="logo" src="/pwa-192x192.png" alt="Zola">
      </div>
      <div class="hero">
        <h1>La gestion de vos points, <span>enfin simple.</span></h1>
        <div class="feature-row">
          <div class="ic">◎</div>
          <div class="txt">
            <h4>Visibilité en temps réel</h4>
            <p>Float électronique et cash, par opérateur, à jour à chaque transaction.</p>
          </div>
        </div>
        <div class="feature-row">
          <div class="ic">✓</div>
          <div class="txt">
            <h4>Vérification du bénéficiaire</h4>
            <p>Évitez les erreurs de transaction avant qu'elles ne se produisent.</p>
          </div>
        </div>
        <div class="feature-row">
          <div class="ic">▤</div>
          <div class="txt">
            <h4>Rapports et traçabilité</h4>
            <p>Comparez vos points, détectez les écarts, exportez vos données.</p>
          </div>
        </div>
      </div>
      <div class="foot">© 2026 Zola — un produit rnexx</div>
    </div>

    <div class="form-panel">
      <div class="form-box">
        <img class="mobile-logo" src="/pwa-192x192.png" alt="Zola">
        <h2>Connexion</h2>
        <div class="sub">Accédez à la gestion de vos points.</div>

        <div v-if="errorMessage" class="error-msg">
          {{ errorMessage }}
        </div>

        <form @submit.prevent="handleSubmit">
          <div class="field">
            <label for="identifier">Email ou téléphone</label>
            <input
              id="identifier"
              v-model="identifier"
              type="text"
              placeholder="ex@email.com ou 6XX XXX XXX"
              autocomplete="username"
              :class="{ error: fieldErrors.identifier }"
              :disabled="isLoading"
            >
          </div>

          <div class="field">
            <label for="password">Mot de passe</label>
            <div class="input-wrap">
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="••••••••"
                autocomplete="current-password"
                :class="{ error: fieldErrors.password }"
                :disabled="isLoading"
              >
              <button
                type="button"
                class="toggle-pass"
                :disabled="isLoading"
                @click="showPassword = !showPassword"
              >
                {{ showPassword ? 'Masquer' : 'Afficher' }}
              </button>
            </div>
          </div>

          <div class="row-between">
            <label class="checkbox-row">
              <input v-model="rememberMe" type="checkbox" style="width: auto">
              Rester connecté
            </label>
            <NuxtLink class="link" to="/mot-de-passe-oublie">Mot de passe oublié ?</NuxtLink>
          </div>

          <button type="submit" class="btn primary" :class="{ loading: isLoading }" :disabled="isLoading">
            <span class="spinner" />
            <span class="btn-label">Se connecter</span>
          </button>
        </form>

        <div class="bottom-note">
          Pas encore de compte ?
          <NuxtLink class="link" to="/creation-compte">Créer un compte Owner</NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
})

const { login, getDashboardRoute } = useAuth()
const route = useRoute()

const identifier = ref('')
const password = ref('')
const rememberMe = ref(false)
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const fieldErrors = reactive({
  identifier: false,
  password: false,
})

async function handleSubmit(): Promise<void> {
  errorMessage.value = ''
  fieldErrors.identifier = false
  fieldErrors.password = false

  if (!identifier.value.trim() || !password.value.trim()) {
    if (!identifier.value.trim()) {
      fieldErrors.identifier = true
    }
    if (!password.value.trim()) {
      fieldErrors.password = true
    }
    errorMessage.value = 'Veuillez remplir tous les champs.'
    return
  }

  isLoading.value = true

  try {
    const user = await login(identifier.value.trim(), password.value)

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : null
    const target = redirect && redirect !== '/connexion'
      ? redirect
      : getDashboardRoute(user.role)

    await navigateTo(target)
  } catch (error) {
    fieldErrors.identifier = true
    fieldErrors.password = true
    const message = error instanceof Error
      ? error.message
      : 'Identifiants incorrects. Vérifiez votre email/téléphone et votre mot de passe.'
    errorMessage.value = message.startsWith('⚠') ? message : `⚠ ${message}`
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
:root {
  --orange: #f56001;
  --orange-dim: #fff1e6;
  --ink: #0a0a0a;
  --slate: #5c5c5e;
  --mist: #f4f4f5;
  --night: #111113;
  --white: #ffffff;
  --border: #e5e5e7;
  --alert: #d14343;
  --alert-bg: #fbeaea;
  --success: #1e8e5a;
}

.layout {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 1.1fr 1fr;
}

.brand-panel {
  background: var(--night);
  color: var(--white);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 56px;
  position: relative;
  overflow: hidden;
}

.brand-panel::before {
  content: '';
  position: absolute;
  width: 480px;
  height: 480px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(245, 96, 1, 0.14) 0%, rgba(245, 96, 1, 0) 60%);
  top: 160px;
  right: -280px;
  pointer-events: none;
}

.brand-panel .logo {
  height: 20px;
  width: auto;
  position: relative;
  z-index: 2;
  image-rendering: -webkit-optimize-contrast;
  display: block;
}

.brand-panel .hero {
  position: relative;
  z-index: 1;
  max-width: 420px;
}

.brand-panel .hero h1 {
  font-size: clamp(26px, 3vw, 34px);
  font-weight: 800;
  line-height: 1.25;
  letter-spacing: -0.01em;
  margin-bottom: 28px;
}

.brand-panel .hero h1 span {
  color: var(--orange);
}

.feature-row {
  display: flex;
  gap: 14px;
  margin-bottom: 20px;
  align-items: flex-start;
}

.feature-row .ic {
  width: 38px;
  height: 38px;
  border-radius: 11px;
  background: rgba(245, 96, 1, 0.15);
  color: var(--orange);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  flex-shrink: 0;
}

.feature-row .txt h4 {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 2px;
}

.feature-row .txt p {
  font-size: 13px;
  color: #b8b8ba;
  line-height: 1.4;
}

.brand-panel .foot {
  font-size: 12px;
  color: #7a7a7d;
  position: relative;
  z-index: 1;
}

.form-panel {
  background: var(--mist);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px;
}

.form-box {
  width: 100%;
  max-width: 380px;
}

.form-box .mobile-logo {
  display: none;
  height: 28px;
  width: auto;
  margin-bottom: 28px;
  image-rendering: -webkit-optimize-contrast;
}

.form-box h2 {
  font-size: 24px;
  font-weight: 800;
  margin-bottom: 6px;
  letter-spacing: -0.01em;
}

.form-box .sub {
  font-size: 14px;
  color: var(--slate);
  margin-bottom: 28px;
}

.field {
  margin-bottom: 18px;
}

.field label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: var(--ink);
  margin-bottom: 7px;
}

.input-wrap {
  position: relative;
}

input[type='text'],
input[type='password'] {
  width: 100%;
  padding: 14px 16px;
  border-radius: 13px;
  border: 1.5px solid var(--border);
  background: var(--white);
  font-family: 'Inter', sans-serif;
  font-size: 15px;
  color: var(--ink);
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

input::placeholder {
  color: #b4b4b6;
}

input:focus {
  outline: none;
  border-color: var(--orange);
  box-shadow: 0 0 0 4px var(--orange-dim);
}

input.error {
  border-color: var(--alert);
}

input.error:focus {
  box-shadow: 0 0 0 4px var(--alert-bg);
}

.toggle-pass {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 12px;
  color: var(--slate);
  font-weight: 600;
  cursor: pointer;
  background: none;
  border: none;
}

.error-msg {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--alert-bg);
  color: var(--alert);
  font-size: 13px;
  font-weight: 600;
  padding: 12px 14px;
  border-radius: 12px;
  margin-bottom: 18px;
}

.row-between {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.link {
  font-size: 13px;
  color: var(--orange);
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
}

.link:hover {
  text-decoration: underline;
}

.checkbox-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: var(--slate);
}

.btn {
  width: 100%;
  padding: 15px;
  border-radius: 13px;
  border: none;
  font-family: 'Inter', sans-serif;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: opacity 0.15s ease, transform 0.1s ease;
}

.btn.primary {
  background: var(--orange);
  color: #fff;
}

.btn.primary:hover {
  opacity: 0.92;
}

.btn.primary:active {
  transform: scale(0.99);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: none;
}

.btn.loading .spinner {
  display: inline-block;
}

.btn.loading .btn-label {
  display: none;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.bottom-note {
  text-align: center;
  font-size: 13px;
  color: var(--slate);
  margin-top: 24px;
}

@media (max-width: 900px) {
  .layout {
    grid-template-columns: 1fr;
  }

  .brand-panel {
    display: none;
  }

  .form-panel {
    padding: 24px;
    align-items: flex-start;
    padding-top: 60px;
  }

  .form-box .mobile-logo {
    display: block;
  }
}

@media (max-width: 420px) {
  .form-box h2 {
    font-size: 21px;
  }
}
</style>
