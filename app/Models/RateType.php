<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'description'])]
class RateType extends Model
{
    public function userRateHistories(): HasMany
    {
        return $this->hasMany(UserRateHistory::class);
    }
}
