<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = ['name', 'slug', 'identifier'];

    /**
     * Get the users associated with the organization.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the blog posts associated with the organization.
     */
    
    protected static function boot(){
        parent::boot();
        static::created(function ($organization){
            $organization->identifier =  uniqid('ORG' . date('ymd'));
        });
        static::updated(function($organization){
            $organization->identifier = uniqid('ORG'. date('ymd'));
        });
    }
}
