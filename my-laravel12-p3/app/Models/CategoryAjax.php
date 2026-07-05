<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryAjax extends Model
{
    protected $fillable = ['name', 'slug'];

    // নাম ইনপুট দিলে অটোমেটিক স্ল্যাগ (Slug) তৈরি করার জন্য মিউটেটর (Mutator)
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }
}
