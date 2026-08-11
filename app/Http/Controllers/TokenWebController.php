<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TokenWebController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->user()->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'message' => 'Token berhasil disimpan'
        ], 201);
    }
}
