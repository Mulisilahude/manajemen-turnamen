<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Tournament;
use Illuminate\Database\Eloquent\Model;

trait ResolvesTournament
{
    protected function ensureOwned(?Model $model, Tournament $tournament): void
    {
        abort_unless(
            $model instanceof Model && (int) $model->tournament_id === (int) $tournament->id,
            404
        );
    }
}
