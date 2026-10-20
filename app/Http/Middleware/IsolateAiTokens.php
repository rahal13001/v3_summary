<?php

namespace App\Http\Middleware;

use App\Models\AiAccessGrant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class IsolateAiTokens
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken() && ! $request->is('api/ai/v1', 'api/ai/v1/*') && Schema::hasTable('personal_access_tokens')) {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            if ($token && (in_array('ai:read', $token->abilities ?? [], true)
                || (Schema::hasTable('ai_access_grants') && AiAccessGrant::where('personal_access_token_id', $token->id)->exists()))) {
                return response()->json(['error' => ['code' => 'AI_TOKEN_ISOLATED', 'message' => 'Token AI hanya berlaku pada API AI.']], 403);
            }
        }

        return $next($request);
    }
}
