<?php
// app/Http/Controllers/Api/BotController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bot;
use App\Models\ExchangeAccount;
use App\Models\Strategy;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function index(Request $request)
    {
        return Bot::with(['exchangeAccount:id,name', 'strategy:id,name'])
            ->where('user_id', $request->user()->id)
            ->get();
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'exchange_account_id' => 'required|integer|exists:exchange_accounts,id',
            'strategy_id'         => 'required|integer|exists:strategies,id',
            'name'                => 'required|string|max:255',
            'symbol'              => 'required|string|max:20',
            'mode'                => 'in:live,paper',
            'base_order_size'     => 'nullable|numeric|min:0',
            'leverage'            => 'nullable|integer|min:1|max:50',
            'max_positions'       => 'nullable|integer|min:1|max:20',
            'config'              => 'nullable|array',
        ]);

        // Seguridad mínima: la cuenta y estrategia deben ser del mismo usuario
        $account = ExchangeAccount::where('id', $data['exchange_account_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $strategy = Strategy::where('id', $data['strategy_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $bot = Bot::create([
            'user_id'            => $user->id,
            'exchange_account_id'=> $account->id,
            'strategy_id'        => $strategy->id,
            'name'               => $data['name'],
            'symbol'             => strtoupper($data['symbol']),
            'mode'               => $data['mode'] ?? 'paper',
            'status'             => 'created',
            'base_order_size'    => $data['base_order_size'] ?? null,
            'leverage'           => $data['leverage'] ?? 1,
            'max_positions'      => $data['max_positions'] ?? 1,
            'config'             => $data['config'] ?? null,
        ]);

        return response()->json($bot->load(['exchangeAccount:id,name', 'strategy:id,name']), 201);
    }

    public function show(Bot $bot)
    {
        $this->authorize('view', $bot);
        return $bot->load(['exchangeAccount', 'strategy']);
    }

    public function update(Request $request, Bot $bot)
    {
        $this->authorize('update', $bot);

        $data = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'mode'            => 'sometimes|in:live,paper',
            'status'          => 'sometimes|in:created,running,paused,stopped,error',
            'base_order_size' => 'sometimes|nullable|numeric|min:0',
            'leverage'        => 'sometimes|nullable|integer|min:1|max:50',
            'max_positions'   => 'sometimes|nullable|integer|min:1|max:20',
            'config'          => 'sometimes|nullable|array',
        ]);

        $bot->update($data);

        return $bot->load(['exchangeAccount:id,name', 'strategy:id,name']);
    }

    public function destroy(Bot $bot)
    {
        $this->authorize('delete', $bot);
        $bot->delete();

        return response()->noContent();
    }
}
