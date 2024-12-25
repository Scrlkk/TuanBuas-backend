<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\Tag;

class PostController extends Controller
{

    public function index()
    {
        $posts = Post::all();

        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        $tags = Tag::all();
        $categories = Category::all();  // Mengambil semua kategori
        return view('posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $request->validate([
          'title' => 'required|string|max:255',
        'content' => 'required|string',
        'status' => 'required|string|in:published,archived',
        'category_id' => 'required|exists:categories,id',
        'tags' => 'nullable|string',  // Validasi tag
        'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        // Mengupload gambar jika ada
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
        }

        // Membuat post baru
        $post = Post::create([
           'title' => $request->title,
           'image' => $imagePath,
        'content' => $request->content,
        'status' => $request->status,
        'user_id' => auth()->id(),
        'category_id' => $request->category_id,
        ]);

        // Memproses tag
    if ($request->tags) {
        $tags = explode(',', $request->tags);  // Memisahkan tag berdasarkan koma
        $tagIds = [];

        foreach ($tags as $tag) {
            // Trim whitespace dan pastikan tag unik
            $tag = trim($tag);

            // Menyimpan tag jika belum ada
            $existingTag = Tag::firstOrCreate(['name' => $tag]);

            // Menambahkan id tag ke array
            $tagIds[] = $existingTag->id;
        }

        // Menyambungkan post dengan tag menggunakan relasi many-to-many
        $post->tags()->sync($tagIds);
    }

    return redirect()->route('posts.index')->with('status', 'Post created successfully');
}

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $categories = Category::all();
        return view('posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
{
    $request->validate([
        'title' => 'required',
        'content' => 'required',
        'category_id' => 'required',
        'status' => 'required|in:published,archived',
        'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'tags' => 'array|nullable|string|distinct',
    ]);

    if ($request->hasFile('image')) {
        // Simpan gambar di folder 'public/images' dan dapat diakses dengan storage
        $imagePath = $request->file('image')->store('images', 'public');
        $post->image = $imagePath;
    }

    $post->update([
        'title' => $request->title,
        'content' => $request->content,
        'category_id' => $request->category_id,
        'status' => $request->status,
        'image' => $post->image,
    ]);

    // Menyimpan tags yang dipilih
    if ($request->has('tags')) {
        $post->tags()->sync($request->tags);  // Menyinkronkan tag yang dipilih
    }

    return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
}

    public function destroy(Post $post)
    {
        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }
}
