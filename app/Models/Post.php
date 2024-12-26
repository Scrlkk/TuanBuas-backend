<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'image', 'category_id', 'status', 'user_id'];

    public function index()
    {
        $posts = Post::latest()->get()->map(function ($post) {
            return $this->formatPostData($post);
        });

        return response()->json($posts);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return url('storage/' . $this->image);
        }
        return null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
    private function formatPostData($posts)
    {
        return [
            'id' => $posts->id,
            'title' => $posts->title,
            'content' => $posts->content,
            'image' => $posts->image ? url('storage/' . $posts->image) : null,
            'category_id' => $posts->category_id,
            'status' => $posts->status,
            'user_id' => $posts->user_id
        ];
    }
}