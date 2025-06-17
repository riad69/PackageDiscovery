<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PHP Package Discovery</title>
    <!-- Styles / Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-blue-50 text-gray-800 min-h-screen px-4 py-8">
  <div class="max-w-7xl mx-auto" x-data="packageDiscovery()" x-init="init()">

    <!-- Header -->
    <h1 class="text-3xl font-bold text-center mb-2">PHP Package Discovery</h1>
    <p class="text-center text-gray-600 mb-6">Discover PHP packages for any niche or topic using the Packagist API</p>

    <!-- Search Bar -->
    <div class="flex flex-col sm:flex-row gap-2 items-center justify-center mb-6">
      <input type="text" placeholder="Search" x-model="query"
             class="w-full sm:w-96 px-4 py-2 rounded-md border border-gray-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" @keyup.enter="search()" />
      <select class="px-4 py-2 border border-gray-300 rounded-md bg-white" x-model="filter">
        <option value="search">Search</option>
        <option value="popular">Popular</option>
      </select>
      <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md" @click="search()">Search</button>
    </div>

    <!-- Popular Searches -->
    <div class="flex flex-wrap justify-center gap-2 mb-6">
      <template x-for="tag in tags" :key="tag">
        <button
          @click="searchTag(tag)"
          :class="{'bg-blue-600 text-white': tag === activeTag, 'bg-white text-gray-700': tag !== activeTag}"
          class="px-3 py-1 rounded-full border border-gray-200 text-sm"
          x-text="tag">
        </button>
      </template>
    </div>

<!-- Filters -->
<div class="flex justify-center space-x-6 mb-6">
  <button 
    :class="{
      'bg-blue-600 text-white shadow-md': filter === 'search', 
      'bg-gray-100 text-gray-700 hover:bg-blue-50 hover:text-blue-600': filter !== 'search'
    }" 
    @click="setFilter('search')"
    class="px-6 py-2 rounded-full font-semibold text-sm uppercase tracking-wide transition-all duration-300 ease-in-out hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-opacity-50"
  >
    Search
  </button>
  <button 
    :class="{
      'bg-blue-600 text-white shadow-md': filter === 'popular', 
      'bg-gray-100 text-gray-700 hover:bg-blue-50 hover:text-blue-600': filter !== 'popular'
    }" 
    @click="setFilter('popular')"
    class="px-6 py-2 rounded-full font-semibold text-sm uppercase tracking-wide transition-all duration-300 ease-in-out hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-opacity-50"
  >
    Popular
  </button>
</div>

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

  <!-- Results Section -->
  <template x-if="!loading && (packages.length > 0 || hasSearched)">
    <div>
      <!-- Search Result Count -->
      <p class="text-sm text-gray-600 text-left mb-4" x-show="total !== null">Search Results: <strong x-text="total"></strong> packages found</p>
      <p class="text-sm text-gray-600 text-left mb-8" x-show="query || activeTag">Showing results for: <strong x-text="activeTag ? activeTag : query"></strong></p>
      <!-- Package Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
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
        <p>Page <span x-text="page"></span> (Showing <span x-text="packages.length"></span> out of <strong x-text="total"></strong> packages found)</p>
        <button class="px-4 py-2 rounded-md border bg-white hover:bg-gray-100" @click="nextPage()" :disabled="!hasNext">Next →</button>
      </div>
    </div>
  </template>

  <!-- Loading Skeleton for Search Results and Showing Results -->
  <template x-if="loading">
    <div class="mb-8 animate-pulse px-4">
      <div class="h-4 bg-gray-200 rounded w-1/3 mb-4"></div>
      <div class="h-3 bg-gray-200 rounded w-1/4 mb-8"></div>
    </div>
  </template>

  <!-- Loading Skeleton for Package Grid -->
  <template x-if="loading">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8 px-4">
      <template x-for="i in 15" :key="i">
        <div class="bg-white p-4 rounded-lg border shadow-sm animate-pulse">
          <div class="flex justify-between items-start mb-2">
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
            <div class="h-4 bg-gray-200 rounded w-6"></div>
          </div>
          <div class="h-3 bg-gray-200 rounded w-full mb-2"></div>
          <div class="h-3 bg-gray-200 rounded w-5/6 mb-4"></div>
          <div class="flex justify-between items-center text-xs text-gray-500">
            <div class="h-3 bg-gray-200 rounded w-1/4"></div>
            <div class="h-3 bg-gray-200 rounded w-1/4"></div>
          </div>
        </div>
      </template>
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
        <div class="text-gray-700 space-y-6 mt-2">
          <!-- Basic Info Section -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-semibold text-lg mb-2">Package Information</h4>
            <template x-if="loadingPackageDetails">
              <div>
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2 animate-pulse"></div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <div class="h-4 bg-gray-200 rounded w-1/2 mb-2 animate-pulse"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2 mb-2 animate-pulse"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2 mb-2 animate-pulse"></div>
                  </div>
                  <div>
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-2 animate-pulse"></div>
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
                <div class="text-center">
                  <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto mb-2 animate-pulse"></div>
                  <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                </div>
                <div class="text-center">
                  <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto mb-2 animate-pulse"></div>
                  <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                </div>
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
                <div class="text-center">
                  <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto mb-2 animate-pulse"></div>
                  <div class="h-6 bg-gray-200 rounded w-1/4 mx-auto animate-pulse"></div>
                </div>
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
          <div x-show="selectedPackage && selectedPackage.maintainers && selectedPackage.maintainers.length > 0 || loadingPackageDetails" class="bg-gray-50 p-4 rounded-lg">
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
            <template x-if="!loadingPackageDetails">
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
          <div x-show="selectedPackage && selectedPackage.versions || loadingPackageDetails" class="bg-gray-50 p-4 rounded-lg">
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
            <template x-if="!loadingPackageDetails">
              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                <template x-for="(version, index) in Object.keys(selectedPackage.versions).slice(0, 8)" :key="index">
                  <div class="bg-white px-3 py-1 rounded border text-sm text-center" x-text="version"></div>
                </template>
                <div x-show="Object.keys(selectedPackage.versions).length > 8" class="bg-white px-3 py-1 rounded border text-sm text-center text-blue-600">
                  +<span x-text="Object.keys(selectedPackage.versions).length - 8"></span> more
                </div>
              </div>
            </template>
          </div>

          <!-- Stats Collection Date -->
          <div x-show="selectedPackage && selectedPackage.date || loadingPackageDetails" class="text-sm text-gray-500 text-center">
            <template x-if="loadingPackageDetails">
              <div class="h-4 bg-gray-200 rounded w-1/3 mx-auto animate-pulse"></div>
            </template>
            <template x-if="!loadingPackageDetails">
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
  <script>
    function packageDiscovery() {
      return {
        query: '',
        filter: 'search',
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

        search() {
          this.activeTag = '';
          this.page = 1;
          this.hasSearched = true;
          this.fetchPackages();
        },
        searchTag(tag) {
          this.activeTag = tag;
          this.query = tag;
          this.page = 1;
          this.filter = 'search';
          this.hasSearched = true;
          this.fetchPackages();
        },
        setFilter(f) {
          this.filter = f;
          this.page = 1;
          this.hasSearched = true;
          this.fetchPackages();
        },
        prevPage() {
          if (this.page > 1) {
            this.page--;
            this.fetchPackages();
          }
        },
        nextPage() {
          if (this.hasNext) {
            this.page++;
            this.fetchPackages();
          }
        },
        async openPackageDetails(pkg) {
          this.loadingPackageDetails = true;
          this.selectedPackage = pkg;
          this.showModal = true;
          
          try {
            // Extract vendor and package name from the package name
            const [vendor, package] = pkg.name.split('/');
            const response = await fetch(`/api/packagist/package/${vendor}/${package}`);
            const data = await response.json();
            
            if (response.ok) {
              // Merge the detailed data with the existing package data
              this.selectedPackage = {
                ...this.selectedPackage,
                ...data,
                downloads: {
                  ...this.selectedPackage.downloads,
                  ...data.downloads
                }
              };
            } else {
              console.error('Error fetching package details:', data.error);
            }
          } catch (error) {
            console.error('Error fetching package details:', error);
          } finally {
            this.loadingPackageDetails = false;
          }
        },
        fetchPackages() {
          // Handle blank search: If query/activeTag are empty for a search filter, revert to initial state
          if (this.filter === 'search' && !this.query && !this.activeTag) {
            this.packages = [];
            this.total = null;
            this.hasNext = false;
            this.loading = false;
            this.hasSearched = false; // Revert to initial state
            return; // Stop execution here
          }

          this.loading = true;
          let url = '';
          if (this.filter === 'popular') {
            url = `/api/packagist/popular?page=${this.page}`;
          } else {
            let params = `?page=${this.page}`;
            if (this.activeTag) params += `&tag=${encodeURIComponent(this.activeTag)}`;
            else if (this.query) params += `&q=${encodeURIComponent(this.query)}`;
            url = `/api/packagist/search${params}`;
          }
          fetch(url)
            .then(res => res.json())
            .then(data => {
              if (this.filter === 'popular') {
                this.packages = data.packages || [];
                this.total = data.total || 0;
                this.hasNext = !!data.next;
              } else {
                this.packages = data.results || [];
                this.total = data.total || 0;
                this.hasNext = !!data.next;
              }
              this.loading = false;
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
          // Do not fetchPackages() on load!
        }
      }
    }
  </script>
</body>
</html>
