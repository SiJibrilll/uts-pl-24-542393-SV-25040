<template>
  <div class="min-h-screen bg-slate-50">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Banner -->
      <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Katalog Koleksi Buku</h2>
          <p class="text-xs text-slate-500 mt-1">
            Data dimuat secara langsung melalui RESTful API Laravel dengan autentikasi Bearer Token Sanctum.
          </p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
          <button
            @click="fetchBooks"
            class="inline-flex items-center px-3.5 py-2 border border-slate-300 shadow-sm text-xs font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-colors"
          >
            <svg class="w-4 h-4 mr-1.5 text-slate-500" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>
          <button
            @click="openAddModal"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-xs font-semibold rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-colors"
          >
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Buku
          </button>
        </div>
      </div>

      <!-- Notification Alert -->
      <div v-if="alertMessage" class="mb-6 p-4 rounded-lg flex items-center justify-between" :class="alertType === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'">
        <div class="flex items-center">
          <svg v-if="alertType === 'success'" class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <svg v-else class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm font-medium">{{ alertMessage }}</span>
        </div>
        <button @click="alertMessage = ''" class="text-slate-400 hover:text-slate-600">✕</button>
      </div>

      <!-- Search & Stats Bar -->
      <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="relative w-full sm:w-80">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari berdasarkan judul, penulis, atau ISBN..."
            class="w-full pl-9 pr-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <div class="text-xs text-slate-500">
          Total Katalog: <span class="font-bold text-slate-800">{{ books.length }}</span> buku terdaftar
        </div>
      </div>

      <!-- Books Table / Cards -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div v-if="loading && books.length === 0" class="p-12 text-center text-slate-500 text-sm">
          <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Memuat koleksi buku dari RESTful API Laravel...
        </div>

        <div v-else-if="filteredBooks.length === 0" class="p-12 text-center text-slate-500 text-sm">
          Tidak ada buku yang cocok dengan kriteria pencarian.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50 text-slate-600 text-left text-xs font-semibold uppercase tracking-wider">
              <tr>
                <th scope="col" class="px-6 py-3.5">Judul Buku & Penulis</th>
                <th scope="col" class="px-6 py-3.5">ISBN & Tahun</th>
                <th scope="col" class="px-6 py-3.5">Harga</th>
                <th scope="col" class="px-6 py-3.5">Stok</th>
                <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-sm">
              <tr v-for="book in filteredBooks" :key="book.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4">
                  <div class="font-semibold text-slate-900">{{ book.title }}</div>
                  <div class="text-xs text-slate-500 mt-0.5">Penulis: {{ book.author }}</div>
                  <div v-if="book.description" class="text-xs text-slate-400 mt-1 line-clamp-1 italic">
                    {{ book.description }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="font-mono text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded inline-block">
                    {{ book.isbn }}
                  </div>
                  <div class="text-xs text-slate-500 mt-1">Tahun: {{ book.published_year }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-800">
                  {{ formatRupiah(book.price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2.5 py-1 text-xs font-semibold rounded-full"
                    :class="book.stock > 10 ? 'bg-emerald-100 text-emerald-800' : (book.stock > 0 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')"
                  >
                    {{ book.stock }} unit
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-xs space-x-2">
                  <button
                    @click="openEditModal(book)"
                    class="text-indigo-600 hover:text-indigo-900 font-semibold px-2 py-1 rounded hover:bg-indigo-50 transition-colors"
                  >
                    Ubah
                  </button>
                  <button
                    @click="confirmDelete(book)"
                    class="text-red-600 hover:text-red-900 font-semibold px-2 py-1 rounded hover:bg-red-50 transition-colors"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Modal Form Tambah/Ubah Buku -->
    <BookFormModal
      :is-open="isModalOpen"
      :is-edit="isEditMode"
      :book-data="selectedBook"
      :submitting="submitting"
      :server-errors="formErrors"
      @close="isModalOpen = false"
      @save="handleSaveBook"
    />

    <!-- Dialog Konfirmasi Hapus -->
    <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 mx-auto flex items-center justify-center mb-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </div>
        <h3 class="text-base font-bold text-slate-900">Konfirmasi Hapus Buku</h3>
        <p class="text-xs text-slate-500 mt-2">
          Apakah Anda yakin ingin menghapus buku <strong class="text-slate-700">"{{ bookToDelete?.title }}"</strong>? Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="mt-6 flex justify-center space-x-3">
          <button
            @click="isDeleteModalOpen = false"
            class="px-4 py-2 border border-slate-300 rounded-md text-xs font-medium text-slate-700 hover:bg-slate-50"
          >
            Batal
          </button>
          <button
            @click="executeDelete"
            :disabled="deleting"
            class="px-4 py-2 bg-red-600 text-white rounded-md text-xs font-medium hover:bg-red-700 disabled:opacity-50"
          >
            {{ deleting ? 'Menghapus...' : 'Ya, Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Navbar from '../components/Navbar.vue'
import BookFormModal from '../components/BookFormModal.vue'
import apiClient from '../services/api'

const books = ref([])
const loading = ref(false)
const searchQuery = ref('')
const alertMessage = ref('')
const alertType = ref('success')

// Modal state
const isModalOpen = ref(false)
const isEditMode = ref(false)
const selectedBook = ref(null)
const submitting = ref(false)
const formErrors = ref({})

// Delete modal state
const isDeleteModalOpen = ref(false)
const bookToDelete = ref(null)
const deleting = ref(false)

const formatRupiah = (number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(number)
}

const showAlert = (message, type = 'success') => {
  alertMessage.value = message
  alertType.value = type
  setTimeout(() => {
    alertMessage.value = ''
  }, 4000)
}

// Fetch all books (Soal 2b: Tampilkan data)
const fetchBooks = async () => {
  loading.value = true
  try {
    const response = await apiClient.get('/books')
    books.value = response.data.data
  } catch (err) {
    showAlert('Gagal memuat daftar buku dari server.', 'error')
  } finally {
    loading.value = false
  }
}

const filteredBooks = computed(() => {
  if (!searchQuery.value) return books.value
  const q = searchQuery.value.toLowerCase()
  return books.value.filter(
    (b) =>
      b.title.toLowerCase().includes(q) ||
      b.author.toLowerCase().includes(q) ||
      b.isbn.toLowerCase().includes(q)
  )
})

const openAddModal = () => {
  isEditMode.value = false
  selectedBook.value = null
  formErrors.value = {}
  isModalOpen.value = true
}

const openEditModal = (book) => {
  isEditMode.value = true
  selectedBook.value = { ...book }
  formErrors.value = {}
  isModalOpen.value = true
}

// Save (Add / Update) (Soal 2b: Tambah & Ubah)
const handleSaveBook = async (formData) => {
  submitting.value = true
  formErrors.value = {}

  try {
    if (isEditMode.value) {
      await apiClient.put(`/books/${selectedBook.value.id}`, formData)
      showAlert('Data buku berhasil diperbarui!')
    } else {
      await apiClient.post('/books', formData)
      showAlert('Buku baru berhasil ditambahkan ke katalog!')
    }
    isModalOpen.value = false
    await fetchBooks()
  } catch (err) {
    if (err.response && err.response.status === 422) {
      formErrors.value = err.response.data.errors || {}
    } else {
      showAlert('Terjadi kesalahan saat menyimpan data.', 'error')
    }
  } finally {
    submitting.value = false
  }
}

// Delete confirmation & execute (Soal 2b: Hapus data)
const confirmDelete = (book) => {
  bookToDelete.value = book
  isDeleteModalOpen.value = true
}

const executeDelete = async () => {
  if (!bookToDelete.value) return
  deleting.value = true
  try {
    await apiClient.delete(`/books/${bookToDelete.value.id}`)
    showAlert(`Buku "${bookToDelete.value.title}" berhasil dihapus!`)
    isDeleteModalOpen.value = false
    await fetchBooks()
  } catch (err) {
    showAlert('Gagal menghapus buku dari server.', 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchBooks()
})
</script>
