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
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }
}
