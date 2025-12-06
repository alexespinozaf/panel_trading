<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3'
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import type { Strategy } from '@/types/trading'

const strategies = ref<Strategy[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

const form = reactive({
  name: '',
  type: '',
  description: '',
  // ejemplo: parámetros por defecto; en serio luego lo refinamos
  configText: '{ "timeframe": "15m", "rsi_period": 14 }',
})

const parseConfig = () => {
  try {
    return form.configText ? JSON.parse(form.configText) : null
  } catch {
    throw new Error('El JSON de config no es válido')
  }
}

const fetchStrategies = async () => {
  loading.value = true
  error.value = null
  try {
    const { data } = await axios.get<Strategy[]>('/api/strategies')
    strategies.value = data
  } catch (e) {
    console.error(e)
    error.value = 'Error cargando estrategias'
  } finally {
    loading.value = false
  }
}

const createStrategy = async () => {
  error.value = null
  try {
    const config = parseConfig()
    await axios.post('/api/strategies', {
      name: form.name,
      type: form.type || null,
      description: form.description || null,
      config,
      is_public: false,
    })

    form.name = ''
    form.type = ''
    form.description = ''
    form.configText = '{ "timeframe": "15m", "rsi_period": 14 }'

    await fetchStrategies()
  } catch (e: any) {
    console.error(e)
    error.value = e.message || 'Error creando estrategia'
  }
}

onMounted(fetchStrategies)
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Estrategias" />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Estrategias
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Form -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Nueva estrategia</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                  placeholder="RSI Mean Reversion"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Tipo</label>
                <input
                  v-model="form.type"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                  placeholder="rsi_mean_reversion"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea
                  v-model="form.description"
                  rows="2"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">
                  Config (JSON)
                </label>
                <textarea
                  v-model="form.configText"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 font-mono text-xs shadow-sm"
                />
              </div>
            </div>

            <div class="mt-4">
              <button
                type="button"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent
                       rounded-md font-semibold text-xs text-white uppercase tracking-widest
                       hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                       transition ease-in-out duration-150"
                @click="createStrategy"
              >
                Guardar estrategia
              </button>
            </div>

            <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>
          </div>
        </div>

        <!-- Lista -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium">Tus estrategias</h3>
              <span v-if="loading" class="text-sm text-gray-500">Cargando...</span>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nombre
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipo
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Pública
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="s in strategies" :key="s.id">
                  <td class="px-4 py-2 text-sm text-gray-900">{{ s.name }}</td>
                  <td class="px-4 py-2 text-sm text-gray-500">{{ s.type ?? '—' }}</td>
                  <td class="px-4 py-2 text-sm text-gray-500">
                    {{ s.is_public ? 'Sí' : 'No' }}
                  </td>
                </tr>

                <tr v-if="!loading && strategies.length === 0">
                  <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">
                    Aún no tienes estrategias.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
