<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name'])]
class TenderType extends Model
{
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
