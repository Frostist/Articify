<?php

use App\Models\Category;
use Livewire\Volt\Component;

new class extends Component {
    public bool $showForm = false;
    public ?Category $editingCategory = null;
    public string $name = '';
    public string $color = '#3B82F6';
    public int $sortOrder = 0;

    public function mount(): void
    {
        $this->sortOrder = auth()->user()->categories()->count();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
            'sortOrder' => ['required', 'integer', 'min:0'],
        ];
    }

    public function getCategoriesProperty()
    {
        $user = auth()->user();
        if (!$user) return collect();
        return $user->categories()->orderBy('sort_order')->get();
    }

    public function save(): void
    {
        $this->validate();
        
        $user = auth()->user();
        
        if ($this->editingCategory) {
            // Check if name already exists (excluding current category)
            $existingCategory = $user->categories()
                ->where('name', $this->name)
                ->where('id', '!=', $this->editingCategory->id)
                ->first();
                
            if ($existingCategory) {
                $this->addError('name', 'A category with this name already exists.');
                return;
            }
            
            $this->editingCategory->update([
                'name' => $this->name,
                'color' => $this->color,
                'sort_order' => $this->sortOrder,
            ]);
            
            $this->dispatch('category-updated');
        } else {
            // Check if name already exists
            if ($user->categories()->where('name', $this->name)->exists()) {
                $this->addError('name', 'A category with this name already exists.');
                return;
            }
            
            $user->categories()->create([
                'name' => $this->name,
                'color' => $this->color,
                'sort_order' => $this->sortOrder,
            ]);
            
            $this->dispatch('category-created');
        }
        
        $this->reset(['name', 'color', 'sortOrder', 'showForm', 'editingCategory']);
        $this->sortOrder = $this->categories->count();
    }

    public function edit(Category $category): void
    {
        $this->editingCategory = $category;
        $this->name = $category->name;
        $this->color = $category->color;
        $this->sortOrder = $category->sort_order;
        $this->showForm = true;
    }

    public function delete(Category $category): void
    {
        // Check if category has articles
        if ($category->articles()->exists()) {
            $this->dispatch('error', message: 'Cannot delete category that has articles. Please reassign or delete the articles first.');
            return;
        }
        
        $category->delete();
        $this->dispatch('category-deleted');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'color', 'sortOrder', 'showForm', 'editingCategory']);
    }

    public function openForm(): void
    {
        $this->showForm = true;
        $this->editingCategory = null;
        $this->name = '';
        $this->color = '#3B82F6';
        $this->sortOrder = $this->categories->count();
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Categories')" :subheading="__('Manage your article categories and their colors')">
        <div class="w-full max-w-4xl">
            <!-- Add Category Button -->
            <div class="mb-6">
        <flux:button
            wire:click="openForm"
            variant="primary"
            icon="plus"
        >
            Add Category
        </flux:button>
    </div>

    <!-- Add/Edit Form -->
    @if($showForm)
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ $editingCategory ? 'Edit Category' : 'Add New Category' }}
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Name -->
                <div class="md:col-span-2">
                    <flux:field label="Category Name" error="{{ $errors->first('name') }}">
                        <flux:input
                            wire:model="name"
                            placeholder="e.g., Machine Learning, Web Development"
                        />
                    </flux:field>
                </div>

                <!-- Color -->
                <div>
                    <flux:field label="Color" error="{{ $errors->first('color') }}">
                        <div class="flex items-center space-x-2">
                            <input
                                type="color"
                                wire:model="color"
                                class="w-12 h-10 rounded border border-gray-300 dark:border-zinc-600 cursor-pointer"
                            >
                            <flux:input
                                wire:model="color"
                                placeholder="#3B82F6"
                                class="flex-1"
                            />
                        </div>
                    </flux:field>
                </div>

                <!-- Sort Order -->
                <div>
                    <flux:field label="Sort Order" error="{{ $errors->first('sortOrder') }}">
                        <flux:input
                            wire:model="sortOrder"
                            type="number"
                            min="0"
                        />
                    </flux:field>
                </div>
            </div>

            <!-- Color Preview -->
            <div class="mt-4">
                <flux:field label="Preview">
                    <div class="flex items-center space-x-2 p-3 rounded border border-gray-200 dark:border-zinc-700">
                        <div 
                            class="w-4 h-4 rounded-full"
                            style="background-color: {{ $color }}"
                        ></div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $name ?: 'Category Name' }}
                        </span>
                    </div>
                </flux:field>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 mt-6">
                                        <flux:button
                            wire:click="cancel"
                            variant="ghost"
                        >
                            Cancel
                        </flux:button>
                <flux:button
                    wire:click="save"
                    variant="primary"
                >
                    {{ $editingCategory ? 'Update' : 'Create' }} Category
                </flux:button>
            </div>
        </div>
    @endif

    <!-- Categories List -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700 overflow-hidden">
        @if($this->categories->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                    <thead class="bg-gray-50 dark:bg-zinc-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Articles
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Sort Order
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-zinc-700">
                        @foreach($this->categories as $category)
                            <tr class="hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div 
                                            class="w-4 h-4 rounded-full mr-3"
                                            style="background-color: {{ $category->color }}"
                                        ></div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $category->name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $category->articles()->count() }} articles
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $category->sort_order }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <flux:button
                                            wire:click="edit({{ $category->id }})"
                                            variant="ghost"
                                            size="sm"
                                        >
                                            Edit
                                        </flux:button>
                                        <flux:button
                                            wire:click="delete({{ $category->id }})"
                                            wire:confirm="Are you sure you want to delete this category? This action cannot be undone."
                                            variant="danger"
                                            size="sm"
                                        >
                                            Delete
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No categories yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Get started by creating your first category to organize your articles.
                </p>
                <div class="mt-6">
                    <flux:button
                        wire:click="openForm"
                        variant="primary"
                    >
                        Create Category
                    </flux:button>
                </div>
            </div>
        @endif
            </div>
        </div>
    </x-settings.layout>
</section>
