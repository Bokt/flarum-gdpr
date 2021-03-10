<?php

namespace Blomstra\Gdpr\Models;

use Flarum\Database\AbstractModel;
use Flarum\User\User;

class ErasureRequest extends AbstractModel
{
    protected $table = 'gdpr_erasure';
    protected $dates = ['created_at', 'user_confirmed_at', 'processed_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
