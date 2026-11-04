<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    /**
     * メールアドレスとパスワードを確認して API トークンを発行する。
     * 画面のログインと同じく、メール認証と二要素認証 (有効な場合) も確認する。
     */
    public function store(Request $request, TwoFactorAuthenticationProvider $twoFactor): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => 'メールアドレスの認証が完了していません。',
            ]);
        }

        // 二要素認証を有効にしているユーザーは、認証アプリの 6 桁コードも必要
        if ($user->hasEnabledTwoFactorAuthentication()) {
            $code = $credentials['code'] ?? '';
            $secret = Fortify::currentEncrypter()->decrypt((string) $user->two_factor_secret);

            if ($code === '' || ! $twoFactor->verify($secret, $code)) {
                throw ValidationException::withMessages([
                    'code' => __('The provided two factor authentication code was invalid.'),
                ]);
            }
        }

        return response()->json([
            'token' => $user->createToken($credentials['device_name'])->plainTextToken,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * 今使っているトークンだけを無効にする (API でのログアウト)。
     */
    public function destroy(Request $request): Response
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->noContent();
    }
}
