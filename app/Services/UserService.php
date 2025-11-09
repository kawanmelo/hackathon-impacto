<?php

namespace App\Services;

use App\Enums\ApiStatus;
use App\Models\User;
use App\Utils\OperationResult;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class UserService
{


    public function getUser(int $id): OperationResult
    {
        $user = User::query()->find($id);
        if (!$user) {
            throw new NotFoundHttpException("User not found.");
        }
        return new OperationResult(
            ApiStatus::Success->value,
            Response::HTTP_OK,
            "user found.",
            ['user' =>  $user],
        );
    }

    /**
     * @throws AuthenticationException
     */
    public function login(array $credentials): OperationResult
    {
        if(!Auth::attempt($credentials)){
            throw new AuthenticationException('Credenciais inválidas.');
        }

        /** @var User $user */
        $user = Auth::user();
        return new OperationResult(
            ApiStatus::Success->value,
            Response::HTTP_OK,
            'Login efetuado com sucesso.',
            [
                'token' =>  $user->createToken('auth_token')->plainTextToken,
                'tokenType' =>  'Bearer',
            ]
        );
    }



    public function create(array $userData): OperationResult
    {
        return new OperationResult(
            ApiStatus::Success->value,
            Response::HTTP_CREATED,
            'Usuário criado.',
            ['user' => User::query()->create(($userData))]
        );
    }

    public function update(array $userData): OperationResult
    {
        return new OperationResult(
            ApiStatus::Success->value,
            Response::HTTP_OK,
            'Usuário atualizado.',
            ['user' => User::query()->update($userData)]
        );
    }

    public function delete(int $id): OperationResult
    {
        User::query()->delete($id);
        return new  OperationResult(
            ApiStatus::Success->value,
            Response::HTTP_OK,
            'Usuário deletado.',
            null
        );
    }

    public function assignRole(array $userData): OperationResult
    {
        /** @var User $user */
        $user = User::query()->find($userData['user_id']);
        $user->assignRole($userData['role_name']);
        return new OperationResult(
            ApiStatus::Success->value,
            Response::HTTP_OK,
            'Função associada a usuário.',
            [
                "userEmail" => $user['email'],
                "userRole" => $user->getRoleNames()[0],
            ]
        );
    }

    public function removeRole(array $userData): OperationResult
    {
        /** @var User $user */
        $user = User::query()->find($userData['user_id']);
        $user->removeRole($userData['role_name']);
        return new OperationResult(
            ApiStatus::Success->value,
            Response::HTTP_OK,
            'Função removida.',
            [
                "userEmail" => $user['email'],
                "roleRemoved" => Role::all()->pluck('name')->toArray()[0],
            ]
        );
    }


}
