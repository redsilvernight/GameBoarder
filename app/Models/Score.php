<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Score
 * 
 * @property int $player_id
 * @property int $leaderboard_id
 * @property int $score
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Score extends Model
{
	protected $table = 'scores';
	public $incrementing = false;
	protected $primaryKey = null;

	protected $casts = [
		'player_id' => 'int',
		'leaderboard_id' => 'int',
		'score' => 'int'
	];

	protected $fillable = [
		'player_id',
		'leaderboard_id',
		'score',
	];

	public function leaderboard(): BelongsTo
	{
		return $this->belongsTo(Leaderboard::class);
	}
}
