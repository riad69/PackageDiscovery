# PHP Package Discovery

<p align="center">
<a href="https://packagist.org"><img src="https://img.shields.io/badge/Packagist-API-F28D1A?style=for-the-badge&logo=packagist&logoColor=white" alt="Packagist API"></a>
<a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 10.x"></a>
<a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="TailwindCSS 3.x"></a>
</p>

## About Package Discovery

Package Discovery is a modern web application that helps developers discover and explore PHP packages from Packagist. Built with Laravel and TailwindCSS, it provides a beautiful and intuitive interface for searching, browsing, and analyzing PHP packages.

### Key Features

- 🔍 **Advanced Search**: Search packages by name, description, or tags
- 📊 **Detailed Statistics**: View comprehensive package statistics including downloads, GitHub metrics, and dependencies
- 👥 **Maintainer Information**: See package maintainers and their contributions
- 📈 **Version History**: Browse through package versions and their requirements
- 🎯 **Popular Packages**: Discover trending and popular packages
- 💫 **Modern UI**: Beautiful and responsive interface built with TailwindCSS
- ⚡ **Real-time Updates**: Live package data from Packagist API

## Getting Started

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js and NPM
- Laravel 10.x

### Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/PackageDiscovery.git
cd PackageDiscovery
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Build assets:
```bash
npm run build
```

7. Start the development server:
```bash
php artisan serve
```

Visit `http://localhost:8000` to see the application.

## Usage

### Searching Packages

1. Use the search bar to find packages by name or description
2. Filter results by type or tags
3. Browse popular packages using the "Popular" filter
4. Click on any package to view detailed information

### Package Details

Each package view includes:
- Basic package information
- GitHub statistics
- Download statistics
- Package dependencies
- Maintainer information
- Version history

## API Integration

The application uses the following Packagist API endpoints:
- `https://packagist.org/search.json` - Package search
- `https://packagist.org/explore/popular.json` - Popular packages
- `https://packagist.org/packages/{vendor}/{package}.json` - Package details
- `https://repo.packagist.org/p2/{vendor}/{package}.json` - Package versions

## Contributing

Thank you for considering contributing to Package Discovery! Please read our [Contributing Guidelines](CONTRIBUTING.md) before submitting pull requests.

## Security

If you discover any security-related issues, please email [your-email@example.com](mailto:your-email@example.com) instead of using the issue tracker.

## License

The Package Discovery application is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Acknowledgments

- [Laravel](https://laravel.com) - The web framework used
- [TailwindCSS](https://tailwindcss.com) - The CSS framework
- [Packagist](https://packagist.org) - The package repository
- [Alpine.js](https://alpinejs.dev) - The JavaScript framework
