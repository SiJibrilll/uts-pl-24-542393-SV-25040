<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg border border-slate-200">
      <div>
        <div class="mx-auto h-14 w-14 bg-indigo-600 text-white rounded-xl flex items-center justify-center shadow-md">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <h2 class="mt-4 text-center text-2xl font-extrabold text-slate-900">
          Masuk ke Bookstore API
        </h2>
        <p class="mt-2 text-center text-xs text-slate-500">
          Ujian Tengah Semester Konstruksi dan Evolusi Perangkat Lunak
        </p>
      </div>

      <div v-if="errorMessage" class="p-3 bg-red-50 border-l-4 border-red-500 rounded text-sm text-red-700">
        {{ errorMessage }}
      </div>

      <form class="mt-8 space-y-5" @submit.prevent="handleSubmit">
        <div>
          <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            Alamat Email
          </label>
          <input
            id="email"
            v-model="email"
            name="email"
            type="email"
            required
            class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="admin@bookstore.test"
          />
        </div>

        <div>
          <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
            Kata Sandi
          </label>
          <input
            id="password"
            v-model="password"
            name="password"
            type="password"
            required
            class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            placeholder="••••••••"
          />
        </div>

        <div class="flex items-center justify-between text-xs">
          <button
            type="button"
            @click="fillDemoCredentials"
            class="text-indigo-600 hover:text-indigo-800 font-medium underline"
          >
            Gunakan Kredensial Uji
          </button>
          <span class="text-slate-400">Token Sanctum</span>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors shadow-sm"
          >
            <span v-if="loading">Memproses...</span>
            <span v-else>Masuk & Dapatkan Token</span>
          </button>
        </div>
      </form>

      <div class="pt-4 border-t border-slate-200 text-center text-xs text-slate-500">
        Mahasiswa: <span class="font-semibold text-slate-700">Jibrilian Wulsa Ariswanto</span><br />
        NIM: <span class="font-mono text-slate-700">24/542393/SV/25040</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

const fillDemoCredentials = () => {
  email.value = 'admin@bookstore.test'
  password.value = 'password123'
}

const handleSubmit = async () => {
  errorMessage.value = ''
  loading.value = true

  const result = await authStore.login(email.value, password.value)
  loading.value = false

  if (result.success) {
    router.push('/')
  } else {
    errorMessage.value = result.message || 'Login gagal, silakan periksa kredensial Anda.'
  }
}
</script>
