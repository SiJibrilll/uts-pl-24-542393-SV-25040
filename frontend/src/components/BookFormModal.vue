<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200">
      <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
        <h3 class="text-base font-bold text-slate-800">
          {{ isEdit ? 'Ubah Informasi Buku' : 'Tambah Buku Baru ke Katalog' }}
        </h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
        <div v-if="serverErrors.general" class="p-3 bg-red-50 text-red-700 text-xs rounded-md">
          {{ serverErrors.general }}
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Judul Buku *</label>
          <input
            v-model="form.title"
            type="text"
            required
            class="w-full px-3 py-2 border rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500"
            :class="serverErrors.title ? 'border-red-400 bg-red-50' : 'border-slate-300'"
            placeholder="Contoh: Clean Code"
          />
          <p v-if="serverErrors.title" class="text-xs text-red-600 mt-1">{{ serverErrors.title[0] }}</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Penulis / Pengarang *</label>
            <input
              v-model="form.author"
              type="text"
              required
              class="w-full px-3 py-2 border rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500"
              :class="serverErrors.author ? 'border-red-400 bg-red-50' : 'border-slate-300'"
              placeholder="Robert C. Martin"
            />
            <p v-if="serverErrors.author" class="text-xs text-red-600 mt-1">{{ serverErrors.author[0] }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Nomor ISBN *</label>
            <input
              v-model="form.isbn"
              type="text"
              required
              class="w-full px-3 py-2 border rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono"
              :class="serverErrors.isbn ? 'border-red-400 bg-red-50' : 'border-slate-300'"
              placeholder="978-0132350884"
            />
            <p v-if="serverErrors.isbn" class="text-xs text-red-600 mt-1">{{ serverErrors.isbn[0] }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Tahun Terbit *</label>
            <input
              v-model.number="form.published_year"
              type="number"
              min="1500"
              max="2100"
              required
              class="w-full px-3 py-2 border rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500"
              :class="serverErrors.published_year ? 'border-red-400 bg-red-50' : 'border-slate-300'"
              placeholder="2024"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Harga (IDR) *</label>
            <input
              v-model.number="form.price"
              type="number"
              min="0"
              step="500"
              required
              class="w-full px-3 py-2 border rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500"
              :class="serverErrors.price ? 'border-red-400 bg-red-50' : 'border-slate-300'"
              placeholder="150000"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Stok Eksemplar *</label>
            <input
              v-model.number="form.stock"
              type="number"
              min="0"
              required
              class="w-full px-3 py-2 border rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500"
              :class="serverErrors.stock ? 'border-red-400 bg-red-50' : 'border-slate-300'"
              placeholder="10"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Deskripsi & Sinopsis</label>
          <textarea
            v-model="form.description"
            rows="3"
            class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500"
            placeholder="Ringkasan atau catatan mengenai buku ini..."
          ></textarea>
        </div>

        <div class="pt-4 border-t border-slate-200 flex justify-end space-x-3">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 border border-slate-300 rounded-md text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-medium hover:bg-indigo-700 disabled:opacity-50 transition-colors shadow-sm"
          >
            {{ submitting ? 'Menyimpan...' : (isEdit ? 'Perbarui Data' : 'Simpan Buku') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  isOpen: Boolean,
  isEdit: Boolean,
  bookData: Object,
  submitting: Boolean,
  serverErrors: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['close', 'save'])

const form = reactive({
  title: '',
  author: '',
  isbn: '',
  published_year: new Date().getFullYear(),
  price: 100000,
  stock: 10,
  description: '',
})

watch(
  () => props.bookData,
  (newVal) => {
    if (newVal && props.isEdit) {
      form.title = newVal.title || ''
      form.author = newVal.author || ''
      form.isbn = newVal.isbn || ''
      form.published_year = newVal.published_year || 2024
      form.price = Number(newVal.price) || 0
      form.stock = Number(newVal.stock) || 0
      form.description = newVal.description || ''
    } else {
      form.title = ''
      form.author = ''
      form.isbn = ''
      form.published_year = new Date().getFullYear()
      form.price = 100000
      form.stock = 10
      form.description = ''
    }
  },
  { immediate: true }
)

const handleSubmit = () => {
  emit('save', { ...form })
}
</script>
