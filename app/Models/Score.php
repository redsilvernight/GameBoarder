<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
	protected $table = 'scores';
	
	protected $casts = [
		'created_at' => 'datetime:d/m/Y à H:i',
		'player_id' => 'int',
		'leaderboard_id' => 'int',
		'score' => 'int'
	];

	protected $fillable = [
		'player_id',
		'leaderboard_id',
		'score'
	];

	protected $appends = ['player_name'];
	protected $hidden = ['player_id', 'player'];

	public function leaderboard(): BelongsTo
	{
		return $this->belongsTo(Leaderboard::class);
	}

	public function player(): BelongsTo
	{
		return $this->belongsTo(Player::class);
	}

	public function getPlayerNameAttribute()
	{
		return $this->player?->name ?? 'Joueur supprimé';
	}
}
