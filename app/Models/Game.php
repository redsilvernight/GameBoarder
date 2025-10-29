<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Game
 * 
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Game extends Model
{
	protected $table = 'games';

	protected $casts = [
		'created_at' => 'datetime:d/m/Y à H:i',
		'updated_at' => 'datetime:d/m/Y à H:i',
		'max_save_slots' => 'integer'
	];

	protected $fillable = [
		'name',
		'user_id',
		'save_mode',
		'max_save_slots'
	];

	public function gameSaves(): HasMany
	{
		return $this->hasMany(GameSave::class);
	}

	protected static function booted(): void
	{
		static::deleting(function (Game $game) {
			foreach ($game->gameSaves as $save) {
				if (\Storage::exists($save->file_path)) {
					\Storage::delete($save->file_path);
				}
			}
		});
	}


	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	public function players(): HasMany
	{
		return $this->hasMany(Player::class);
	}

	public function leaderboards(): HasMany
	{
		return $this->hasMany(Leaderboard::class);
	}
}
