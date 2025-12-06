<?php

// app/Http/Controllers/Api/ExchangeAccountController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExchangeAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ExchangeAccountController extends Controller
{
    public function index(Request $request)
    {
        return ExchangeAccount::where('user_id', $request->user()->id)->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'exchange' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
            'is_futures' => 'boolean',
            'is_testnet' => 'boolean',
        ]);

        $account = ExchangeAccount::create([
            'user_id' => $request->user()->id,
            'exchange' => $data['exchange'],
            'name' => $data['name'],
            'api_key_encrypted' => Crypt::encryptString($data['api_key']),
            'api_secret_encrypted' => Crypt::encryptString($data['api_secret']),
            'is_futures' => $data['is_futures'] ?? false,
            'is_testnet' => $data['is_testnet'] ?? false,
        ]);

        return response()->json($account, 201);
    }

    public function show(ExchangeAccount $exchangeAccount)
    {
        $this->authorize('view', $exchangeAccount);
        return $exchangeAccount;
    }

    public function update(Request $request, ExchangeAccount $exchangeAccount)
    {
        $this->authorize('update', $exchangeAccount);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_futures' => 'sometimes|boolean',
            'is_testnet' => 'sometimes|boolean',
            'status' => 'sometimes|in:active,disabled,error',
        ]);

        $exchangeAccount->update($data);

        return $exchangeAccount;
    }

    public function destroy(ExchangeAccount $exchangeAccount)
    {
        $this->authorize('delete', $exchangeAccount);
        $exchangeAccount->delete();

        return response()->noContent();
    }
}
