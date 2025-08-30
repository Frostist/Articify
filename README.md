# Article Reading Tracker for PhD Students

A Laravel application that helps PhD students track their daily academic reading progress with a GitHub-style contribution graph.

## Features

- **Secure Authentication**: User registration and login system
- **Article Tracking**: Add articles with title, publication date, URL, and read date
- **GitHub-Style Contribution Graph**: Visual representation of daily reading activity
- **Modern UI**: Built with Flux UI components and Tailwind CSS
- **Dark Mode Support**: Automatic dark/light mode switching
- **Responsive Design**: Works on desktop, tablet, and mobile devices

## Technology Stack

- **Laravel 12** - PHP framework
- **Livewire 3** - Full-stack framework for Laravel
- **Volt** - Class-based API for Livewire
- **Flux UI** - Component library for Livewire
- **Tailwind CSS 4** - Utility-first CSS framework
- **SQLite** - Database (can be easily changed to MySQL/PostgreSQL)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Articify
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Set up environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

## Usage

### Getting Started

1. Visit `http://localhost:8000`
2. Register a new account or use the demo account:
   - Email: `phd@example.com`
   - Password: `password`
3. Navigate to the dashboard to start tracking your reading

### Adding Articles

1. Click "Add Article" on the dashboard
2. Fill in the form:
   - **Article Title**: The title of the academic paper
   - **Publication Date**: When the article was published
   - **Article URL**: Link to the article (DOI, journal website, etc.)
   - **Date Read**: When you read the article
3. Click "Save Article"

### Understanding the Contribution Graph

The contribution graph shows your reading activity over the past year:

- **Gray squares**: No articles read on that day
- **Light green**: 1 article read
- **Medium green**: 2-3 articles read
- **Dark green**: 4-5 articles read
- **Very dark green**: 6+ articles read

Hover over any square to see the exact date and number of articles read.

### Managing Articles

- View your recent articles in the "Recent Articles" section
- Click "View Article" to open the article URL in a new tab
- Click the trash icon to delete an article (with confirmation)

## Database Schema

### Articles Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `user_id` | bigint | Foreign key to users table |
| `title` | varchar(255) | Article title |
| `publication_date` | date | When the article was published |
| `url` | varchar(2048) | Article URL |
| `read_date` | date | When the user read the article |
| `created_at` | timestamp | Record creation time |
| `updated_at` | timestamp | Record update time |

## Testing

Run the test suite:

```bash
php artisan test
```

Run specific tests:

```bash
php artisan test tests/Feature/ArticleTrackingTest.php
```

## Development

### Code Style

The project uses Laravel Pint for code formatting:

```bash
vendor/bin/pint
```

### Adding New Features

1. Create migrations for database changes
2. Update models with relationships and validation
3. Create Volt components for new pages
4. Write tests for new functionality
5. Update documentation

## Security Features

- **Authentication**: Laravel's built-in authentication system
- **Authorization**: Users can only access and modify their own articles
- **CSRF Protection**: Automatic CSRF token validation
- **Input Validation**: Comprehensive form validation
- **SQL Injection Protection**: Eloquent ORM with parameter binding

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Run the test suite
6. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For support, please open an issue on the GitHub repository or contact the development team.
