<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PackagistController extends Controller
{
    private $userAgent = 'PackageDiscovery/1.0 (https://github.com/yourusername/PackageDiscovery)';

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $page = $request->input('page', 1);
        $tag = $request->input('tag', '');
        $type = $request->input('type', '');

        $url = 'https://packagist.org/search.json?q=' . urlencode($query) . '&page=' . $page;
        if ($tag) $url .= '&tags=' . urlencode($tag);
        if ($type) $url .= '&type=' . urlencode($type);

        $cacheKey = 'packagist_search_' . md5($url);

        $data = Cache::remember($cacheKey, 60, function () use ($url) {
            return Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])->get($url)->json();
        });

        return response()->json($data);
    }

    public function popular(Request $request)
    {
        $page = $request->input('page', 1);
        $url = 'https://packagist.org/explore/popular.json?per_page=15&page=' . $page;
        $cacheKey = 'packagist_popular_' . $page;

        $data = Cache::remember($cacheKey, 60, function () use ($url) {
            return Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])->get($url)->json();
        });

        return response()->json($data);
    }

    public function getPackageDetails($vendor, $package)
    {
        $packageName = $vendor . '/' . $package;
        $cacheKey = 'packagist_package_details_' . md5($packageName);

        try {
            // Get package details from Packagist API
            $packageData = Cache::remember($cacheKey, 3600 * 12, function () use ($packageName) {
                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'application/json',
                ])->get('https://packagist.org/packages/' . $packageName . '.json');
                
                if (!$response->successful()) {
                    \Log::error('Packagist API error', [
                        'package' => $packageName,
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return null;
                }

                return $response->json();
            });

            if (!$packageData || !isset($packageData['package'])) {
                \Log::warning('Package data not found', ['package' => $packageName]);
                return response()->json(['error' => 'Package not found or data unavailable'], 404);
            }

            // Get package versions from Composer v2 metadata
            $versionsData = Cache::remember($cacheKey . '_versions', 3600 * 12, function () use ($vendor, $package) {
                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'application/json',
                ])->get('https://repo.packagist.org/p2/' . $vendor . '/' . $package . '.json');
                
                if (!$response->successful()) {
                    \Log::warning('Packagist versions API error', [
                        'package' => $vendor . '/' . $package,
                        'status' => $response->status()
                    ]);
                    return null;
                }

                return $response->json();
            });

            // Get the package data
            $package = $packageData['package'];

            // Format the package data
            $formattedPackage = [
                'name' => $package['name'],
                'description' => $package['description'] ?? '',
                'type' => $package['type'] ?? 'library',
                'time' => $package['time'] ?? null,
                'repository' => $package['repository'] ?? null,
                'downloads' => [
                    'total' => $package['downloads']['total'] ?? 0,
                    'monthly' => $package['downloads']['monthly'] ?? 0,
                    'daily' => $package['downloads']['daily'] ?? 0
                ],
                'favers' => $package['favers'] ?? 0,
                'maintainers' => [],
                'versions' => [],
                'github_stars' => $package['github_stars'] ?? 0,
                'github_watchers' => $package['github_watchers'] ?? 0,
                'github_forks' => $package['github_forks'] ?? 0,
                'github_open_issues' => $package['github_open_issues'] ?? 0,
                'dependents' => $package['dependents'] ?? 0,
                'suggesters' => $package['suggesters'] ?? 0
            ];

            // Add versions data if available
            if ($versionsData && isset($versionsData['packages'][$packageName])) {
                $formattedPackage['versions'] = collect($versionsData['packages'][$packageName])
                    ->map(function ($version) {
                        return [
                            'version' => $version['version'],
                            'time' => $version['time'] ?? null,
                            'type' => $version['type'] ?? null,
                            'require' => $version['require'] ?? [],
                            'require-dev' => $version['require-dev'] ?? [],
                        ];
                    })
                    ->keyBy('version')
                    ->toArray();
            }

            // Format maintainers data
            if (isset($package['maintainers'])) {
                $formattedPackage['maintainers'] = collect($package['maintainers'])->map(function ($maintainer) {
                    return [
                        'name' => $maintainer['name'],
                        'avatar_url' => $maintainer['avatar_url'] ?? null,
                        'homepage' => $maintainer['homepage'] ?? null,
                    ];
                })->toArray();
            }

            // Ensure all numeric values are integers
            $formattedPackage['downloads'] = array_map(function ($value) {
                return is_numeric($value) ? (int) $value : 0;
            }, $formattedPackage['downloads']);

            $formattedPackage['favers'] = (int) $formattedPackage['favers'];
            $formattedPackage['github_stars'] = (int) $formattedPackage['github_stars'];
            $formattedPackage['github_watchers'] = (int) $formattedPackage['github_watchers'];
            $formattedPackage['github_forks'] = (int) $formattedPackage['github_forks'];
            $formattedPackage['github_open_issues'] = (int) $formattedPackage['github_open_issues'];
            $formattedPackage['dependents'] = (int) $formattedPackage['dependents'];
            $formattedPackage['suggesters'] = (int) $formattedPackage['suggesters'];

            return response()->json($formattedPackage);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
} 