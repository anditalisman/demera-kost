<?php

namespace App\Domain\Platform\Models;

use App\Enums\NotificationChannel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name', 'channel', 'subject', 'body_template', 'is_active'])]
class NotificationTemplate extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'is_active' => 'boolean',
        ];
    }

    public function render(array $placeholders): string
    {
        $body = $this->body_template;

        foreach ($placeholders as $key => $value) {
            $body = str_replace('{{'.$key.'}}', (string) $value, $body);
        }

        return $body;
    }
}
