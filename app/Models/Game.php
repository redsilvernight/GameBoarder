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
	];

	protected $fillable = [
		'name',
		'user_id'
	];

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
