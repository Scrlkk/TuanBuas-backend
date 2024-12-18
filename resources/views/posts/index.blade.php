@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto mt-10 font-sans">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800">Posts</h1>
            <a href="{{ route('posts.create') }}" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition-colors duration-200">Create New Post</a>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('posts.index') }}" class="mb-8 p-4 bg-gray-100 rounded-lg shadow-md">
            <label for="tag-filter" class="block text-gray-700 text-sm font-bold mb-2">Filter by Tag:</label>
            <div class="flex items-center space-x-4">
                <select name="tag" id="tag-filter" class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Tags</option>
                    @foreach ($allTags as $tag)
                        <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Filter</button>
            </div>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($posts as $post)
            <div class="bg-white border border-gray-200 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                @if($post->image)
                    <img src="{{ asset('storage/' . $post->image) }}" alt="Post Image" class="w-full h-48 object-cover">
                @else
                    <div class="w-full h-48 bg-gray-300 flex items-center justify-center text-gray-500">
                        <span>No Image</span>
                    </div>
                @endif

                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-2">
                        <a href="{{ route('posts.show', $post->id) }}" class="hover:text-blue-500">{{ $post->title }}</a>
                    </h2>

                    <p class="text-gray-700 mb-4">{{ Str::limit($post->content, 100) }}</p>

                    <div class="flex items-center justify-between mb-4">
                        @foreach ($post->tags as $tag)
                            <a href="{{ route('posts.index', ['tag' => $tag->id]) }}" class="text-sm font-medium text-gray-600 bg-gray-100 px-3 py-1 rounded-full hover:bg-gray-200">
                                {{ $tag->name }}
                            </a>
                        @endforeach
                        <span class="text-sm font-medium text-white bg-{{ $post->status == 'published' ? 'green' : 'red' }}-500 px-3 py-1 rounded-full">
                            {{ ucfirst($post->status) }}
                        </span>
                    </div>

                    <div class="text-sm text-gray-600 mb-4">
                        <strong>Author:</strong> {{ $post->user->name }}
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('posts.show', $post->id) }}" class="text-blue-500 hover:text-blue-700 font-semibold">Read More</a>
                        @if ($post->user_id == auth()->id())
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('posts.edit', $post->id) }}" class="text-gray-500 hover:text-blue-500 font-semibold">Edit</a>
                                <form action="{{ route('posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-semibold">Delete</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
@endsection