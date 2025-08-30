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
    'category_id' => '',
    'showForm' => false,
    'missedDate' => '',
    'showCategorySetup' => false,
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
    return $user->articles()->where('is_missed_day', false)->latest('read_date')->get();
});

$getContributionLevel = function($count) {
    if ($count === 0) return 0;
    if ($count === 1) return 1;  // Light green for 1 article
    if ($count === 2) return 2;  // Medium green for 2 articles
    if ($count === 3) return 3;  // Dark green for 3 articles
    return 4;  // Darkest green for 4+ articles
};

$contributionData = computed(function() use ($getContributionLevel) {
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
        $dayArticles = $articles->get($dateKey, collect());
        
        // Check if any articles for this day are missed days
        $hasMissedDay = $dayArticles->contains('is_missed_day', true);
        $regularArticles = $dayArticles->where('is_missed_day', false);
        $count = $regularArticles->count();
        
        $data[] = [
            'date' => $currentDate->copy(),
            'count' => $count,
            'level' => $getContributionLevel($count),
            'is_missed_day' => $hasMissedDay,
        ];
        
        $currentDate->addDay();
    }
    
    return $data;
});

$save = function() {
    $this->validate();
    
    auth()->user()->articles()->create([
        'title' => $this->title,
        'publication_date' => $this->publication_date,
        'url' => $this->url,
        'read_date' => $this->read_date,
        'category_id' => $this->category_id ?: null,
    ]);
    
    $this->reset(['title', 'publication_date', 'url', 'read_date', 'category_id', 'showForm']);
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

// Check if user needs category setup on page load
$user = auth()->user();
if ($user && !$user->has_setup_categories) {
    $showCategorySetup = true;
}

$markMissedDay = function() {
    $this->missedDate = Carbon::today()->format('Y-m-d');
    
    $this->validate([
        'missedDate' => ['required', 'date', 'before_or_equal:today'],
    ]);
    
    // Check if a missed day entry already exists for today
    if (auth()->user()->articles()->where('read_date', $this->missedDate)->where('is_missed_day', true)->exists()) {
        $this->dispatch('error', message: 'You have already marked today as a missed reading day.');
        return;
    }

    // Create a special "missed day" article entry
    auth()->user()->articles()->create([
        'title' => 'Missed Reading Day',
        'publication_date' => $this->missedDate,
        'url' => '#',
        'read_date' => $this->missedDate,
        'is_missed_day' => true,
    ]);
    
    $this->reset(['missedDate']);
    $this->dispatch('missed-day-marked');
    $this->dispatch('success', message: 'Today has been marked as a missed reading day!');
};

?>

<div class="min-h-screen">
    <style>
        .grid-cols-53 {
            grid-template-columns: repeat(53, 1fr);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        .animate-fade-in-scale {
            animation: fadeInScale 0.4s ease-out forwards;
        }
        
        .contribution-square {
            animation: fadeInScale 0.3s ease-out forwards;
            opacity: 0;
        }
        
        .article-item {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
        }
    </style>
    
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Article Reading Tracker
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Track your daily academic reading progress with a activity graph
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">
                Add articles you've read to build your reading history and see your progress over time
            </p>
        </div>

        <!-- Add Article Button -->
        <div class="mb-6">
            <div 
                class="transition-all duration-300 ease-in-out"
                :class="$showForm ? 'bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800' : ''"
            >
                <div class="flex flex-col sm:flex-row gap-3 items-center">
                    <flux:button 
                        wire:click="toggleForm" 
                        variant="primary"
                        class="transition-all duration-300 ease-in-out transform hover:scale-110 hover:shadow-lg"
                        :class="$showForm ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
                    >
                        <div class="flex items-center justify-center transition-all duration-300">
                            <flux:icon 
                                :name="$showForm ? 'x-mark' : 'plus'" 
                                class="w-4 h-4 mr-2 transition-all duration-300 transform"
                                :class="$showForm ? 'rotate-90' : 'rotate-0'"
                            />
                            <span class="transition-all duration-300">
                                {{ $showForm ? 'Close Form' : 'Add Article' }}
                            </span>
                        </div>
                    </flux:button>
                    
                    <flux:button 
                        wire:click="markMissedDay"
                        variant="primary"
                        icon="x-circle"
                        class="transition-all duration-300 ease-in-out hover:shadow-lg bg-red-600 hover:bg-red-700"
                    >
                        I did not read today
                    </flux:button>
                </div>
                
                @if($showForm)
                    <p class="text-sm text-blue-600 dark:text-blue-400 mt-2 text-center">
                        Fill out the form below to add your article
                    </p>
                @endif
            </div>
        </div>

        <!-- Add Article Form -->
        <div 
            x-data="{ show: @entangle('showForm') }"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95 -translate-y-4"
            x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 transform scale-95 -translate-y-4"
            class="mb-8"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Add New Article
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Track an academic article you've read. Fill in the details below to add it to your reading log.
                </p>
                
                <form wire:submit="save" class="space-y-6">
                    <!-- Article Title -->
                    <flux:field label="Article Title" required>
                        <flux:input 
                            wire:model="title" 
                            placeholder="e.g., 'Machine Learning Applications in Healthcare'"
                            error="{{ $errors->first('title') }}"
                        />
                        <flux:description>Enter the full title of the academic paper or article</flux:description>
                    </flux:field>

                    <!-- Article URL -->
                    <flux:field label="Article URL" required>
                        <flux:input 
                            type="url" 
                            wire:model="url" 
                            placeholder="https://doi.org/10.1000/example or https://example.com/article"
                            error="{{ $errors->first('url') }}"
                        />
                        <flux:description>Link to the article (DOI, journal website, or research repository)</flux:description>
                    </flux:field>

                    <!-- Category -->
                    <flux:field label="Category">
                        <flux:select wire:model="category_id">
                            <option value="">No Category</option>
                            @foreach(auth()->user()->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:description>Choose a category to organize your article (optional)</flux:description>
                    </flux:field>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Publication Date -->
                        <flux:field label="Publication Date" required>
                            <flux:input 
                                type="date" 
                                wire:model="publication_date"
                                error="{{ $errors->first('publication_date') }}"
                            />
                            <flux:description>When the article was published</flux:description>
                        </flux:field>

                        <!-- Date Read -->
                        <flux:field label="Date You Read It" required>
                            <flux:input 
                                type="date" 
                                wire:model="read_date"
                                error="{{ $errors->first('read_date') }}"
                            />
                            <flux:description>When you read this article</flux:description>
                        </flux:field>
                    </div>

                    <div class="flex justify-center pt-4 border-t border-gray-200 dark:border-gray-700">
                        <flux:button 
                            type="submit" 
                            variant="primary" 
                            icon="plus"
                            class="transition-all duration-200 hover:scale-105 px-8"
                        >
                            Add Article
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Contribution Graph -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Reading Activity
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Your reading activity over the past year. Each square represents a day, with darker green indicating more articles read.
            </p>
            
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
                    <div class="grid grid-cols-53 gap-1 p-2">
                        @foreach($this->contributionData as $index => $day)
                            @php
                                $bgColor = match($day['level']) {
                                    0 => 'bg-gray-100 dark:bg-gray-700',
                                    1 => 'bg-green-200 dark:bg-green-800',
                                    2 => 'bg-green-400 dark:bg-green-600',
                                    3 => 'bg-green-600 dark:bg-green-400',
                                    4 => 'bg-green-800 dark:bg-green-200',
                                };
                                $bgColor = $day['is_missed_day'] ? 'bg-red-200 dark:bg-red-800' : $bgColor;
                            @endphp
                            <div 
                                class="w-3 h-3 rounded-sm {{ $bgColor }} hover:scale-150 transition-all duration-200 ease-in-out cursor-pointer transform hover:z-10 hover:shadow-lg contribution-square relative"
                                style="animation-delay: {{ $index * 2 }}ms;"
                                title="{{ $day['date']->format('M j, Y') }}: {{ $day['is_missed_day'] ? 'Missed reading day' : ($day['count'] . ' article' . ($day['count'] !== 1 ? 's' : '') . ' read') }}"
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
                        <div class="w-3 h-3 bg-red-600 dark:bg-red-400 rounded-sm"></div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Missed</span>
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
                    @foreach($this->articles->take(10) as $index => $article)
                        <div 
                            class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg transition-all duration-300 ease-in-out transform hover:scale-[1.02] hover:shadow-md article-item"
                            style="animation-delay: {{ $index * 100 }}ms;"
                        >
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
                                        class="text-blue-600 dark:text-blue-400 hover:underline transition-colors duration-200"
                                    >
                                        View Article
                                    </a>
                                </div>
                            </div>
                            <flux:button 
                                wire:click="deleteArticle({{ $article->id }})" 
                                variant="danger" 
                                size="sm"
                                icon="trash"
                                wire:confirm="Are you sure you want to delete this article?"
                                class="transition-all duration-200 hover:scale-110"
                            >
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

<!-- Category Setup Modal -->
@if($showCategorySetup)
    <x-category-setup-modal />
@endif
