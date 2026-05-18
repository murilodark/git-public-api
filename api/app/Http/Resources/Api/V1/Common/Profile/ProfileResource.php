<?php

namespace App\Http\Resources\Api\V1\Common\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
          return [
        'id'                => $this->id,
        'name'              => $this->name,
        'status'            => $this->status,
        'tipo_pessoa'       => $this->tipo_pessoa,
        'email'             => $this->email, // Injetado pelo Controller
        'email_verified_at' => $this->email_verified_at,
        'phone'             => $this->phone, // Injetado pelo Controller
        'phone_verified_at' => $this->phone_verified_at,
        'perfis'            => $this->perfis ? $this->perfis->pluck('slug') : [],
        'created_at'        => $this->created_at,
    ];
    }
}
