<?php

namespace App\Http\Services\Api;

use App\Models\ApiKey;
use App\Models\User;
use Ramsey\Uuid\Uuid;

class UpdateApiKey
{
    public function create(User $user): ApiKey
    {
        $key = $user->apiKey;
        if (null != $key) {
            return $key;
        }

        return $user->apiKey()->forceCreate([
            'id' => Uuid::uuid4(),
            'user_id' => $user->id,
            'general_use' => 1,
        ]);
    }

    public function destroy(User $user)
    {
        $key = $user->apiKey;
        if (null != $key) {
            return $key->delete();
        }

        return true;
    }
}
