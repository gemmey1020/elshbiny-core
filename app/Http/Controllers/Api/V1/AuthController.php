<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuoteResource;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'token_name' => ['nullable', 'string', 'max:80'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => User::ROLE_BUYER,
        ]);

        return $this->tokenResponse($user, $validated['token_name'] ?? 'elshbiny-b2b', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'token_name' => ['nullable', 'string', 'max:80'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->tokenResponse($user, $validated['token_name'] ?? 'elshbiny-b2b');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->userPayload($request->user()),
        ]);
    }

    public function myQuotes(Request $request): AnonymousResourceCollection
    {
        $quotes = Quote::query()
            ->with(['items.product.category'])
            ->whereBelongsTo($request->user())
            ->latest()
            ->paginate((int) min(max((int) $request->integer('per_page', 10), 1), 50));

        return QuoteResource::collection($quotes);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'data' => $this->userPayload($user->refresh()),
            'meta' => [
                'message' => 'Profile updated.',
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->tokens()->delete();

        return response()->json([
            'meta' => [
                'message' => 'Logged out.',
            ],
        ]);
    }

    private function tokenResponse(User $user, string $tokenName, int $status = 200): JsonResponse
    {
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'data' => [
                'user' => $this->userPayload($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], $status);
    }

    /**
     * @return array{id:int,name:string,email:string,role:string,phone:?string,company_name:?string,address:?string}
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'company_name' => $user->company_name,
            'address' => $user->address,
        ];
    }
}
