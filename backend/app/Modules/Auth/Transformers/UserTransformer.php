<?php

namespace App\Modules\Auth\Transformers;

class UserTransformer
{
    private function formatDate($date): ?string
    {
        if (empty($date)) {
            return null;
        }
        
        if ($date instanceof \DateTime) {
            return $date->format('c');
        }
        
        return $date;
    }

    public function transform($user): array
    {
        return [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'email' => $user->email,
            'created_at' => $this->formatDate($user->created_at),
            'updated_at' => $this->formatDate($user->updated_at),
        ];
    }
}