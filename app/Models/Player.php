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
 * Class Player
 * 
 * @property int $id
 * @property int $game_id
 * @property string $name
 * @property string $password
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Player extends Model
{
	protected $table = 'players';

	protected $casts = [
		'created_at' => 'datetime:d/m/Y à H:i',
		'game_id' => 'int'
	];

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'game_id',
		'name',
		'password'
	];

	public function game(): BelongsTo
	{
		return $this->belongsTo(Game::class);
	}

	public function gameSaves(): HasMany
	{
		return $this->hasMany(GameSave::class);
	}

	protected static function booted(): void
	{
		static::deleting(function (Player $player) {
			foreach ($player->gameSaves as $save) {
				if (\Storage::exists($save->file_path)) {
					\Storage::delete($save->file_path);
				}
			}
		});
	}
}
