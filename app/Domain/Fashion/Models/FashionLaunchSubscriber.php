<?php

namespace App\Domain\Fashion\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'whatsapp_number', 'source', 'subscribed_at'])]
class FashionLaunchSubscriber extends Model
{
    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
        ];
    }
}
