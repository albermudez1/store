<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'question' => $validated['question'],
            'answer' => Hash::make($validated['answer']),
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => auth('api')->user(),
        ], 200);
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Cierre de sesión exitoso.',
        ], 200);
    }

    public function me()
    {
        return response()->json([
            'user' => auth('api')->user(),
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:255',
            'new_password' => 'required|string|min:8',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        if ($user->question !== $validated['question']) {
            return response()->json([
                'message' => 'La pregunta de seguridad no coincide.',
            ], 400);
        }

        if (! Hash::check($validated['answer'], $user->answer)) {
            return response()->json([
                'message' => 'La respuesta de seguridad es incorrecta.',
            ], 400);
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ], 200);
    }

}