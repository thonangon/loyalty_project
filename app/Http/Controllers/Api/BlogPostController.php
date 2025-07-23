<?php

namespace App\Http\Controllers\Api;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::where('status', 'published')->latest()->paginate(5);
        return view('welcome', compact('posts'));
    }
}
