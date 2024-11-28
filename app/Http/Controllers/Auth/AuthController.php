<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Requests\LoginRequest;
use App\Http\Controllers\Auth\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="API для аутентифікації"
 * )
 *
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Електронна пошта користувача"),
 *     @OA\Property(property="password", type="string", format="password", example="password123", description="Пароль користувача")
 * )
 *
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     required={"name", "email", "password", "password_confirmation"},
 *     @OA\Property(property="name", type="string", example="John Doe", description="Ім'я користувача"),
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Електронна пошта користувача"),
 *     @OA\Property(property="password", type="string", format="password", example="password123", description="Пароль користувача"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="password123", description="Підтвердження пароля")
 * )
 *
 * @OA\Schema(
 *     schema="TokenResponse",
 *     type="object",
 *     @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR...", description="JWT токен доступу"),
 *     @OA\Property(property="token_type", type="string", example="bearer", description="Тип токену"),
 *     @OA\Property(property="expires_in", type="integer", example=3600, description="Термін дії токену (в секундах)")
 * )
 */
class AuthController
{

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Авторизація користувача",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успішна авторизація",
     *         @OA\JsonContent(ref="#/components/schemas/TokenResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Некоректні облікові дані"
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     summary="Отримати дані про поточного користувача",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Інформація про користувача",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1, description="ID користувача"),
     *             @OA\Property(property="name", type="string", example="John Doe", description="Ім'я користувача"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="Електронна пошта користувача")
     *         )
     *     )
     * )
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Вихід з системи",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Успішний вихід",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Оновлення токену доступу",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Новий токен доступу",
     *         @OA\JsonContent(ref="#/components/schemas/TokenResponse")
     *     )
     * )
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }



    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Реєстрація нового користувача",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Успішна реєстрація",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Користувач успішно зареєстрований"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *             )
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        $validator = $request->validated();

        $user = User::create([
            'name' => $validator['name'],
            'email' => $validator['email'],
            'password' => Hash::make($validator['password']), // Хешування пароля
        ]);

        return response()->json([
            'message' => 'Користувач успішно зареєстрований',
            'user' => $user,
        ], 201);
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * config('jwt.ttl'),
        ]);
    }
}
