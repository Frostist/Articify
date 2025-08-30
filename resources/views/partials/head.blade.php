<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>
<meta name="description" content="{{ $description ?? 'Articify - Your learning journey, organized and tracked.' }}" />
<meta name="keywords" content="learning, education, tracking, articles, knowledge management" />
<meta name="author" content="Articify" />

<!-- Favicons -->
<link rel="icon" href="/favicon.png" type="image/png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ request()->url() }}" />
<meta property="og:title" content="{{ $title ?? config('app.name') }}" />
<meta property="og:description" content="{{ $description ?? 'Articify - Your learning journey, organized and tracked.' }}" />
<meta property="og:image" content="{{ url('/images/assets/Logo.png') }}" />

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image" />
<meta property="twitter:url" content="{{ request()->url() }}" />
<meta property="twitter:title" content="{{ $title ?? config('app.name') }}" />
<meta property="twitter:description" content="{{ $description ?? 'Articify - Your learning journey, organized and tracked.' }}" />
<meta property="twitter:image" content="{{ url('/images/assets/Logo.png') }}" />

<!-- Additional SEO -->
<meta name="robots" content="index, follow" />

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
