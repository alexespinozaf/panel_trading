<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3'
import { ref, onMounted, onBeforeUnmount,computed } from 'vue'
import axios from 'axios'

import type { Bot, BotLoopData, BotLog, Order, BotSignal } from '@/types/trading'
import type { Position } from '@/types/position'

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

const position = ref<Position | null>(null)
const loadingPosition = ref(false)
const positionError = ref<string | null>(null)

const position2 = ref<any | null>(null);
const pnl = ref<any | null>(null);
const realized = ref<any | null>(null);
const loadingStats = ref(false);
const statsError = ref<string | null>(null);
async function fetchPosition() {
  loadingPosition.value = true
  positionError.value = null

  try {
    const { data } = await axios.get(`/api/bots/${props.botId}/position`)
    position.value = data.position
  } catch (e) {
    console.log(e)
    positionError.value = 'No se pudo cargar la posición actual'
  } finally {
    loadingPosition.value = false
  }
}
const pnlGross  = computed(() => position.value?.pnl_gross ?? 0)
const pnlNet    = computed(() => position.value?.pnl_net ?? 0)
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
const fetchStats = async () => {
  try {
    loadingStats.value = true;
    statsError.value = null;
    const { data } = await axios.get(`/api/bots/${props.botId}/stats`);
    position2.value = data.position;
    pnl.value = data.pnl;
    realized.value = data.realized;
  } catch (e) {
    console.error(e);
    statsError.value = 'Error cargando stats del bot';
  } finally {
    loadingStats.value = false;
  }
};
onMounted(() => {
  fetchLoopData()
   fetchStats()
   fetchPosition()
  intervalId = window.setInterval(fetchLoopData, 15000) // refresca cada 5s
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
        <!-- Ganancias-->
         <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
  <!-- PnL flotante -->
  <div class="rounded-lg border bg-white p-4 shadow-sm">
    <h3 class="mb-2 text-sm font-semibold text-gray-700">
      PnL flotante
    </h3>

    <p v-if="loadingStats" class="text-xs text-gray-500">
      Cargando stats...
    </p>

    <p v-else-if="!position" class="text-xs text-gray-500">
      Sin posición abierta.
    </p>

    <div v-else>
      <p class="text-sm text-gray-500">
        Side:
        <span
          :class="['font-semibold', position.side === 'LONG' || position.side === 'BUY'
            ? 'text-green-600'
            : 'text-red-600']"
        >
          {{ position.side }}
        </span>
      </p>
      <p class="text-sm text-gray-500">
        Qty: <span class="font-mono">{{ position.quantity }}</span>
      </p>
      <p class="text-sm text-gray-500">
        Entry: <span class="font-mono">{{ position.entry_price }}</span>
      </p>

      <div v-if="pnl" class="mt-3">
        <p class="text-sm text-gray-500">
          Último precio:
          <span class="font-mono">{{ pnl.last_price }}</span>
        </p>
        <p class="text-sm">
          PnL flotante:
          <span
            class="font-mono"
            :class="pnl.unrealized_pnl >= 0 ? 'text-green-600' : 'text-red-600'"
          >
            {{ pnl.unrealized_pnl.toFixed(4) }} USDT
            ({{ pnl.unrealized_pnl_pct.toFixed(2) }}%)
          </span>
        </p>
      </div>
    </div>
  </div>

  <!-- PnL realizado -->
  <div class="rounded-lg border bg-white p-4 shadow-sm">
    <h3 class="mb-2 text-sm font-semibold text-gray-700">
      PnL realizado
    </h3>

    <p v-if="!realized" class="text-xs text-gray-500">
      Sin trades cerrados aún.
    </p>
    <div v-else>
      <p class="text-sm text-gray-500">
        Hoy:
        <span
          class="font-mono"
          :class="realized.today >= 0 ? 'text-green-600' : 'text-red-600'"
        >
          {{ realized.today.toFixed(4) }} USDT
        </span>
      </p>
      <p class="text-sm text-gray-500">
        Total:
        <span
          class="font-mono"
          :class="realized.total >= 0 ? 'text-green-600' : 'text-red-600'"
        >
          {{ realized.total.toFixed(4) }} USDT
        </span>
      </p>
      <p class="mt-1 text-[11px] text-gray-400">
        *Ahora mismo el motor guarda realized_pnl=0; cuando implementemos
        cierre de posiciones verás aquí el resultado real.
      </p>
    </div>
  </div>
</div>
   <!-- Card Posición actual -->
<div class="bg-white rounded-xl shadow-sm p-4">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-sm font-semibold text-gray-700">
      Posición actual
    </h2>
    <button
      type="button"
      class="text-xs text-gray-500 hover:text-gray-700"
      @click="fetchPosition"
    >
      Refrescar
    </button>
  </div>

  <div v-if="loadingPosition" class="text-sm text-gray-500">
    Cargando posición...
  </div>

  <div v-else-if="positionError" class="text-sm text-red-500">
    {{ positionError }}
  </div>

  <!-- 👇 aquí garantizamos que position NO es null -->
  <div v-else-if="!position" class="text-sm text-gray-500">
    Sin posición abierta.
  </div>

  <div v-else class="grid grid-cols-2 gap-4 text-sm">
    <div>
      <div class="text-xs text-gray-500">Símbolo</div>
      <div class="font-medium">{{ position.symbol }}</div>
    </div>

    <div>
      <div class="text-xs text-gray-500">Lado</div>
      <div class="font-medium">
        <span
          :class="[
            'px-2 py-0.5 rounded-full text-xs font-semibold',
            position.side === 'LONG'
              ? 'bg-emerald-100 text-emerald-700'
              : 'bg-rose-100 text-rose-700'
          ]"
        >
          {{ position.side }}
        </span>
      </div>
    </div>

    <div>
      <div class="text-xs text-gray-500">Cantidad</div>
      <div class="font-mono">{{ position.quantity }}</div>
    </div>

    <div>
      <div class="text-xs text-gray-500">Precio entrada</div>
      <div class="font-mono">{{ position.entry_price }}</div>
    </div>

    <div>
      <div class="text-xs text-gray-500">Precio actual</div>
      <div class="font-mono">
        {{ position.last_price ?? '—' }}
      </div>
    </div>

    <div>
      <div class="text-xs text-gray-500">PnL bruto</div>
      <div
        class="font-mono"
        :class="pnlGross > 0 ? 'text-emerald-600' : (pnlGross < 0 ? 'text-rose-600' : 'text-gray-700')"
      >
        {{ position.pnl_gross?.toFixed(4) ?? '—' }}
      </div>
    </div>

    <div>
      <div class="text-xs text-gray-500">Comisiones</div>
      <div class="font-mono">
        {{ position.total_fees?.toFixed(4) ?? '—' }} USDT
      </div>
    </div>

    <div>
      <div class="text-xs text-gray-500">PnL neto</div>
      <div
        class="font-mono"
        :class="pnlNet > 0 ? 'text-emerald-700' : (pnlNet < 0 ? 'text-rose-700' : 'text-gray-700')"
      >
        {{ position.pnl_net?.toFixed(4) ?? '—' }}
      </div>
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
      </div>
    </div>
  </AuthenticatedLayout>
</template>
