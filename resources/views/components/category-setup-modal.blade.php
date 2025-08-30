<?php

use App\Models\Category;
use function Livewire\Volt\{state, computed, rules};

state([
    'showModal' => false,
    'categories' => [],
    'newCategoryName' => '',
    'newCategoryColor' => '#3B82F6',
]);

rules([
    'newCategoryName' => ['required', 'string', 'max:255'],
    'newCategoryColor' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
]);

$defaultCategories = [
    ['name' => 'Machine Learning', 'color' => '#3B82F6'],
    ['name' => 'Web Development', 'color' => '#10B981'],
    ['name' => 'Data Science', 'color' => '#8B5CF6'],
    ['name' => 'Programming', 'color' => '#F59E0B'],
    ['name' => 'Research', 'color' => '#EF4444'],
];

$addCategory = function() {
    $this->validate();
    
    // Check if name already exists
    $existingNames = collect($this->categories)->pluck('name')->toArray();
    if (in_array($this->newCategoryName, $existingNames)) {
        $this->addError('newCategoryName', 'A category with this name already exists.');
        return;
    }
    
    $this->categories[] = [
        'name' => $this->newCategoryName,
        'color' => $this->newCategoryColor,
    ];
    
    $this->reset(['newCategoryName', 'newCategoryColor']);
    $this->newCategoryColor = '#3B82F6';
};

$removeCategory = function($index) {
    unset($this->categories[$index]);
    $this->categories = array_values($this->categories);
};

$addDefaultCategory = function($category) {
    $existingNames = collect($this->categories)->pluck('name')->toArray();
    if (!in_array($category['name'], $existingNames)) {
        $this->categories[] = $category;
    }
};

$saveCategories = function() {
    if (empty($this->categories)) {
        $this->addError('categories', 'Please add at least one category.');
        return;
    }
    
    $user = auth()->user();
    
    foreach ($this->categories as $index => $category) {
        $user->categories()->create([
            'name' => $category['name'],
            'color' => $category['color'],
            'sort_order' => $index,
        ]);
    }
    
    $user->update(['has_setup_categories' => true]);
    
    $this->showModal = false;
    $this->dispatch('categories-setup-complete');
};

$skipSetup = function() {
    $user = auth()->user();
    $user->update(['has_setup_categories' => true]);
    $this->showModal = false;
};

?>

<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modal-title">
                                Welcome to Articify! 🎉
                            </h3>
                        </div>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Let's set up some categories to help you organize your articles. You can choose from our suggestions or create your own.
                        </p>

                        <!-- Suggested Categories -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Suggested Categories</h4>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($defaultCategories as $category)
                                    <button
                                        type="button"
                                        wire:click="addDefaultCategory({{ json_encode($category) }})"
                                        class="flex items-center space-x-2 p-2 rounded border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors"
                                    >
                                        <div 
                                            class="w-3 h-3 rounded-full"
                                            style="background-color: {{ $category['color'] }}"
                                        ></div>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $category['name'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Add Custom Category -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Add Custom Category</h4>
                            <div class="flex space-x-2">
                                <div class="flex-1">
                                    <flux:input
                                        wire:model="newCategoryName"
                                        placeholder="Category name"
                                        error="{{ $errors->first('newCategoryName') }}"
                                    />
                                </div>
                                <input
                                    type="color"
                                    wire:model="newCategoryColor"
                                    class="w-12 h-10 rounded border border-gray-300 dark:border-zinc-600 cursor-pointer"
                                >
                                <flux:button
                                    wire:click="addCategory"
                                    variant="ghost"
                                    size="sm"
                                >
                                    Add
                                </flux:button>
                            </div>
                        </div>

                        <!-- Selected Categories -->
                        @if(!empty($categories))
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Your Categories</h4>
                                <div class="space-y-2">
                                    @foreach($categories as $index => $category)
                                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-zinc-700 rounded">
                                            <div class="flex items-center space-x-2">
                                                <div 
                                                    class="w-3 h-3 rounded-full"
                                                    style="background-color: {{ $category['color'] }}"
                                                ></div>
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $category['name'] }}</span>
                                            </div>
                                            <flux:button
                                                wire:click="removeCategory({{ $index }})"
                                                variant="danger"
                                                size="sm"
                                            >
                                                Remove
                                            </flux:button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @error('categories')
                            <div class="mb-4 text-sm text-red-600 dark:text-red-400">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="bg-gray-50 dark:bg-zinc-900 px-6 py-3 flex justify-between">
                        <flux:button
                            wire:click="skipSetup"
                            variant="ghost"
                        >
                            Skip for now
                        </flux:button>
                        <flux:button
                            wire:click="saveCategories"
                            variant="primary"
                        >
                            Save Categories
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
