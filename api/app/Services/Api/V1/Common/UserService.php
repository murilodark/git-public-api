<?php

namespace App\Services\Api\V1\Common;

use App\Models\Pessoa;
use App\Models\SysPerfil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Atualiza um usuário (Pessoa + User + Perfis)
     */
   public function update(int $id, array $data)
{
    $pessoa = Pessoa::with(['user', 'perfis'])->findOrFail($id);
    $user = $pessoa->user;

    $this->validatePermissions($pessoa, $data);

    DB::transaction(function () use ($pessoa, $user, $data) {

        $pessoa->update([
            'name' => $data['name'] ?? $pessoa->name,
            'status' => $data['status'] ?? $pessoa->status,
            'tipo_pessoa' => $data['tipo_pessoa'] ?? $pessoa->tipo_pessoa,
        ]);

        if ($user) {
            $userData = [
                'email' => $data['email'] ?? $user->email,
            ];

            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $user->update($userData);
        }

        if (array_key_exists('perfis', $data)) {
            $perfisIds = SysPerfil::whereIn('slug', $data['perfis'] ?? [])
                ->pluck('id')
                ->toArray();

            $pessoa->perfis()->sync($perfisIds);
        }
    });
    
    if ($pessoa->status !== 'ativo' && $user) {
        // Deleta todos os tokens do Sanctum. O usuário será deslogado de tudo.
        $user->tokens()->delete();
    }

    return $pessoa->load(['user', 'perfis']);
}

    public function store(array $data): Pessoa
{
    return DB::transaction(function () use ($data) {

        $this->authService->canManageUser($data['perfis'] ?? []);

        $pessoa = Pessoa::create([
            'name'        => $data['name'],
            'tipo_pessoa' => $data['tipo_pessoa'] ?? 'fisica',
            'status'      => $data['status'] ?? 'ativo',
        ]);

        $user = $pessoa->user()->create([
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        if (!empty($data['perfis'])) {
            $perfisIds = SysPerfil::whereIn('slug', $data['perfis'])->pluck('id');
            $pessoa->perfis()->attach($perfisIds);
        }

        return $pessoa->load(['user', 'perfis']);
    });
}

    /**
     * Centraliza validações de permissão
     */
    protected function validatePermissions(Pessoa $pessoa, array $data): void
    {
        $user = $pessoa->user;

        // 🔐 Valida o usuário atual
        if ($user) {
            $perfisAtuais = $pessoa->perfis->pluck('slug')->toArray();
            $this->authService->canManageUser($perfisAtuais, $user->id);
        }

        // 🔐 Valida novos perfis
        if (isset($data['perfis'])) {
            $this->authService->canManageUser($data['perfis']);
        }
    }
}