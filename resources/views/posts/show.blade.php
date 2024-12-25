@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded-lg shadow-md border border-gray-200 font-sans">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">{{ $post->title }}</h1>

<!-- Display Post Category and Status -->
<div class="text-gray-600 text-sm mb-4">
    <span class="font-semibold">Category:</span> {{ $post->category->name }}
</div>
<div class="text-gray-600 text-sm mb-6">
    <span class="font-semibold">Status:</span> {{ ucfirst($post->status) }}
</div>

<p class="text-gray-700 text-lg mb-6">{{ $post->content }}</p>




        <!-- Display Post Image if available -->
        @if($post->image)
            <div class="mb-6">
                <img src="{{ asset('storage/' . $post->image) }}" alt="Image" class="w-full h-auto rounded-lg shadow-md">
            </div>
        @endif

        <!-- Edit and Delete Actions (Visible only to the post owner) -->
        @if ($post->user_id == auth()->id())
            <div class="flex items-center space-x-4 mb-6">
                <a href="{{ route('posts.edit', $post->id) }}" class="px-4 py-2 bg-blue-500 text-white font-semibold rounded hover:bg-blue-600 transition-colors duration-200">Edit</a>

                <form action="{{ route('posts.destroy', $post->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white font-semibold rounded hover:bg-red-600 transition-colors duration-200">
                        Delete
                    </button>
                </form>
            </div>
        @endif

                <!-- Display Show Tag -->
        <div class="pt-4">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Tags</h3>
            <ul class="flex space-x-2">
                @foreach ($post->tags as $tag)
                    <li class="text-sm font-medium text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                        <a href="{{ route('posts.index', ['tag' => $tag->id]) }}">
                            {{ $tag->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Display Comments -->
        <div class="mt-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Comments</h3>
            @foreach ($post->comments as $comment)
                <div class="mb-4 p-4 border border-gray-200 rounded-lg">
                    <p class="text-gray-700 mb-2"><strong>{{ $comment->user->name }}</strong></p>
                    <p>{{ $comment->content }}</p>
                    <!-- Allow delete only for comment owner -->
                    @if ($comment->user_id == auth()->id())
                        <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Form to Add a Comment -->
        <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Add a Comment</h4>
            <form action="{{ route('comments.store', $post->id) }}" method="POST">
                @csrf
                <textarea name="content" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Write your comment..." required></textarea>
                <button type="submit" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Submit</button>
            </form>
        </div>
    </div>
@endsection
