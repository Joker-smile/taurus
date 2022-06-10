<?php


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected function serializeDate(\DateTimeInterface $date)
    {
        return Carbon::instance($date)->toDateTimeString();
    }
}
