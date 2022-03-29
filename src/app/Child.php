<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Child extends Model
{

    public function parent()
    {
        return $this->BelongsTo(Parent_::class);
    }

    public function driver()
    {
        return $this->BelongsTo(Driver::class);
    }

    public function school()
    {
        return $this->BelongsTo(School::class);
    }
}
