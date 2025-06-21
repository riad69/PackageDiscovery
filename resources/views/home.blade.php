<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Packagist Package Discovery</title>
    <!-- Styles / Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-blue-50 text-gray-800 min-h-screen px-4 py-8">
  <div class="max-w-7xl mx-auto" x-data="packageDiscovery()" x-init="init()">

    <!-- Header -->
    <h1 class="text-3xl font-bold text-center mb-2">Packagist Package Discovery</h1>
    <p class="text-center text-gray-600 mb-6">Discover packages for any niche or topic using the Packagist API</p>

    <!-- Error Message -->
    <div x-show="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
      <span x-text="errorMessage"></span>
    </div>

    <!-- Search Bar -->
    <div class="flex flex-col sm:flex-row gap-2 items-center justify-center mb-6 relative">
      <div class="w-full sm:w-96 relative">
        <input type="text" placeholder="Search packages..." x-model="query"
               class="w-full px-4 py-2 rounded-md border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
               @input.debounce.300ms="searchSuggestions()"
               @keydown.escape="showSuggestions = false"
               @keydown.enter="selectSuggestion(selectedSuggestion)"
               @keydown.down.prevent="selectNextSuggestion"
               @keydown.up.prevent="selectPreviousSuggestion"
               @focus="showSuggestions = true"
               @blur="setTimeout(() => showSuggestions = false, 200)" />
        
        <!-- Autocomplete Suggestions -->
        <div x-show="showSuggestions && suggestions.length > 0" 
             class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200 max-h-96 overflow-y-auto">
          <template x-for="(suggestion, index) in suggestions" :key="suggestion.name">
            <div @click="selectSuggestion(suggestion)"
                 @mouseenter="selectedSuggestionIndex = index"
                 :class="{'bg-blue-50': selectedSuggestionIndex === index}"
                 class="px-4 py-2 hover:bg-gray-50 cursor-pointer">
              <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-blue-600 truncate" x-text="suggestion.name"></p>
                  <p class="text-sm text-gray-500 truncate" x-text="suggestion.description"></p>
                </div>
                <div class="flex items-center gap-2 ml-4">
                  <div class="flex items-center text-xs text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span x-text="formatNumber(suggestion.downloads)"></span>
                  </div>
                  <div class="flex items-center text-xs text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    <span x-text="formatNumber(suggestion.favers)"></span>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
      <div class="flex gap-2">
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md flex items-center gap-2 transition-colors" @click="search()" :disabled="loading">
          <svg x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span x-text="loading ? 'Searching...' : 'Search'"></span>
        </button>
        <button class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md flex items-center gap-2 transition-colors" @click="resetSearch()" :disabled="loading">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>Reset</span>
        </button>
      </div>
    </div>

    <!-- Popular Categories -->
    <div class="mb-6">
      <h3 class="text-center text-lg font-semibold text-gray-800 mb-3">Popular Categories</h3>
      <div class="flex flex-wrap justify-center gap-2">
        <template x-for="tag in tags" :key="tag">
          <button
            @click="searchTag(tag)"
            :class="{'bg-blue-600 text-white shadow-md': tag === activeTag, 'bg-white text-gray-700 border-gray-200 hover:bg-blue-50 hover:text-blue-600': tag !== activeTag}"
            class="px-4 py-2 rounded-full border text-sm font-medium transition-all duration-200 hover:shadow-sm"
            x-text="tag">
          </button>
        </template>
      </div>
    </div>

    <!-- Main Content Area -->
    <template x-if="!loading">
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Results Area -->
        <div class="flex-1">
          <!-- Search Prompt -->
          <template x-if="!loading && packages.length === 0 && !hasSearched">
            <div class="flex flex-col items-center justify-center py-20">
              <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 48 48">
                <circle cx="22" cy="22" r="14" stroke="currentColor" stroke-width="3" fill="none"/>
                <line x1="35" y1="35" x2="44" y2="44" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
              </svg>
              <h2 class="text-2xl font-semibold text-gray-700 mb-2">Start your search</h2>
              <p class="text-gray-500">Enter a topic, niche, or keyword to discover PHP packages</p>
            </div>
          </template>

          <!-- Search Result Count -->
          <p class="text-sm text-gray-600 text-left mb-4" x-show="total !== null && !filtersActive">Search Results: <strong x-text="total"></strong> packages found</p>
          <template x-if="filtersActive">
            <p class="text-sm text-yellow-700 bg-yellow-100 border-l-4 border-yellow-400 px-3 py-2 mb-2 rounded">
              <strong>Note:</strong> Filters apply only to the current page of results. <br>
              Showing <strong x-text="filteredCount"></strong> filtered packages out of <strong x-text="originalTotal"></strong> total found on this page.
            </p>
          </template>
          <p class="text-sm text-gray-600 text-left mb-8" x-show="query || activeTag">
            Showing search results for: <strong x-text="activeTag ? activeTag : query"></strong>
          </p>
          
          <!-- Package Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4 mb-8" x-show="!loading && packages.length > 0">
            <template x-for="pkg in packages" :key="pkg.name">
              <div class="rounded-lg border border-gray-200 bg-white text-card-foreground shadow-sm h-full hover:shadow-md transition-shadow relative" __v0_r="0,8650,8701" data-v0-t="card">
                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent h-9 rounded-md px-3 absolute top-2 right-2 z-10 text-gray-400 hover:text-red-500" __v0_r="1,8785,8916" aria-label="Add to favorites">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-heart w-4 h-4" __v0_r="1,9182,9242" aria-hidden="true">
                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                  </svg>
                </button>
                <div class="flex flex-col space-y-1.5 p-6 pb-3 pr-12" __v0_r="0,9314,9326">
                  <div class="flex items-start justify-between gap-2" __v0_r="0,9353,9393">
                    <div class="flex-1 min-w-0" __v0_r="0,9422,9438">
                      <h3 class="tracking-tight text-lg font-semibold text-blue-600 hover:text-blue-800 transition-colors" __v0_r="0,9475,9550">
                        <button @click="openPackageDetails(pkg)" class="flex items-center gap-1 break-all text-left hover:underline" __v0_r="0,9728,9789" aria-label="View package details" type="button" aria-haspopup="dialog" aria-expanded="false" aria-controls="radix-«rcg»" data-state="closed">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-4 h-4 flex-shrink-0" __v0_r="0,10071,10094">
                            <path d="m7.5 4.27 9 5.15"></path>
                            <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                            <path d="m3.3 7 8.7 5 8.7-5"></path>
                            <path d="M12 22V12"></path>
                          </svg>
                          <span x-text="pkg.name"></span>
                        </button>
                      </h3>
                    </div>
                  </div>
                  <p class="text-muted-foreground text-sm line-clamp-2" __v0_r="0,21935,21957" x-text="pkg.description"></p>
                </div>
                <div class="p-6 pt-0" __v0_r="0,22158,22164">
                  <div class="flex items-center justify-between text-sm text-muted-foreground" __v0_r="0,22191,22256">
                    <div class="flex items-center gap-4" __v0_r="0,22285,22310">
                      <div class="flex items-center gap-1" __v0_r="0,22341,22366">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download w-4 h-4" __v0_r="0,22404,22413">
                          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                          <polyline points="7 10 12 15 17 10"></polyline>
                          <line x1="12" x2="12" y1="15" y2="3"></line>
                        </svg>
                        <span x-text="formatNumber(pkg.downloads)"></span>
                      </div>
                      <div class="flex items-center gap-1" __v0_r="0,22526,22551">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star w-4 h-4" __v0_r="0,22585,22594">
                          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span x-text="formatNumber(pkg.favers)"></span>
                      </div>
                    </div>
                    <a :href="pkg.url" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:text-blue-700" __v0_r="0,22832,22867" aria-label="View package on Packagist">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link w-4 h-4" __v0_r="0,22998,23007" aria-hidden="true">
                        <path d="M15 3h6v6"></path>
                        <path d="M10 14 21 3"></path>
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                      </svg>
                    </a>
                  </div>
                </div>
              </div>
            </template>
          </div>
          
          <!-- Pagination -->
          <div class="flex justify-between items-center text-sm text-gray-600" x-show="total > 0">
            <button class="px-4 py-2 rounded-md border bg-white hover:bg-gray-100" @click="prevPage()" :disabled="page === 1">← Previous</button>
            <p>Page <span x-text="page"></span> (Showing <span x-text="((page - 1) * 15) + 1"></span> to <span x-text="Math.min(page * 15, total)"></span> out of <strong x-text="total"></strong> packages found)</p>
            <button class="px-4 py-2 rounded-md border bg-white hover:bg-gray-100" @click="nextPage()" :disabled="!hasNext">Next →</button>
          </div>
        </div>
      </div>
    </template>

    <!-- Loading Skeleton for Search Results and Showing Results -->
    <template x-if="loading">
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Results Area with Loading Skeleton -->
        <div class="flex-1">
          <!-- Loading Result Count -->
          <div class="mb-4 animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-1/3"></div>
          </div>
          
          <!-- Loading Search Info -->
          <div class="mb-8 animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
          </div>

          <!-- Loading Package Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4 mb-8">
            <template x-for="i in 9" :key="i">
              <div class="bg-white p-6 rounded-lg border shadow-sm animate-pulse">
                <!-- Package Header -->
                <div class="flex justify-between items-start mb-3">
                  <div class="flex-1">
                    <div class="h-5 bg-gray-200 rounded w-3/4 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                  </div>
                  <div class="h-6 w-6 bg-gray-200 rounded"></div>
                </div>
                
                <!-- Package Description -->
                <div class="mb-4">
                  <div class="h-3 bg-gray-200 rounded w-full mb-2"></div>
                  <div class="h-3 bg-gray-200 rounded w-5/6 mb-2"></div>
                  <div class="h-3 bg-gray-200 rounded w-4/5"></div>
                </div>
                
                <!-- Package Stats -->
                <div class="flex justify-between items-center">
                  <div class="flex gap-4">
                    <div class="flex items-center gap-1">
                      <div class="h-4 w-4 bg-gray-200 rounded"></div>
                      <div class="h-3 bg-gray-200 rounded w-8"></div>
                    </div>
                    <div class="flex items-center gap-1">
                      <div class="h-4 w-4 bg-gray-200 rounded"></div>
                      <div class="h-3 bg-gray-200 rounded w-8"></div>
                    </div>
                  </div>
                  <div class="h-4 w-4 bg-gray-200 rounded"></div>
                </div>
              </div>
            </template>
          </div>

          <!-- Loading Pagination -->
          <div class="flex justify-between items-center animate-pulse">
            <div class="h-8 bg-gray-200 rounded w-20"></div>
            <div class="h-4 bg-gray-200 rounded w-48"></div>
            <div class="h-8 bg-gray-200 rounded w-20"></div>
          </div>
        </div>
      </div>
    </template>

    <!-- Package Details Modal -->
    <div x-show="showModal" @click.self="showModal = false" class="fixed inset-0 z-50 flex items-center justify-center bg-transparent" style="display: none;">
      <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-auto transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[85vh] flex flex-col">
        <!-- Sticky Header -->
        <div class="flex justify-between items-center px-6 py-4 sticky top-0 bg-white border-b z-30">
          <h3 class="text-2xl font-bold text-gray-900" x-text="selectedPackage ? selectedPackage.name : ''"></h3>
          <button @click="showModal = false" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto scroll-smooth px-6 pb-6">
          <template x-if="packageDetailsError">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
              <strong class="font-bold">Error:</strong>
              <span x-text="packageDetailsError"></span>
              <button @click="openPackageDetails(selectedPackage)" class="ml-4 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Retry</button>
            </div>
          </template>
          <div class="text-gray-700 space-y-6 mt-2" x-show="!packageDetailsError">
            <!-- Basic Info Section -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-semibold text-lg mb-2">Package Information</h4>
              <template x-if="loadingPackageDetails">
                <div class="space-y-4">
                  <div class="h-4 bg-gray-200 rounded w-3/4 animate-pulse"></div>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                      <div class="h-4 bg-gray-200 rounded w-1/2 animate-pulse"></div>
                      <div class="h-4 bg-gray-200 rounded w-1/2 animate-pulse"></div>
                      <div class="h-4 bg-gray-200 rounded w-1/2 animate-pulse"></div>
                    </div>
                    <div class="space-y-2">
                      <div class="h-4 bg-gray-200 rounded w-3/4 animate-pulse"></div>
                    </div>
                  </div>
                </div>
              </template>
              <template x-if="!loadingPackageDetails">
                <div>
                  <p class="mb-2" x-text="selectedPackage ? selectedPackage.description : ''"></p>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <p><strong class="font-semibold">Type:</strong> <span x-text="selectedPackage ? (selectedPackage.type || 'library') : ''"></span></p>
                      <p><strong class="font-semibold">Created:</strong> <span x-text="selectedPackage && selectedPackage.time ? formatDate(selectedPackage.time) : 'N/A'"></span></p>
                      <p><strong class="font-semibold">Favorites:</strong> <span x-text="selectedPackage ? formatNumber(selectedPackage.favers) : '0'"></span></p>
                    </div>
                    <div>
                      <p x-show="selectedPackage && selectedPackage.repository">
                        <strong class="font-semibold">Repository:</strong><br>
                        <a :href="selectedPackage ? selectedPackage.repository : '#'" target="_blank" class="text-blue-600 hover:underline break-all" x-text="selectedPackage ? selectedPackage.repository : ''"></a>
                      </p>
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <!-- GitHub Stats Section -->
            <div x-show="selectedPackage && (selectedPackage.github_stars || selectedPackage.github_forks || selectedPackage.github_watchers || selectedPackage.github_open_issues) || loadingPackageDetails" class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-semibold text-lg mb-2">GitHub Statistics</h4>
              <template x-if="loadingPackageDetails">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                  <div class="text-center space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto animate-pulse"></div>
                    <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                  </div>
                  <div class="text-center space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto animate-pulse"></div>
                    <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                  </div>
                  <div class="text-center space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto animate-pulse"></div>
                    <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                  </div>
                  <div class="text-center space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto animate-pulse"></div>
                    <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                  </div>
                </div>
              </template>
              <template x-if="!loadingPackageDetails">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Stars</p>
                    <p class="text-xl font-bold" x-text="selectedPackage ? formatNumber(selectedPackage.github_stars) : '0'"></p>
                  </div>
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Forks</p>
                    <p class="text-xl font-bold" x-text="selectedPackage ? formatNumber(selectedPackage.github_forks) : '0'"></p>
                  </div>
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Watchers</p>
                    <p class="text-xl font-bold" x-text="selectedPackage ? formatNumber(selectedPackage.github_watchers) : '0'"></p>
                  </div>
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Open Issues</p>
                    <p class="text-xl font-bold" x-text="selectedPackage ? formatNumber(selectedPackage.github_open_issues) : '0'"></p>
                  </div>
                </div>
              </template>
            </div>

            <!-- Download Stats Section -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-semibold text-lg mb-2">Download Statistics</h4>
              <template x-if="loadingPackageDetails">
                <div class="grid grid-cols-3 gap-4">
                  <div class="text-center space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto animate-pulse"></div>
                    <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                  </div>
                  <div class="text-center space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto animate-pulse"></div>
                    <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                  </div>
                  <div class="text-center space-y-2">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto animate-pulse"></div>
                    <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                  </div>
                </div>
              </template>
              <template x-if="!loadingPackageDetails">
                <div class="grid grid-cols-3 gap-4">
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Total Downloads</p>
                    <p class="text-xl font-bold" x-text="selectedPackage && selectedPackage.downloads && selectedPackage.downloads.total ? formatNumber(selectedPackage.downloads.total) : '0'"></p>
                  </div>
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Monthly Downloads</p>
                    <p class="text-xl font-bold" x-text="selectedPackage && selectedPackage.downloads && selectedPackage.downloads.monthly ? formatNumber(selectedPackage.downloads.monthly) : '0'"></p>
                  </div>
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Daily Downloads</p>
                    <p class="text-xl font-bold" x-text="selectedPackage && selectedPackage.downloads && selectedPackage.downloads.daily ? formatNumber(selectedPackage.downloads.daily) : '0'"></p>
                  </div>
                </div>
              </template>
            </div>

            <!-- Package Stats Section -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-semibold text-lg mb-2">Package Statistics</h4>
              <template x-if="loadingPackageDetails">
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto mb-2 animate-pulse"></div>
                    <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                  </div>
                  <div class="text-center">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto mb-2 animate-pulse"></div>
                    <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                  </div>
                </div>
              </template>
              <template x-if="!loadingPackageDetails">
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Dependent Packages</p>
                    <p class="text-xl font-bold" x-text="selectedPackage ? formatNumber(selectedPackage.dependents) : '0'"></p>
                  </div>
                  <div class="text-center">
                    <p class="text-sm text-gray-600">Suggesters</p>
                    <p class="text-xl font-bold" x-text="selectedPackage ? formatNumber(selectedPackage.suggesters) : '0'"></p>
                  </div>
                </div>
              </template>
            </div>

            <!-- Maintainers Section -->
            <div x-show="selectedPackage && selectedPackage.maintainers && selectedPackage.maintainers.length > 0" class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-semibold text-lg mb-2">Maintainers</h4>
              <template x-if="loadingPackageDetails">
                <div class="flex flex-wrap gap-2">
                  <div class="flex items-center gap-2 bg-white px-3 py-1 rounded-full border">
                    <div class="w-6 h-6 bg-gray-200 rounded-full animate-pulse"></div>
                    <div class="h-4 bg-gray-200 rounded w-20 animate-pulse"></div>
                  </div>
                  <div class="flex items-center gap-2 bg-white px-3 py-1 rounded-full border">
                    <div class="w-6 h-6 bg-gray-200 rounded-full animate-pulse"></div>
                    <div class="h-4 bg-gray-200 rounded w-20 animate-pulse"></div>
                  </div>
                </div>
              </template>
              <template x-if="!loadingPackageDetails && selectedPackage && selectedPackage.maintainers">
                <div class="flex flex-wrap gap-2">
                  <template x-for="maintainer in selectedPackage.maintainers" :key="maintainer.name">
                    <div class="flex items-center gap-2 bg-white px-3 py-1 rounded-full border">
                      <img :src="maintainer.avatar_url" :alt="maintainer.name" class="w-6 h-6 rounded-full">
                      <span x-text="maintainer.name"></span>
                    </div>
                  </template>
                </div>
              </template>
            </div>

            <!-- Versions Section -->
            <div x-show="selectedPackage && selectedPackage.versions" class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-semibold text-lg mb-2">Available Versions</h4>
              <template x-if="loadingPackageDetails">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                  <div class="bg-white px-3 py-1 rounded border text-sm text-center">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto animate-pulse"></div>
                  </div>
                  <div class="bg-white px-3 py-1 rounded border text-sm text-center">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto animate-pulse"></div>
                  </div>
                  <div class="bg-white px-3 py-1 rounded border text-sm text-center">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto animate-pulse"></div>
                  </div>
                  <div class="bg-white px-3 py-1 rounded border text-sm text-center">
                    <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto animate-pulse"></div>
                  </div>
                </div>
              </template>
              <template x-if="!loadingPackageDetails && selectedPackage && selectedPackage.versions">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                  <template x-for="(version, index) in Object.keys(selectedPackage.versions).slice(0, showAllVersions ? Object.keys(selectedPackage.versions).length : 8)" :key="index">
                    <div class="bg-white px-3 py-1 rounded border text-sm text-center" x-text="version"></div>
                  </template>
                  <div x-show="!showAllVersions && Object.keys(selectedPackage.versions).length > 8" class="bg-white px-3 py-1 rounded border text-sm text-center text-blue-600 cursor-pointer" @click="showAllVersions = true">
                    +<span x-text="Object.keys(selectedPackage.versions).length - 8"></span> more
                  </div>
                  <div x-show="showAllVersions && Object.keys(selectedPackage.versions).length > 8" class="bg-white px-3 py-1 rounded border text-sm text-center text-blue-600 cursor-pointer" @click="showAllVersions = false">
                    Show less
                  </div>
                </div>
              </template>
            </div>

            <!-- Stats Collection Date -->
            <div x-show="selectedPackage && selectedPackage.date" class="text-sm text-gray-500 text-center">
              <template x-if="loadingPackageDetails">
                <div class="h-4 bg-gray-200 rounded w-1/3 mx-auto animate-pulse"></div>
              </template>
              <template x-if="!loadingPackageDetails && selectedPackage && selectedPackage.date">
                Stats collected since <span x-text="selectedPackage.date"></span>
              </template>
            </div>
          </div>
        </div>

        <!-- Sticky Footer -->
        <div class="flex justify-end gap-3 px-6 py-4 sticky bottom-0 bg-white border-t z-30">
          <a :href="selectedPackage ? selectedPackage.repository : '#'" target="_blank" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"></path><path d="M9 18c-4.51 2-5-2-7-2"></path></svg>
            View Repository
          </a>
          <a :href="selectedPackage ? selectedPackage.url : '#'" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M15 3h6v6"></path><path d="M10 14 21 3"></path><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path></svg>
            View on Packagist
          </a>
        </div>
      </div>
    </div>
  </div>
  <script>
    function packageDiscovery() {
      return {
        query: '',
        tags: ['Laravel','Symfony','Testing','API','Database','Authentication','Cache','Logging','Email','Image Processing','PDF','HTTP Client'],
        activeTag: '',
        page: 1,
        packages: [],
        total: null,
        hasNext: false,
        loading: false,
        hasSearched: false,
        showModal: false,
        selectedPackage: null,
        loadingPackageDetails: false,
        packageDetailsError: null,
        errorMessage: null,
        suggestions: [],
        showSuggestions: false,
        selectedSuggestionIndex: -1,
        debounceTimer: null,
        originalTotal: null,
        filteredCount: null,
        filtersActive: false,
        showAllVersions: false,
        async searchSuggestions() {
          if (this.query.length < 2) {
            this.suggestions = [];
            this.showSuggestions = false;
            return;
          }

          try {
            const response = await fetch(`/api/packagist/autocomplete?q=${encodeURIComponent(this.query)}`);
            if (!response.ok) throw new Error('Failed to fetch suggestions');
            const data = await response.json();
            this.suggestions = data.suggestions;
            this.showSuggestions = true;
            this.selectedSuggestionIndex = -1;
            this.errorMessage = null;
          } catch (error) {
            console.error('Error fetching suggestions:', error);
            this.suggestions = [];
            this.showSuggestions = false;
            this.errorMessage = 'Failed to load suggestions. Please try again.';
          }
        },
        selectSuggestion(suggestion) {
          if (!suggestion) return;
          this.query = suggestion.name;
          this.showSuggestions = false;
          this.search();
        },
        selectNextSuggestion() {
          if (this.selectedSuggestionIndex < this.suggestions.length - 1) {
            this.selectedSuggestionIndex++;
          }
        },
        selectPreviousSuggestion() {
          if (this.selectedSuggestionIndex > 0) {
            this.selectedSuggestionIndex--;
          }
        },
        get selectedSuggestion() {
          return this.suggestions[this.selectedSuggestionIndex] || null;
        },
        search() {
          this.activeTag = '';
          this.page = 1;
          this.hasSearched = true;
          this.errorMessage = null;
          this.fetchPackages();
        },
        searchTag(tag) {
          this.activeTag = tag;
          this.query = tag;
          this.page = 1;
          this.hasSearched = true;
          this.errorMessage = null;
          this.fetchPackages();
        },
        resetSearch() {
          this.query = '';
          this.activeTag = '';
          this.page = 1;
          this.packages = [];
          this.total = null;
          this.hasNext = false;
          this.loading = false;
          this.hasSearched = false;
          this.errorMessage = null;
          this.suggestions = [];
          this.showSuggestions = false;
          this.selectedSuggestionIndex = -1;
        },
        prevPage() {
          if (this.page > 1) {
            this.page--;
            this.errorMessage = null;
            this.fetchPackages();
          }
        },
        nextPage() {
          if (this.hasNext) {
            this.page++;
            this.errorMessage = null;
            this.fetchPackages();
          }
        },
        async openPackageDetails(pkg) {
          this.loadingPackageDetails = true;
          this.selectedPackage = pkg;
          this.showModal = true;
          this.showAllVersions = false;
          this.packageDetailsError = null;

          try {
            const [vendor, packageName] = pkg.name.split('/');
            if (!vendor || !packageName) {
              this.packageDetailsError = 'Invalid package name.';
              return;
            }
            const response = await fetch(`/api/packagist/package/${vendor}/${packageName}`);
            const data = await response.json();

            if (response.ok && data && !data.error) {
              this.selectedPackage = {
                ...data,
                url: `https://packagist.org/packages/${data.name}`,
              };
            } else {
              this.packageDetailsError = data.error || 'Failed to load package details.';
            }
          } catch (error) {
            this.packageDetailsError = 'Network error while fetching package details.';
          } finally {
            this.loadingPackageDetails = false;
          }
        },
        fetchPackages() {
          if (!this.query && !this.activeTag) {
            this.packages = [];
            this.total = null;
            this.hasNext = false;
            this.loading = false;
            this.hasSearched = false;
            this.errorMessage = null;
            return;
          }

          this.loading = true;
          let params = `?page=${this.page}`;
          if (this.activeTag) params += `&tag=${encodeURIComponent(this.activeTag)}`;
          else if (this.query) params += `&q=${encodeURIComponent(this.query)}`;

          const url = `/api/packagist/search${params}`;
          
          fetch(url)
            .then(res => {
              if (!res.ok) throw new Error('Failed to fetch packages');
              return res.json();
            })
            .then(data => {
              this.packages = data.results || [];
              this.total = data.total || 0;
              this.hasNext = !!data.next;
              this.originalTotal = data.original_total || null;
              this.filteredCount = data.filtered_count || null;
              this.filtersActive = !!data.filters_active;
              this.loading = false;
              this.errorMessage = null;
            })
            .catch(error => {
              console.error('Error fetching packages:', error);
              this.packages = [];
              this.total = 0;
              this.hasNext = false;
              this.loading = false;
              this.errorMessage = 'Failed to load search results. Please try again.';
            });
        },
        formatNumber(n) {
          if (!n && n !== 0) return '';
          if (n >= 1e6) return (n/1e6).toFixed(1) + 'M';
          if (n >= 1e3) return (n/1e3).toFixed(1) + 'K';
          return n;
        },
        formatDate(dateString) {
          if (!dateString) return '';
          return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          });
        },
        init() {
          // Initialize component
        }
      }
    }
  </script>
</body>
</html>