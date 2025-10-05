<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
	protected $table = 'scores';
	// SUPPRIME ces deux lignes :
	// public $incrementing = false;
	// protected $primaryKey = null;

	protected $casts = [
		'player_id' => 'int',
		'leaderboard_id' => 'int',
		'score' => 'int'
	];

	protected $fillable = [
		'player_id',
		'leaderboard_id',
		'score'
	];

	public function leaderboard(): BelongsTo
	{
		return $this->belongsTo(Leaderboard::class);
	}

	public function player(): BelongsTo
	{
		return $this->belongsTo(Player::class);
	}
}
