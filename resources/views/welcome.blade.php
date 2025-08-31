<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Articify - Track your reading, build lasting habits</title>

        <link rel="icon" href="/favicon.png" type="image/png">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-white dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 min-h-screen">
        <!-- Navigation -->
        <nav class="border-b border-neutral-200 dark:border-neutral-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Articify</h1>
                    </div>
                    
                    <!-- Auth Links -->
                    @if (Route::has('login'))
                        <div class="flex items-center gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" 
                                   class="bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 px-4 py-2 rounded-lg text-sm font-medium hover:bg-neutral-800 dark:hover:bg-neutral-100 transition-colors">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" 
                                   class="text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white px-3 py-2 text-sm font-medium transition-colors">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" 
                                       class="bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 px-4 py-2 rounded-lg text-sm font-medium hover:bg-neutral-800 dark:hover:bg-neutral-100 transition-colors">
                                        Sign up
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
                <div class="text-center">
                    <!-- Main Headline -->
                    <h1 class="text-4xl font-bold tracking-tight text-neutral-900 dark:text-white sm:text-6xl">
                        Track your reading,<br>
                        <span class="text-blue-600 dark:text-blue-400">build lasting habits</span>
                    </h1>
                    
                    <!-- Subtitle -->
                    <p class="mt-6 text-lg leading-8 text-neutral-600 dark:text-neutral-400 max-w-2xl mx-auto">
                        Visualize your article reading progress with beautiful charts, organize content by categories, 
                        and never lose track of your learning journey.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="mt-10 flex items-center justify-center gap-6">
                        <a href="{{ route('dashboard') }}" 
                           class="bg-blue-600 text-white px-6 py-3 rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-950">
                            Get started for free
                        </a>
                        <a href="#features" 
                           class="text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white px-6 py-3 text-sm font-semibold transition-colors">
                            Learn more
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="py-24 bg-neutral-50 dark:bg-neutral-900/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-neutral-900 dark:text-white">
                        Everything you need to build a reading habit
                    </h2>
                    <p class="mt-4 text-lg text-neutral-600 dark:text-neutral-400">
                        Simple tools to track, organize, and visualize your learning journey
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Visual Progress -->
                    <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 shadow-sm border border-neutral-200 dark:border-neutral-800">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Visual Progress</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            See your reading consistency with GitHub-style contribution graphs that show your daily progress at a glance.
                        </p>
                    </div>

                    <!-- Smart Categories -->
                    <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 shadow-sm border border-neutral-200 dark:border-neutral-800">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Smart Organization</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Categorize articles by topic and track which subjects you're exploring most. Color-coded for easy identification.
                        </p>
                    </div>

                    <!-- Streak Tracking -->
                    <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 shadow-sm border border-neutral-200 dark:border-neutral-800">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Streak Building</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Build momentum with reading streaks. Track missed days and maintain consistency in your learning journey.
                        </p>
                    </div>

                    <!-- Article Management -->
                    <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 shadow-sm border border-neutral-200 dark:border-neutral-800">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Article Tracking</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Log articles with publication dates, URLs, and reading dates. Keep a complete record of your learning sources.
                        </p>
                    </div>

                    <!-- Analytics -->
                    <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 shadow-sm border border-neutral-200 dark:border-neutral-800">
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Learning Analytics</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Understand your reading patterns and identify trends in your learning habits over time.
                        </p>
                    </div>

                    <!-- Simple Interface -->
                    <div class="bg-white dark:bg-neutral-900 rounded-xl p-6 shadow-sm border border-neutral-200 dark:border-neutral-800">
                        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-neutral-900 dark:text-white mb-2">Simple & Clean</h3>
                        <p class="text-neutral-600 dark:text-neutral-400">
                            Beautifully designed interface that gets out of your way and lets you focus on what matters most: reading.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-blue-600">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-white mb-4">
                        Ready to build your reading habit?
                    </h2>
                    <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                        Join thousands of learners who are already tracking their progress and building consistent reading habits.
                    </p>
                    <a href="{{ route('dashboard') }}" 
                       class="bg-white text-blue-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-50 transition-colors focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-600">
                        Start tracking today
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-neutral-200 dark:border-neutral-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center text-sm text-neutral-500 dark:text-neutral-400">
                    <p>&copy; {{ date('Y') }} Articify.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
