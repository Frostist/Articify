<?php

use App\Models\Article;
use function Livewire\Volt\{state, computed};

state([
    'search' => '',
    'filter' => 'all', // all, read, missed
    'sortBy' => 'read_date',
    'sortOrder' => 'desc',
]);

$articles = computed(function() {
    $user = auth()->user();
    if (!$user) return collect();
    
    $query = $user->articles();
    
    // Apply search filter
    if ($this->search) {
        $query->where('title', 'like', '%' . $this->search . '%');
    }
    
    // Apply type filter
    if ($this->filter === 'read') {
        $query->where('is_missed_day', false);
    } elseif ($this->filter === 'missed') {
        $query->where('is_missed_day', true);
    }
    
    // Apply sorting
    $query->orderBy($this->sortBy, $this->sortOrder);
    
    return $query->get();
});

$totalArticles = computed(function() {
    return $this->articles->where('is_missed_day', false)->count();
});

$totalMissedDays = computed(function() {
    return $this->articles->where('is_missed_day', true)->count();
});

$deleteArticle = function(Article $article) {
    $article->delete();
    $this->dispatch('article-deleted');
};

$updateSort = function($field) {
    if ($this->sortBy === $field) {
        $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortBy = $field;
        $this->sortOrder = 'desc';
    }
};

?>

<div class="min-h-screen">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Articles
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Browse and manage your article reading history
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Articles Read</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $this->totalArticles }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Missed Days</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $this->totalMissedDays }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700 p-6 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search articles..."
                        icon="magnifying-glass"
                    />
                </div>

                <!-- Filter -->
                <div class="sm:w-48">
                    <flux:select wire:model.live="filter">
                        <option value="all">All Articles</option>
                        <option value="read">Articles Read</option>
                        <option value="missed">Missed Days</option>
                    </flux:select>
                </div>
            </div>
        </div>

        <!-- Articles Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700 overflow-hidden">
            @if($this->articles->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-800" wire:click="updateSort('title')">
                                    <div class="flex items-center space-x-1">
                                        <span>Title</span>
                                        @if($sortBy === 'title')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($sortOrder === 'asc')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                @endif
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-800" wire:click="updateSort('publication_date')">
                                    <div class="flex items-center space-x-1">
                                        <span>Publication Date</span>
                                        @if($sortBy === 'publication_date')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($sortOrder === 'asc')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                @endif
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-zinc-800" wire:click="updateSort('read_date')">
                                    <div class="flex items-center space-x-1">
                                        <span>Read Date</span>
                                        @if($sortBy === 'read_date')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($sortOrder === 'asc')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                @endif
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-zinc-700">
                            @foreach($this->articles as $article)
                                <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($article->is_missed_day)
                                                <div class="flex-shrink-0 w-2 h-2 bg-red-500 rounded-full mr-3"></div>
                                                <span class="text-gray-500 dark:text-gray-400 italic">{{ $article->title }}</span>
                                            @else
                                                <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $article->title }}
                                                    </div>
                                                    @if($article->url && $article->url !== '#')
                                                        <a href="{{ $article->url }}" target="_blank" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                                            View Article →
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $article->publication_date ? $article->publication_date->format('M j, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $article->read_date->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <flux:button
                                            wire:click="deleteArticle({{ $article->id }})"
                                            wire:confirm="Are you sure you want to delete this article?"
                                            variant="danger"
                                            size="sm"
                                        >
                                            Delete
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No articles found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if($search || $filter !== 'all')
                            Try adjusting your search or filter criteria.
                        @else
                            Get started by adding your first article from the dashboard.
                        @endif
                    </p>
                    @if($search || $filter !== 'all')
                        <div class="mt-6">
                            <flux:button
                                wire:click="$set('search', ''); $set('filter', 'all')"
                                variant="primary"
                            >
                                Clear filters
                            </flux:button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
