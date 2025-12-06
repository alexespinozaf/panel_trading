<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3'
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import type { Bot } from '@/types/trading'
import type { ExchangeAccount } from '@/types/exchange'
import type { Strategy } from '@/types/trading'

const bots = ref<Bot[]>([])
const exchangeAccounts = ref<ExchangeAccount[]>([])
const strategies = ref<Strategy[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

const form = reactive({
  name: '',
  exchange_account_id: null as number | null,
  strategy_id: null as number | null,
  symbol: 'BTCUSDT',
  mode: 'paper' as 'paper' | 'live',
  base_order_size: 10,
  leverage: 1,
  max_positions: 1,
})

const fetchAll = async () => {
  loading.value = true
  error.value = null
  try {
    const [botsRes, accRes, stratRes] = await Promise.all([
      axios.get<Bot[]>('/api/bots'),
      axios.get<ExchangeAccount[]>('/api/exchange-accounts'),
      axios.get<Strategy[]>('/api/strategies'),
    ])

    bots.value = botsRes.data
    exchangeAccounts.value = accRes.data
    strategies.value = stratRes.data
  } catch (e) {
    console.error(e)
    error.value = 'Error cargando datos'
  } finally {
    loading.value = false
  }
}

const createBot = async () => {
  error.value = null
  try {
    if (!form.exchange_account_id || !form.strategy_id) {
      error.value = 'Selecciona cuenta y estrategia'
      return
    }

    await axios.post('/api/bots', {
      ...form,
    })

    // reset parcial
    form.name = ''
    form.symbol = 'BTCUSDT'
    form.mode = 'paper'
    form.base_order_size = 10
    form.leverage = 1
    form.max_positions = 1

    await fetchAll()
  } catch (e) {
    console.error(e)
    error.value = 'Error creando bot'
  }
}

onMounted(fetchAll)
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Bots" />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Bots
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- FORM -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium mb-4">Nuevo bot</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                  placeholder="Bot BTC 15m"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Cuenta de exchange</label>
                <select
                  v-model.number="form.exchange_account_id"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                >
                  <option :value="null" disabled>Selecciona una cuenta</option>
                  <option v-for="acc in exchangeAccounts" :key="acc.id" :value="acc.id">
                    {{ acc.name }} ({{ acc.exchange }})
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Estrategia</label>
                <select
                  v-model.number="form.strategy_id"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                >
                  <option :value="null" disabled>Selecciona una estrategia</option>
                  <option v-for="s in strategies" :key="s.id" :value="s.id">
                    {{ s.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Símbolo</label>
                <input
                  v-model="form.symbol"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                  placeholder="BTCUSDT"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Modo</label>
                <select
                  v-model="form.mode"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                >
                  <option value="paper">Paper</option>
                  <option value="live">Live</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Tamaño base (USD)</label>
                <input
                  v-model.number="form.base_order_size"
                  type="number"
                  min="0"
                  step="0.01"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Leverage</label>
                <input
                  v-model.number="form.leverage"
                  type="number"
                  min="1"
                  max="50"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Máx. posiciones</label>
                <input
                  v-model.number="form.max_positions"
                  type="number"
                  min="1"
                  max="20"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
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
                @click="createBot"
              >
                Guardar bot
              </button>
            </div>

            <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>
          </div>
        </div>

        <!-- LISTA -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium">Tus bots</h3>
              <span v-if="loading" class="text-sm text-gray-500">Cargando...</span>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nombre
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Símbolo
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cuenta
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estrategia
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Modo
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estado
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="b in bots" :key="b.id">
                  <td class="px-4 py-2 text-sm text-gray-900">{{ b.name }}</td>
                  <td class="px-4 py-2 text-sm text-gray-500">{{ b.symbol }}</td>
                  <td class="px-4 py-2 text-sm text-gray-500">
                    {{ b.exchange_account?.name ?? b.exchange_account_id }}
                  </td>
                  <td class="px-4 py-2 text-sm text-gray-500">
                    {{ b.strategy?.name ?? b.strategy_id }}
                  </td>
                  <td class="px-4 py-2 text-sm text-gray-500">
                    {{ b.mode }}
                  </td>
                  <td class="px-4 py-2 text-sm text-gray-500">
                    {{ b.status }}
                  </td>
                </tr>

                <tr v-if="!loading && bots.length === 0">
                  <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">
                    Aún no tienes bots.
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
