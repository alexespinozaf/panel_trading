<?php
// app/Http/Controllers/Api/BotController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PositionResource;
use App\Models\Bot;
use App\Models\BotLog;
use App\Models\BotSignal;
use App\Models\BotTrade;
use App\Models\Candle;
use App\Models\ExchangeAccount;
use App\Models\Order;
use App\Models\Strategy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

class BotController extends Controller
{
        use AuthorizesRequests, ValidatesRequests;
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
public function loopData(Request $request, Bot $bot)
{

    $logsLimit = min((int) $request->get('logs', 50), 200);
    $ordersLimit = min((int) $request->get('orders', 20), 100);
    $signalsLimit = min((int) $request->get('signals', 20), 100);
    $bot->load(['exchangeAccount:id,name', 'strategy:id,name']);

    $logs = BotLog::where('bot_id', $bot->id)
        ->orderByDesc('id')
        ->limit($logsLimit)
        ->get();

    $orders = Order::where('bot_id', $bot->id)
        ->orderByDesc('placed_at')
        ->orderByDesc('id')
        ->limit($ordersLimit)
        ->get();

    $signals = BotSignal::where('bot_id', $bot->id)
        ->orderByDesc('signal_time')
        ->orderByDesc('id')
        ->limit($signalsLimit)
        ->get();

    return response()->json([
        'bot' => $bot,
        'logs' => $logs,
        'orders' => $orders,
        'signals' => $signals,
    ]);
}
public function position(Bot $bot)
{

    $position = $bot->openPosition()->first();

    if (! $position) {
        return response()->json([
            'position' => null,
        ]);
    }

    // timeframe desde la estrategia (config JSON), default 15m
    $strategy = $bot->strategy;
    $timeframe = '15m';
    if ($strategy && is_array($strategy->config) && isset($strategy->config['timeframe'])) {
        $timeframe = $strategy->config['timeframe'];
    }

    // Última vela = precio actual
    $lastCandle = Candle::query()
        ->where('symbol', $position->symbol)
        ->where('timeframe', $timeframe)
        ->orderByDesc('open_time')
        ->first();

    $lastPrice = $lastCandle?->close;

    $pnlGross = null;
    $pnlNet = null;
    $totalFees = null;

    if ($lastPrice !== null) {
        $qty   = (float) $position->quantity;
        $entry = (float) $position->entry_price;
        $last  = (float) $lastPrice;

        if ($position->side === 'LONG') {
            $pnlGross = ($last - $entry) * $qty;
        } else {
            // para futuro SHORT
            $pnlGross = ($entry - $last) * $qty;
        }

        // fees desde que se abrió la posición
        $totalFees = (float) BotTrade::query()
            ->where('bot_id', $bot->id)
            ->where('symbol', $position->symbol)
            ->where('trade_time', '>=', $position->opened_at)
            ->sum('fee');

        $pnlNet = $pnlGross - $totalFees;
    }

    // 👇 atributos calculados (no columnas)
    $position->last_price = $lastPrice;
    $position->pnl_gross  = $pnlGross;
    $position->pnl_net    = $pnlNet;
    $position->total_fees = $totalFees;

    return response()->json([
        'position' => PositionResource::make($position),
    ]);
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
    public function start(Bot $bot)
{

    if ($bot->status === 'running') {
        return response()->json(['message' => 'Bot ya está en ejecución'], 409);
    }

    $bot->status = 'running';
    $bot->save();

    return response()->json($bot->fresh(['exchangeAccount', 'strategy']));
}

public function pause(Bot $bot)
{

    if ($bot->status !== 'running') {
        return response()->json(['message' => 'Bot no está en ejecución'], 409);
    }

    $bot->status = 'paused';
    $bot->save();

    return response()->json($bot->fresh(['exchangeAccount', 'strategy']));
}

public function stop(Bot $bot)
{

    // aquí puedes también cerrar posición, etc. por ahora solo marcamos stopped
    $bot->status = 'stopped';
    $bot->save();

    return response()->json($bot->fresh(['exchangeAccount', 'strategy']));
}

}
