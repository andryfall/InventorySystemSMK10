<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function show()
    {
        $balance = Balance::first();
        return response()->json($balance);
    }

    public function update(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
        ]);

        $balance = Balance::firstOrCreate([], ['amount' => 0]);
        $balance->amount = $request->amount;
        $balance->save();

        return response()->json([
            'message' => 'Balance updated.',
            'balance' => $balance,
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
        ]);

        $balance = Balance::firstOrCreate([], ['amount' => 0]);
        $balance->amount += $request->amount;
        $balance->save();

        return response()->json([
            'message' => 'Amount added to balance.',
            'balance' => $balance,
        ]);
    }
}
