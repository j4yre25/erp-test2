<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'description'])]
class AccountReceivableStatus extends Model
{
    public function accountReceivables(): HasMany
    {
        return $this->hasMany(AccountReceivable::class);
    }
}
