<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PackagistController extends Controller
{
    private $userAgent = 'PackageDiscovery/1.0 (https://github.com/riad69/PackageDiscovery)';

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $page = $request->input('page', 1);
        $tag = $request->input('tag', '');
        $type = $request->input('type', '');
        $sort = $request->input('sort', 'downloads');

        // New filter parameters
        $githubStarsMin = $request->input('github_stars_min');
        $githubForksMin = $request->input('github_forks_min');
        $githubWatchersMin = $request->input('github_watchers_min');
        $githubIssuesMin = $request->input('github_issues_min');
        $downloadsTotalMin = $request->input('downloads_total_min');
        $downloadsMonthlyMin = $request->input('downloads_monthly_min');
        $downloadsDailyMin = $request->input('downloads_daily_min');
        $dependentsMin = $request->input('dependents_min');
        $suggestersMin = $request->input('suggesters_min');

        $url = 'https://packagist.org/search.json?q=' . urlencode($query) . '&page=' . $page;
        if ($tag) $url .= '&tags=' . urlencode($tag);
        if ($type) $url .= '&type=' . urlencode($type);

        $cacheKey = 'packagist_search_' . md5($url . '_' . $sort);

        $data = Cache::remember($cacheKey, 60, function () use ($url, $sort) {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])->get($url)->json();

            // Fix: Extract downloads_monthly and downloads_daily for sorting
            if (isset($response['results']) && is_array($response['results'])) {
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
                switch ($sort) {
                    case 'downloads':
                        $results = $results->sortByDesc('downloads');
                        break;
                    case 'downloads_asc':
                        $results = $results->sortBy('downloads');
                        break;
                    case 'stars':
                        $results = $results->sortByDesc('github_stars');
                        break;
                    case 'stars_asc':
                        $results = $results->sortBy('github_stars');
                        break;
                    case 'forks':
                        $results = $results->sortByDesc('github_forks');
                        break;
                    case 'forks_asc':
                        $results = $results->sortBy('github_forks');
                        break;
                    case 'watchers':
                        $results = $results->sortByDesc('github_watchers');
                        break;
                    case 'watchers_asc':
                        $results = $results->sortBy('github_watchers');
                        break;
                    case 'issues':
                        $results = $results->sortByDesc('github_open_issues');
                        break;
                    case 'issues_asc':
                        $results = $results->sortBy('github_open_issues');
                        break;
                    case 'monthly':
                        $results = $results->sortByDesc('downloads_monthly');
                        break;
                    case 'monthly_asc':
                        $results = $results->sortBy('downloads_monthly');
                        break;
                    case 'daily':
                        $results = $results->sortByDesc('downloads_daily');
                        break;
                    case 'daily_asc':
                        $results = $results->sortBy('downloads_daily');
                        break;
                    case 'suggesters':
                        $results = $results->sortByDesc('suggesters');
                        break;
                    case 'suggesters_asc':
                        $results = $results->sortBy('suggesters');
                        break;
                    case 'dependents':
                        $results = $results->sortByDesc('dependents');
                        break;
                    default:
                        $results = $results->sortByDesc('downloads');
                }
                $response['results'] = $results->values()->toArray();
            }

            return $response;
        });

        // Save the original total from Packagist
        $originalTotal = isset($data['total']) ? $data['total'] : null;
        $filtersActive = $githubStarsMin !== null || $githubForksMin !== null || $githubWatchersMin !== null || $githubIssuesMin !== null || $downloadsTotalMin !== null || $downloadsMonthlyMin !== null || $downloadsDailyMin !== null || $dependentsMin !== null || $suggestersMin !== null;
        $filteredCount = null;

        // Apply new filters to the results
        if (isset($data['results']) && is_array($data['results'])) {
            if ($filtersActive) {
                // If any GitHub filter is active, fetch details for each package
                $githubFiltersActive = $githubStarsMin !== null || $githubForksMin !== null || $githubWatchersMin !== null || $githubIssuesMin !== null;
                $results = collect($data['results']);
                if ($githubFiltersActive) {
                    $results = $results->map(function ($pkg) {
                        $packageName = $pkg['name'];
                        $cacheKey = 'packagist_package_details_' . md5($packageName);
                        $packageData = Cache::remember($cacheKey, 3600 * 12, function () use ($packageName) {
                            $response = Http::withHeaders([
                                'User-Agent' => $this->userAgent,
                                'Accept' => 'application/json',
                            ])->get('https://packagist.org/packages/' . $packageName . '.json');
                            if (!$response->successful()) {
                                return [];
                            }
                            $json = $response->json();
                            return $json['package'] ?? [];
                        });
                        // Merge GitHub stats if available
                        foreach ([
                            'github_stars', 'github_forks', 'github_watchers', 'github_open_issues',
                            'dependents', 'suggesters'
                        ] as $field) {
                            if (isset($packageData[$field])) {
                                $pkg[$field] = $packageData[$field];
                            }
                        }
                        return $pkg;
                    });
                }
                $data['results'] = $results->filter(function ($pkg) use (
                    $githubStarsMin, $githubForksMin, $githubWatchersMin, $githubIssuesMin,
                    $downloadsTotalMin, $downloadsMonthlyMin, $downloadsDailyMin,
                    $dependentsMin, $suggestersMin
                ) {
                    // GitHub stats
                    if ($githubStarsMin !== null && (!isset($pkg['github_stars']) || !is_numeric($pkg['github_stars']) || $pkg['github_stars'] < $githubStarsMin)) return false;
                    if ($githubForksMin !== null && (!isset($pkg['github_forks']) || !is_numeric($pkg['github_forks']) || $pkg['github_forks'] < $githubForksMin)) return false;
                    if ($githubWatchersMin !== null && (!isset($pkg['github_watchers']) || !is_numeric($pkg['github_watchers']) || $pkg['github_watchers'] < $githubWatchersMin)) return false;
                    if ($githubIssuesMin !== null && (!isset($pkg['github_open_issues']) || !is_numeric($pkg['github_open_issues']) || $pkg['github_open_issues'] < $githubIssuesMin)) return false;
                    // Download stats
                    if ($downloadsTotalMin !== null && (!isset($pkg['downloads']['total']) || !is_numeric($pkg['downloads']['total']) || $pkg['downloads']['total'] < $downloadsTotalMin)) return false;
                    if ($downloadsMonthlyMin !== null && (!isset($pkg['downloads_monthly']) || !is_numeric($pkg['downloads_monthly']) || $pkg['downloads_monthly'] < $downloadsMonthlyMin)) return false;
                    if ($downloadsDailyMin !== null && (!isset($pkg['downloads_daily']) || !is_numeric($pkg['downloads_daily']) || $pkg['downloads_daily'] < $downloadsDailyMin)) return false;
                    // Package stats
                    if ($dependentsMin !== null && (!isset($pkg['dependents']) || !is_numeric($pkg['dependents']) || $pkg['dependents'] < $dependentsMin)) return false;
                    if ($suggestersMin !== null && (!isset($pkg['suggesters']) || !is_numeric($pkg['suggesters']) || $pkg['suggesters'] < $suggestersMin)) return false;
                    return true;
                })->values()->toArray();
                $filteredCount = count($data['results']);
                $data['total'] = $filteredCount;
            }
        }

        // Add extra info for frontend
        $data['original_total'] = $originalTotal;
        $data['filtered_count'] = $filteredCount;
        $data['filters_active'] = $filtersActive;
        $data['sort_applied'] = $sort;
        $data['filter_fields_applied'] = array_filter([
            $githubStarsMin !== null ? 'github_stars_min' : null,
            $githubForksMin !== null ? 'github_forks_min' : null,
            $githubWatchersMin !== null ? 'github_watchers_min' : null,
            $githubIssuesMin !== null ? 'github_issues_min' : null,
            $downloadsTotalMin !== null ? 'downloads_total_min' : null,
            $downloadsMonthlyMin !== null ? 'downloads_monthly_min' : null,
            $downloadsDailyMin !== null ? 'downloads_daily_min' : null,
            $dependentsMin !== null ? 'dependents_min' : null,
            $suggestersMin !== null ? 'suggesters_min' : null,
        ]);
        if ($filtersActive || $sort !== 'downloads') {
            $data['note'] = 'Sorting and advanced filters are only applied to the current page of results due to Packagist API limitations.';
        }
        // Debug log
        \Log::debug('[PackagistController] search params', [
            'sort' => $sort,
            'filters' => [
                'githubStarsMin' => $githubStarsMin,
                'githubForksMin' => $githubForksMin,
                'githubWatchersMin' => $githubWatchersMin,
                'githubIssuesMin' => $githubIssuesMin,
                'downloadsTotalMin' => $downloadsTotalMin,
                'downloadsMonthlyMin' => $downloadsMonthlyMin,
                'downloadsDailyMin' => $downloadsDailyMin,
                'dependentsMin' => $dependentsMin,
                'suggestersMin' => $suggestersMin,
            ]
        ]);

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

    public function autocomplete(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $url = 'https://packagist.org/search.json?q=' . urlencode($query) . '&per_page=100';
        $cacheKey = 'packagist_autocomplete_' . md5($query);

        $data = Cache::remember($cacheKey, 60, function () use ($url) {
            return Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])->get($url)->json();
        });

        $suggestions = [];
        if (isset($data['results'])) {
            $suggestions = collect($data['results'])->map(function ($package) {
                return [
                    'name' => $package['name'],
                    'description' => $package['description'] ?? '',
                    'downloads' => $package['downloads'] ?? 0,
                    'favers' => $package['favers'] ?? 0
                ];
            })->toArray();
        }

        return response()->json(['suggestions' => $suggestions]);
    }
}