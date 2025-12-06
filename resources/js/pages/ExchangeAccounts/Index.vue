<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';

import { Head } from '@inertiajs/vue3'
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

import type { ExchangeAccount, ExchangeAccountForm } from '@/types/exchange'

const accounts = ref<ExchangeAccount[]>([])
const loading = ref<boolean>(false)
const error = ref<string | null>(null)

// reactive para objetos es más cómodo que ref
const form = reactive<ExchangeAccountForm>({
  name: '',
  exchange: 'binance',
  api_key: '',
  api_secret: '',
  is_futures: false,
  is_testnet: false,
})

const fetchAccounts = async () => {
  loading.value = true
  error.value = null
  try {
    const { data } = await axios.get<ExchangeAccount[]>('/api/exchange-accounts')
    accounts.value = data
  } catch (e) {
    console.error(e)
    error.value = 'Error cargando cuentas'
  } finally {
    loading.value = false
  }
}

const createAccount = async () => {
  error.value = null
  try {
    // form ya tiene tipo ExchangeAccountForm
    await axios.post('/api/exchange-accounts', form)
    // reset
    form.name = ''
    form.exchange = 'binance'
    form.api_key = ''
    form.api_secret = ''
    form.is_futures = false
    form.is_testnet = false

    await fetchAccounts()
  } catch (e) {
    console.error(e)
    error.value = 'Error creando cuenta (revisa los campos)'
  }
}

onMounted(fetchAccounts)
</script>
<template>
  <AppLayout>
    <Head title="Cuentas de Exchange" />

    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Cuentas de Exchange
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- FORM -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h3 class="text-lg font-medium mb-4">Nueva cuenta</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                  placeholder="Binance principal"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Exchange</label>
                <input
                  v-model="form.exchange"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                  placeholder="binance"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">API Key</label>
                <input
                  v-model="form.api_key"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">API Secret</label>
                <input
                  v-model="form.api_secret"
                  type="password"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                />
              </div>

              <div class="flex items-center mt-2">
                <input
                  id="is_futures"
                  v-model="form.is_futures"
                  type="checkbox"
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                />
                <label for="is_futures" class="ml-2 block text-sm text-gray-700">
                  Futuros
                </label>
              </div>
              <div class="flex items-center mt-2">
                <input
                  id="is_testnet"
                  v-model="form.is_testnet"
                  type="checkbox"
                  class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                />
                <label for="is_testnet" class="ml-2 block text-sm text-gray-700">
                  demo
                </label>
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
                @click="createAccount"
              >
                Guardar cuenta
              </button>
            </div>

            <p v-if="error" class="mt-3 text-sm text-red-600">
              {{ error }}
            </p>
          </div>
        </div>

        <!-- LISTA -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium">Tus cuentas</h3>
              <span v-if="loading" class="text-sm text-gray-500">Cargando...</span>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nombre
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Exchange
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Futuros
                  </th>
                     <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Testnet
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Última sync
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="acc in accounts" :key="acc.id">
                  <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                    {{ acc.name }}
                  </td>
                  <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                    {{ acc.exchange }}
                  </td>
                  <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                      :class="acc.is_futures ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800'"
                    >
                      {{ acc.is_futures ? 'Sí' : 'No' }}
                    </span>
                  </td>
                   <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                      :class="acc.is_futures ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800'"
                    >
                      {{ acc.is_testnet ? 'Sí' : 'No' }}
                    </span>
                  </td>
                  <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                    {{ acc.status }}
                  </td>
                  <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                    {{ acc.last_synced_at ?? '—' }}
                  </td>
                </tr>

                <tr v-if="!loading && accounts.length === 0">
                  <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">
                    Aún no tienes cuentas configuradas.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
