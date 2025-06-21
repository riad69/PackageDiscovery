<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PackagistController extends Controller
{
    private $userAgent = 'PackageDiscovery/1.0 (https://github.com/riad69/PackageDiscovery)';

    /**
     * Helper function to make API calls with retries
     */
    private function makeApiCall($url, $cacheKey = null, $cacheDuration = 300)
    {
        $maxRetries = 3;
        $retryDelay = 1000; // ms

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'application/json',
                    'If-Modified-Since' => $cacheKey && Cache::has($cacheKey . '_last_modified') 
                        ? gmdate('D, d M Y H:i:s T', Cache::get($cacheKey . '_last_modified')) 
                        : null,
                ])->timeout(10)->get($url);

                if ($response->successful()) {
                    // Store Last-Modified header if present
                    if ($cacheKey && $response->header('Last-Modified')) {
                        Cache::put($cacheKey . '_last_modified', strtotime($response->header('Last-Modified')), $cacheDuration);
                    }
                    // Cache the response if cacheKey is provided
                    if ($cacheKey) {
                        Cache::put($cacheKey, $response->json(), $cacheDuration);
                    }
                    return $response->json();
                }

                if ($response->status() === 304 && $cacheKey) { // Not Modified, return cached data if cacheKey exists
                    return Cache::get($cacheKey);
                }

                \Log::warning('API call failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'attempt' => $attempt,
                ]);

                if ($attempt < $maxRetries) {
                    usleep($retryDelay * 1000);
                }
            } catch (\Exception $e) {
                \Log::error('API call exception', [
                    'url' => $url,
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                ]);
                if ($attempt < $maxRetries) {
                    usleep($retryDelay * 1000);
                }
            }
        }

        return null;
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $page = $request->input('page', 1);
        $tag = $request->input('tag', '');

        $url = 'https://packagist.org/search.json?q=' . urlencode($query) . '&page=' . $page . '&per_page=15';
        if ($tag) $url .= '&tags=' . urlencode($tag);

        $cacheKey = 'packagist_search_' . md5($url);

        $data = Cache::remember($cacheKey, 300, function () use ($url, $cacheKey) {
            $response = $this->makeApiCall($url, $cacheKey, 300);

            if (!$response || !isset($response['results'])) {
                return ['results' => [], 'total' => 0];
            }

            if (is_array($response['results'])) {
                $results = collect($response['results']);
                $results = $results->map(function ($item) {
                    if (isset($item['downloads']) && is_array($item['downloads'])) {
                        $item['downloads_monthly'] = $item['downloads']['monthly'] ?? 0;
                        $item['downloads_daily'] = $item['downloads']['daily'] ?? 0;
                    } else {
                        $item['downloads_monthly'] = 0;
                        $item['downloads_daily'] = 0;
                    }
                    return $item;
                });
                // Default sort by downloads
                $results = $results->sortByDesc('downloads.total');
                $response['results'] = $results->values()->toArray();
            }

            return $response;
        });

        return response()->json($data);
    }

    public function getPackageDetails($vendor, $package)
    {
        $packageName = $vendor . '/' . $package;
        $cacheKey = 'packagist_package_details_' . md5($packageName);

        try {
            // Get package details from Packagist API
            $packageData = Cache::remember($cacheKey, 3600 * 24, function () use ($packageName, $cacheKey) {
                $response = $this->makeApiCall('https://packagist.org/packages/' . $packageName . '.json', $cacheKey, 3600 * 24);
                
                \Log::info('Packagist API response', [
                    'package' => $packageName,
                    'status' => $response ? 'success' : 'failed',
                ]);

                if (!$response || !isset($response['package'])) {
                    \Log::warning('Package data not found', ['package' => $packageName]);
                    return null;
                }

                return $response;
            });

            if (!$packageData || !isset($packageData['package'])) {
                return response()->json(['error' => 'Package not found or data unavailable'], 404);
            }

            // Get package versions from Composer v2 metadata (tagged and dev)
            $versionsData = Cache::remember($cacheKey . '_versions', 3600 * 24, function () use ($vendor, $package, $cacheKey) {
                $taggedResponse = $this->makeApiCall('https://repo.packagist.org/p2/' . $vendor . '/' . $package . '.json', $cacheKey . '_versions_tagged', 3600 * 24);
                $devResponse = $this->makeApiCall('https://repo.packagist.org/p2/' . $vendor . '/' . $package . '~dev.json', $cacheKey . '_versions_dev', 3600 * 24);

                $versions = [];
                if ($taggedResponse && isset($taggedResponse['packages'][$vendor . '/' . $package])) {
                    $versions = array_merge($versions, $taggedResponse['packages'][$vendor . '/' . $package]);
                }
                if ($devResponse && isset($devResponse['packages'][$vendor . '/' . $package])) {
                    $versions = array_merge($versions, $devResponse['packages'][$vendor . '/' . $package]);
                }

                if (empty($versions)) {
                    \Log::warning('Packagist versions API error', [
                        'package' => $vendor . '/' . $package,
                    ]);
                    return null;
                }

                return ['packages' => [$vendor . '/' . $package => $versions]];
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
            \Log::error('Error fetching package details', [
                'package' => $packageName,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function autocomplete(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $url = 'https://packagist.org/search.json?q=' . urlencode($query) . '&per_page=100';
        $cacheKey = 'packagist_autocomplete_' . md5($query);

        $data = Cache::remember($cacheKey, 300, function () use ($url, $cacheKey) {
            $response = $this->makeApiCall($url, $cacheKey, 300);
            return $response ?: ['results' => []];
        });

        $suggestions = [];
        if (isset($data['results'])) {
            $suggestions = collect($data['results'])->map(function ($package) {
                return [
                    'name' => $package['name'],
                    'description' => $package['description'] ?? '',
                    'downloads' => $package['downloads']['total'] ?? 0,
                    'favers' => $package['favers'] ?? 0
                ];
            })->toArray();
        }

        return response()->json(['suggestions' => $suggestions]);
    }
}
?>