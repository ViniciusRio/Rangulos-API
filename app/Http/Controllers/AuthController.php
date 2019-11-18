<?php

namespace App\Http\Controllers;

use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function register()
    {
        $user = $this->user->newInstance();

        $user->name = request('name');
        $user->email = request('email');
        $user->password = bcrypt(request('password'));

        if ($user->save()) {
            return response()->json([
                'success' => 'Registro efetuado com sucesso',
            ]);
        }

        return response()->json([
            'error' => 'Nao foi possivel registrar o usuario'
        ], 500);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $this->user->where('email', request('email'))->first();
        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function show()
    {
        $userAuth = JWTAuth::parseToken()->toUser();
        $user = $this->user->find($userAuth->id);

        return $user;
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
        ]);
    }

}
