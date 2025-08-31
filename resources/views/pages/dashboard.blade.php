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
    'showOnboardingModal' => false,
    'onboardingStep' => 1,
    'onboardingCategories' => [],
    'newCategoryName' => '',
    'newCategoryColor' => '#3B82F6',
    'selectedYear' => 2025,
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

$getContributionColor = function($articles, $user) {
    if ($articles->isEmpty()) {
        return null; // No color for no articles
    }
    
    // Check if any articles are missed days
    if ($articles->contains('is_missed_day', true)) {
        return '#EF4444'; // Red for missed days
    }
    
    // Get regular articles (not missed days)
    $regularArticles = $articles->where('is_missed_day', false);
    
    if ($regularArticles->isEmpty()) {
        return null;
    }
    
    // Get unique categories for the day
    $categories = $regularArticles->pluck('category_id')->filter()->unique();
    
    if ($categories->isEmpty()) {
        // No categories - use green gradient based on count
        $count = $regularArticles->count();
        if ($count === 1) return '#10B981'; // Light green
        if ($count === 2) return '#059669'; // Medium green
        if ($count === 3) return '#047857'; // Dark green
        return '#065F46'; // Darkest green
    }
    
    if ($categories->count() === 1) {
        // Single category - use that category's color
        $category = $user->categories()->find($categories->first());
        return $category ? $category->color : '#10B981';
    }
    
    // Multiple categories - use the multiple categories color
    return $user->multiple_categories_color ?? '#F59E0B';
};

$contributionData = computed(function() use ($getContributionColor) {
    $user = auth()->user();
    if (!$user) return [];
    
    // Use the selected year for the date range
    $startDate = Carbon::create($this->selectedYear, 1, 1)->startOfYear();
    $endDate = Carbon::create($this->selectedYear, 12, 31)->endOfYear();
    
    // Get all articles read in the selected year
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
        
        $color = $getContributionColor($dayArticles, $user);
        $count = $dayArticles->where('is_missed_day', false)->count();
        $hasMissedDay = $dayArticles->contains('is_missed_day', true);
        
        $data[] = [
            'date' => $currentDate->copy(),
            'count' => $count,
            'color' => $color,
            'is_missed_day' => $hasMissedDay,
        ];
        
        $currentDate->addDay();
    }
    
    return $data;
});

$needsOnboarding = computed(function() {
    $user = auth()->user();
    return $user && !$user->has_completed_onboarding;
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

// Onboarding functions
$nextStep = function() {
    if ($this->onboardingStep < 2) {
        $this->onboardingStep++;
    }
};

$previousStep = function() {
    if ($this->onboardingStep > 1) {
        $this->onboardingStep--;
    }
};

$skipOnboarding = function() {
    $user = auth()->user();
    $user->update([
        'has_setup_categories' => true,
        'has_completed_onboarding' => true,
    ]);
    
    $this->showOnboardingModal = false;
    $this->dispatch('success', message: 'Welcome to Articify! You can set up categories later.');
};

$completeOnboarding = function() {
    $user = auth()->user();
    
    // Mark user as completed onboarding
    $user->update([
        'has_setup_categories' => true,
        'has_completed_onboarding' => true,
    ]);
    
    $this->showOnboardingModal = false;
    $this->dispatch('success', message: 'Welcome to Articify! Your account is now set up.');
};

$addCategory = function() {
    $this->validate([
        'newCategoryName' => ['required', 'string', 'max:255'],
        'newCategoryColor' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
    ]);
    
    // Check if name already exists
    $existingNames = collect($this->onboardingCategories)->pluck('name')->toArray();
    if (in_array($this->newCategoryName, $existingNames)) {
        $this->addError('newCategoryName', 'A category with this name already exists.');
        return;
    }
    
    $this->onboardingCategories[] = [
        'name' => $this->newCategoryName,
        'color' => $this->newCategoryColor,
    ];
    
    $this->reset(['newCategoryName', 'newCategoryColor']);
    $this->newCategoryColor = '#3B82F6';
};

$removeCategory = function($index) {
    unset($this->onboardingCategories[$index]);
    $this->onboardingCategories = array_values($this->onboardingCategories);
};



?>

<div>
<div class="min-h-screen">
    <style>
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
                Articify Dashboard
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Track your daily academic reading progress with an activity graph
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
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Reading Activity
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Your reading activity for the selected year. Each square represents a day. Green squares indicate articles without categories, colored squares show category colors, and red squares indicate missed reading days.
                </p>
                
                <!-- Horizontal Year Buttons -->
                <div class="flex items-center space-x-1 mb-4">
                    @php
                        $availableYears = [2024, 2025];
                    @endphp
                    @foreach($availableYears as $year)
                        <button 
                            wire:click="$set('selectedYear', {{ $year }})"
                            class="px-3 py-1 text-sm rounded transition-all duration-200 ease-in-out {{ $selectedYear == $year ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                        >
                            {{ $year }}
                        </button>
                    @endforeach
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <div class="inline-block min-w-full">
                    <!-- Integrated Calendar Grid with Month Labels -->
                    <div class="flex flex-wrap items-start gap-1 p-2">
                        @php
                            $contributionData = $this->contributionData;
                            $currentMonth = null;
                            $blockIndex = 0;
                        @endphp
                        
                        @foreach($contributionData as $index => $day)
                            @php
                                $dayMonth = $day['date']->format('M');
                                $showMonthLabel = ($currentMonth !== $dayMonth);
                                $currentMonth = $dayMonth;
                                
                                $bgColor = $day['color'] ? '' : 'bg-gray-100 dark:bg-gray-700';
                                $customStyle = $day['color'] ? "background-color: {$day['color']};" : '';
                            @endphp
                            
                            @if($showMonthLabel)
                                <!-- Month Label -->
                                <div class="flex items-center justify-center text-xs text-gray-500 dark:text-gray-400 font-medium h-3 px-2 whitespace-nowrap">
                                    {{ $dayMonth }}
                                </div>
                            @endif
                            
                            <!-- Contribution Block -->
                            <div 
                                class="w-3 h-3 rounded-sm {{ $bgColor }} hover:scale-150 transition-all duration-200 ease-in-out cursor-pointer transform hover:z-10 hover:shadow-lg contribution-square relative"
                                style="animation-delay: {{ $blockIndex * 2 }}ms; {{ $customStyle }}"
                                title="{{ $day['date']->format('M j, Y') }}: {{ $day['is_missed_day'] ? 'Missed reading day' : ($day['count'] . ' article' . ($day['count'] !== 1 ? 's' : '') . ' read') }}"
                            ></div>
                            
                            @php
                                $blockIndex++;
                            @endphp
                        @endforeach
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center justify-end mt-4 space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-gray-100 dark:bg-gray-700 rounded-sm"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">No activity</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-sm" style="background-color: #10B981;"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">No category</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-sm" style="background-color: #F59E0B;"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Multiple categories</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-red-600 dark:bg-red-400 rounded-sm"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Missed day</span>
                        </div>
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

    <!-- Multi-Step Onboarding Modal -->
    @if($this->needsOnboarding)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-opacity-75 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-700 rounded-lg shadow-xl max-w-2xl w-full p-6">
            <!-- Step 0: Welcome -->
            @if($onboardingStep == 1)
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Welcome to Articify! 🎉
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    We're excited to help you track your academic reading progress.
                </p>
                <div class="flex justify-center space-x-3">
                    <flux:button
                        wire:click="skipOnboarding"
                        variant="ghost"
                    >
                        Skip for now
                    </flux:button>
                    <flux:button
                        wire:click="nextStep"
                        variant="primary"
                    >
                        Get Started
                    </flux:button>
                </div>
            </div>
            @endif

            <!-- Step 1: Categories Setup -->
            @if($onboardingStep == 2)
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Organize Your Articles with Categories
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Create custom categories to organize your academic articles. You can categorize by subject, research area, or any system that works for you. This helps you track your reading progress and find articles quickly.
                </p>
                <div class="mb-6">
                    <img src="/images/Step_1.png" alt="Categories Setup" class="mx-auto max-w-md h-auto rounded-lg shadow-md border-2 border-gray-200 dark:border-gray-600">
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                    Navigate to Settings → Categories to create your first category
                </p>
                <div class="flex justify-center space-x-3">
                    <flux:button
                        wire:click="previousStep"
                        variant="ghost"
                    >
                        Back
                    </flux:button>
                    <flux:button
                        wire:click="completeOnboarding"
                        variant="primary"
                    >
                        Continue
                    </flux:button>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
