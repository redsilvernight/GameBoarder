<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSave extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'game_id',
        'slot',
        'file_path',
        'checksum',
        'file_size',
    ];

    protected $casts = [
        'slot' => 'integer',
        'file_size' => 'integer',
    ];

    protected $appends = ['formatted_created_at', 'formatted_updated_at'];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function formattedCreatedAt(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->created_at ? $this->created_at->format('d/m/Y à H:i') : null,
        );
    }

    public function formattedUpdatedAt(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->updated_at ? $this->updated_at->format('d/m/Y à H:i') : null,
        );
    }

    protected static function booted(): void
    {
        static::deleting(function (GameSave $save) {
            if (\Storage::exists($save->file_path)) {
                \Storage::delete($save->file_path);
            }
        });
    }
}
