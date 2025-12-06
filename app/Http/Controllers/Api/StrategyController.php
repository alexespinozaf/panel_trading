<?php
// app/Http/Controllers/Api/StrategyController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Strategy;
use Illuminate\Http\Request;

class StrategyController extends Controller
{
    public function index(Request $request)
    {
        return Strategy::where('user_id', $request->user()->id)->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'config'      => 'nullable|array',
            'is_testnet' => 'boolean',

            'is_public'   => 'boolean',
        ]);

        $strategy = Strategy::create([
            'user_id'     => $request->user()->id,
            'name'        => $data['name'],
            'type'        => $data['type'] ?? null,
            'description' => $data['description'] ?? null,
            'config'      => $data['config'] ?? null,
            'is_public'   => $data['is_public'] ?? false,
            'is_testnet' => $data['is_testnet'] ?? false,

        ]);

        return response()->json($strategy, 201);
    }

    public function show(Strategy $strategy)
    {
        $this->authorize('view', $strategy);
        return $strategy;
    }

    public function update(Request $request, Strategy $strategy)
    {
        $this->authorize('update', $strategy);

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'type'        => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'config'      => 'sometimes|nullable|array',
            'is_public'   => 'sometimes|boolean',
        ]);

        $strategy->update($data);

        return $strategy;
    }

    public function destroy(Strategy $strategy)
    {
        $this->authorize('delete', $strategy);
        $strategy->delete();

        return response()->noContent();
    }
}
