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
 * Class Leaderboard
 * 
 * @property int $id
 * @property string $name
 * @property int $game_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Leaderboard extends Model
{
	protected $table = 'leaderboards';

	protected $fillable = [
		'name',
		'game_id',
		'is_unique'
	];

	protected $casts = [
		'is_unique' => 'boolean'
	];

	protected static function booted()
	{
		static::deleting(function ($leaderboard) {
			$leaderboard->scores()->delete();
		});
	}

	public function game(): BelongsTo
	{
		return $this->belongsTo(Game::class);
	}

	public function scores(): HasMany
	{
		return $this->hasMany(Score::class);
	}
}
