<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
{
    return response()->json([
        'message' => 'Регистрация недоступна. Пользователей создаёт администратор.',
    ], 403);
}

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Вы вышли из системы',
        ]);
    }

    public function forgotPassword(Request $request)
{
    $data = $request->validate([
        'email' => ['required', 'email', 'exists:users,email'],
    ]);

    $user = User::where('email', $data['email'])->first();

    $code = (string) random_int(100000, 999999);

    $user->forceFill([
        'password_reset_code' => Hash::make($code),
        'password_reset_expires_at' => now()->addMinutes(10),
    ])->save();

    Mail::raw(
        "Ваш код для восстановления пароля: {$code}\n\nКод действует 10 минут.",
        function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Код восстановления пароля');
        }
    );

    return response()->json([
        'message' => 'Код восстановления отправлен на email',
    ]);
}

    public function resetPassword(Request $request)
{
    $data = $request->validate([
        'email' => ['required', 'email', 'exists:users,email'],
        'code' => ['required', 'string', 'size:6'],
        'password' => ['required', 'string', 'min:8'],
        'confirmPassword' => ['required', 'same:password'],
    ]);

    $user = User::where('email', $data['email'])->first();

    if (
        !$user->password_reset_code ||
        !$user->password_reset_expires_at ||
        now()->greaterThan($user->password_reset_expires_at) ||
        !Hash::check($data['code'], $user->password_reset_code)
    ) {
        return response()->json([
            'message' => 'Неверный или просроченный код',
        ], 422);
    }

    $user->forceFill([
        'password' => Hash::make($data['password']),
        'password_reset_code' => null,
        'password_reset_expires_at' => null,
    ])->save();

    $user->tokens()->delete();

    return response()->json([
        'message' => 'Пароль успешно изменён',
    ]);
}
}