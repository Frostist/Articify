<?php

use App\Models\Article;
use App\Http\Requests\StoreArticleRequest;
use function Livewire\Volt\{state, computed, rules};
use Carbon\Carbon;

state([
    'title' => '',
    'publication_date' => '',
    'url' => '',
    'read_date' => '',
    'showForm' => false,
]);

rules([
    'title' => ['required', 'string', 'max:255'],
    'publication_date' => ['required', 'date', 'before_or_equal:today'],
    'url' => ['required', 'url', 'max:2048'],
    'read_date' => ['required', 'date', 'before_or_equal:today'],
]);

$articles = computed(function() {
    $user = auth()->user();
    if (!$user) return collect();
    return $user->articles()->latest('read_date')->get();
});

$contributionData = computed(function() {
    $user = auth()->user();
    if (!$user) return [];
    
    $startDate = Carbon::now()->subYear()->startOfYear();
    $endDate = Carbon::now()->endOfYear();
    
    // Get all articles read in the last year
    $articles = $user->articles()
        ->whereBetween('read_date', [$startDate, $endDate])
        ->get()
        ->groupBy(function($article) {
            return $article->read_date->format('Y-m-d');
        });
    
    $data = [];
    $currentDate = $startDate->copy();
    
    while ($currentDate <= $endDate) {
        $dateKey = $currentDate->format('Y-m-d');
        $count = $articles->get($dateKey, collect())->count();
        
        $data[] = [
            'date' => $currentDate->copy(),
            'count' => $count,
            'level' => $this->getContributionLevel($count),
        ];
        
        $currentDate->addDay();
    }
    
    return $data;
});

$getContributionLevel = function($count) {
    if ($count === 0) return 0;
    if ($count === 1) return 1;
    if ($count <= 3) return 2;
    if ($count <= 5) return 3;
    return 4;
};

$save = function() {
    $this->validate();
    
    auth()->user()->articles()->create([
        'title' => $this->title,
        'publication_date' => $this->publication_date,
        'url' => $this->url,
        'read_date' => $this->read_date,
    ]);
    
    $this->reset(['title', 'publication_date', 'url', 'read_date', 'showForm']);
    $this->dispatch('article-added');
};

$toggleForm = function() {
    $this->showForm = !$this->showForm;
    if ($this->showForm) {
        $this->read_date = now()->format('Y-m-d');
    }
};

$deleteArticle = function(Article $article) {
    $article->delete();
    $this->dispatch('article-deleted');
};

?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <style>
        .grid-cols-53 {
            grid-template-columns: repeat(53, 1fr);
        }
    </style>
    
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Article Reading Tracker
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Track your daily academic reading progress
            </p>
        </div>

        <!-- Add Article Button -->
        <div class="mb-6">
            <flux:button 
                wire:click="toggleForm" 
                variant="primary"
                class="mb-4"
            >
                <flux:icon name="plus" class="w-4 h-4 mr-2" />
                {{ $showForm ? 'Cancel' : 'Add Article' }}
            </flux:button>
        </div>

        <!-- Add Article Form -->
        @if($showForm)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Add New Article
            </h2>
            
            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field label="Article Title" required>
                        <flux:input 
                            wire:model="title" 
                            placeholder="Enter article title"
                            error="{{ $errors->first('title') }}"
                        />
                    </flux:field>

                    <flux:field label="Publication Date" required>
                        <flux:input 
                            type="date" 
                            wire:model="publication_date"
                            error="{{ $errors->first('publication_date') }}"
                        />
                    </flux:field>

                    <flux:field label="Article URL" required>
                        <flux:input 
                            type="url" 
                            wire:model="url" 
                            placeholder="https://example.com/article"
                            error="{{ $errors->first('url') }}"
                        />
                    </flux:field>

                    <flux:field label="Date Read" required>
                        <flux:input 
                            type="date" 
                            wire:model="read_date"
                            error="{{ $errors->first('read_date') }}"
                        />
                    </flux:field>
                </div>

                <div class="flex justify-end space-x-3">
                    <flux:button 
                        type="button" 
                        wire:click="toggleForm" 
                        variant="ghost"
                    >
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        Save Article
                    </flux:button>
                </div>
            </form>
        </div>
        @endif

        <!-- Contribution Graph -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Reading Activity
            </h2>
            
            <div class="overflow-x-auto">
                <div class="inline-block min-w-full">
                    <!-- Month Labels -->
                    <div class="flex mb-2">
                        @php
                            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            $currentYear = now()->year;
                        @endphp
                        @foreach($months as $index => $month)
                            @php
                                $monthDate = Carbon::create($currentYear, $index + 1, 1);
                                $weeksInMonth = $monthDate->daysInMonth > 28 ? 5 : 4;
                            @endphp
                            <div class="text-xs text-gray-500 dark:text-gray-400" style="width: {{ $weeksInMonth * 14 }}px;">
                                {{ $month }}
                            </div>
                        @endforeach
                    </div>

                    <!-- Contribution Grid -->
                    <div class="grid grid-cols-53 gap-1">
                        @foreach($this->contributionData as $day)
                            @php
                                $bgColor = match($day['level']) {
                                    0 => 'bg-gray-100 dark:bg-gray-700',
                                    1 => 'bg-green-200 dark:bg-green-800',
                                    2 => 'bg-green-400 dark:bg-green-600',
                                    3 => 'bg-green-600 dark:bg-green-400',
                                    4 => 'bg-green-800 dark:bg-green-200',
                                };
                            @endphp
                            <div 
                                class="w-3 h-3 rounded-sm {{ $bgColor }} hover:scale-125 transition-transform cursor-pointer"
                                title="{{ $day['date']->format('M j, Y') }}: {{ $day['count'] }} article{{ $day['count'] !== 1 ? 's' : '' }} read"
                            ></div>
                        @endforeach
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center justify-end mt-4 space-x-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Less</span>
                        <div class="flex space-x-1">
                            <div class="w-3 h-3 bg-gray-100 dark:bg-gray-700 rounded-sm"></div>
                            <div class="w-3 h-3 bg-green-200 dark:bg-green-800 rounded-sm"></div>
                            <div class="w-3 h-3 bg-green-400 dark:bg-green-600 rounded-sm"></div>
                            <div class="w-3 h-3 bg-green-600 dark:bg-green-400 rounded-sm"></div>
                            <div class="w-3 h-3 bg-green-800 dark:bg-green-200 rounded-sm"></div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">More</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Articles List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Recent Articles
            </h2>
            
            @if($this->articles->count() > 0)
                <div class="space-y-4">
                    @foreach($this->articles->take(10) as $article)
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white">
                                    {{ $article->title }}
                                </h3>
                                <div class="flex items-center space-x-4 mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    <span>Published: {{ $article->publication_date->format('M j, Y') }}</span>
                                    <span>Read: {{ $article->read_date->format('M j, Y') }}</span>
                                    <a 
                                        href="{{ $article->url }}" 
                                        target="_blank" 
                                        class="text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        View Article
                                    </a>
                                </div>
                            </div>
                            <flux:button 
                                wire:click="deleteArticle({{ $article->id }})" 
                                variant="danger" 
                                size="sm"
                                wire:confirm="Are you sure you want to delete this article?"
                            >
                                <flux:icon name="trash" class="w-4 h-4" />
                            </flux:button>
                        </div>
                    @endforeach
                </div>
                
                @if($this->articles->count() > 10)
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Showing 10 of {{ $this->articles->count() }} articles
                        </p>
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <flux:icon name="document-text" class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">
                        No articles added yet. Start tracking your reading progress!
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
