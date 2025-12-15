<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3'
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'

import type { Bot, BotLoopData, BotLog, Order, BotSignal } from '@/types/trading'

const props = defineProps<{
  botId: number
}>()

const bot = ref<Bot | null>(null)
const logs = ref<BotLog[]>([])
const orders = ref<Order[]>([])
const signals = ref<BotSignal[]>([])

const loading = ref(false)
const error = ref<string | null>(null)
const lastUpdated = ref<string | null>(null)

let intervalId: number | null = null

const fetchLoopData = async () => {
  loading.value = true
  error.value = null

  try {
    const { data } = await axios.get<BotLoopData>(`/api/bots/${props.botId}/loop-data`)
    
    bot.value = data.bot
    logs.value = data.logs
    orders.value = data.orders
    signals.value = data.signals
    lastUpdated.value = new Date().toLocaleTimeString()
  } catch (e) {
    console.error(e)
    error.value = 'Error cargando datos del bot'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchLoopData()
  intervalId = window.setInterval(fetchLoopData, 5000) // refresca cada 5s
})

onBeforeUnmount(() => {
  if (intervalId !== null) {
    clearInterval(intervalId)
  }
})
</script>

<template>
  <AuthenticatedLayout>
    <Head :title="bot ? `Bot: ${bot.name}` : 'Bot'" />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ bot ? `Bot: ${bot.name}` : 'Bot' }}
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- RESUMEN -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium">Resumen</h3>
              <div class="text-sm text-gray-500">
                <span v-if="loading">Actualizando...</span>
                <span v-else-if="lastUpdated">Última actualización: {{ lastUpdated }}</span>
              </div>
            </div>

            <div v-if="bot" class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
              <div>
                <div class="text-gray-500">Símbolo</div>
                <div class="font-medium">{{ bot.symbol }}</div>
              </div>
              <div>
                <div class="text-gray-500">Cuenta</div>
                <div class="font-medium">
                  {{ bot.exchange_account?.name ?? bot.exchange_account_id }}
                </div>
              </div>
              <div>
                <div class="text-gray-500">Estrategia</div>
                <div class="font-medium">
                  {{ bot.strategy?.name ?? bot.strategy_id }}
                </div>
              </div>
              <div>
                <div class="text-gray-500">Modo</div>
                <div class="font-medium uppercase">{{ bot.mode }}</div>
              </div>
              <div>
                <div class="text-gray-500">Estado</div>
                <div class="font-medium">{{ bot.status }}</div>
              </div>
              <div>
                <div class="text-gray-500">Tamaño base (USD)</div>
                <div class="font-medium">{{ bot.base_order_size ?? '—' }}</div>
              </div>
            </div>

            <p v-if="error" class="mt-3 text-sm text-red-600">
              {{ error }}
            </p>
          </div>
        </div>

        <!-- LOGS -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium">Logs recientes</h3>
              <span class="text-xs text-gray-500">Últimos {{ logs.length }} registros</span>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Nivel</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Código</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Mensaje</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="log in logs" :key="log.id">
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ log.created_at }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                      <span
                        class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold"
                        :class="{
                          'bg-green-100 text-green-800': log.level === 'info',
                          'bg-yellow-100 text-yellow-800': log.level === 'warning',
                          'bg-red-100 text-red-800': log.level === 'error',
                          'bg-gray-100 text-gray-800': !['info','warning','error'].includes(log.level)
                        }"
                      >
                        {{ log.level }}
                      </span>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ log.code ?? '—' }}
                    </td>
                    <td class="px-3 py-2 text-gray-900">
                      {{ log.message }}
                    </td>
                  </tr>
                  <tr v-if="logs.length === 0">
                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                      Aún no hay logs para este bot.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ÓRDENES -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium">Órdenes recientes</h3>
              <span class="text-xs text-gray-500">Últimas {{ orders.length }}</span>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Símbolo</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Side</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider">Exec</th>
                    <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="o in orders" :key="o.id">
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ o.placed_at ?? o.created_at }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                      {{ o.symbol }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ o.side }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ o.type }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ o.status }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-right text-gray-500">
                      {{ o.quantity }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-right text-gray-500">
                      {{ o.executed_quantity }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-right text-gray-500">
                      {{ o.avg_price ?? o.price ?? '—' }}
                    </td>
                  </tr>
                  <tr v-if="orders.length === 0">
                    <td colspan="8" class="px-3 py-4 text-center text-gray-500">
                      Aún no hay órdenes para este bot.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- SEÑALES -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium">Señales recientes</h3>
              <span class="text-xs text-gray-500">Últimas {{ signals.length }}</span>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Señal</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">RSI</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">EMA fast</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">EMA slow</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">ATR</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="s in signals" :key="s.id">
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ s.signal_time }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                      {{ s.signal }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ s.price }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ s.rsi ?? '—' }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ s.ema_fast ?? '—' }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ s.ema_slow ?? '—' }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                      {{ s.atr ?? '—' }}
                    </td>
                  </tr>
                  <tr v-if="signals.length === 0">
                    <td colspan="7" class="px-3 py-4 text-center text-gray-500">
                      Aún no hay señales registradas para este bot.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
