<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::where('status', 'published')->latest()->paginate(5);
        return view('welcome', compact('posts'));
    }
}
