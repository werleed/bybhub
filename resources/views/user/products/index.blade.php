@extends('layouts.app')
@section('title', isset($category) ? $category->getTranslatedName() : __('app.products'))
@section('meta_description', isset($category) ? 'Buy ' . $category->getTranslatedName() . ' from China via BYBHUB.' : 'Browse products from China. Electronics, Fashion, Home and more.')

@push('styles')
<style>
.prod-card{transition:transform .22s cubic-bezier(.4,0,.2,1),box-shadow .22s cubic-bezier(.4,0,.2,1)}
.prod-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(26,58,107,0.14)}
.prod-card:active{transform:scale(.98)}
@keyframes shimmer-sweep{0%{background-position:-400px 0}100%{background-position:400px 0}}
.skel{background:linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);background-size:400px 100%;animation:shimmer-sweep 1.4s infinite}
</style>
@endpush

@section('content')
<div x-data="{filterOpen:false,viewMode:localStorage.getItem('bybhub_view')||'grid',setView(v){this.viewMode=v;localStorage.setItem('bybhub_view',v)}}"
     @keydown.escape.window="filterOpen=false">

    {{-- Mobile filter overlay --}}
    <div x-show="filterOpen" x-transition:enter="transition" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         @click="filterOpen=false" class="fixed inset-0 bg-black/50 z-40 lg:hidden" style="display:none"></div>

    {{-- Mobile filter drawer --}}
    <div x-show="filterOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-x-full"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="-translate-x-full opacity-0"
         class="fixed left-0 top-0 bottom-0 w-72 bg-white z-50 shadow-2xl overflow-y-auto lg:hidden" style="display:none">
        <div class="flex items-center justify-between p-4 border-b border-gray-100 sticky top-0 bg-white">
            <h2 class="font-bold text-primary flex items-center gap-2"><i class="fas fa-sliders-h text-sm"></i> Filters</h2>
            <button @click="filterOpen=false" class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center"><i class="fas fa-times text-sm text-gray-500"></i></button>
        </div>
        <form method="GET" action="{{ route('products.index') }}" class="p-4 space-y-5">
            @include('partials.products-filter-fields')
            <button type="submit" class="w-full bg-primary text-white py-2.5 rounded-xl text-sm font-bold transition">Apply</button>
            <a href="{{ route('products.index') }}" class="block text-center text-xs text-gray-400 hover:text-primary mt-1">Clear all filters</a>
        </form>
    </div>

    <div class="flex gap-6">

        {{-- Desktop sidebar --}}
        <aside class="hidden lg:block w-56 flex-shrink-0">
            <div class="sticky top-24">
                <form method="GET" action="{{ route('products.index') }}" class="space-y-4">
                    <div class="bg-white rounded-2xl shadow-card p-4">
                        <h3 class="font-bold text-primary text-sm mb-4 flex items-center gap-2">
                            <i class="fas fa-filter text-xs"></i> Filters
                        </h3>
                        @include('partials.products-filter-fields')
                        <div class="pt-3 space-y-2">
                            <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white py-2.5 rounded-xl text-xs font-bold transition">Apply Filters</button>
                            <a href="{{ route('products.index') }}" class="block text-center text-xs text-gray-400 hover:text-primary hover:underline transition">Clear All</a>
                        </div>
                    </div>
                </form>
            </div>
        </aside>

        {{-- Products main area --}}
        <div class="flex-1 min-w-0">

            {{-- China / Local tabs --}}
            <div class="flex items-center gap-1 bg-gray-100 rounded-2xl p-1 mb-5">
                <a href="{{ route('products.index', array_merge(request()->except('type','page'), ['type'=>'china'])) }}"
                    class="flex-1 text-center py-2.5 rounded-xl text-sm font-bold transition {{ !$isLocal ? 'bg-white shadow text-primary' : 'text-gray-500 hover:text-gray-700' }}">
                    🇨🇳 China Imports
                </a>
                <a href="{{ route('products.index', array_merge(request()->except('type','page'), ['type'=>'local'])) }}"
                    class="flex-1 text-center py-2.5 rounded-xl text-sm font-bold transition {{ $isLocal ? 'bg-white shadow text-primary' : 'text-gray-500 hover:text-gray-700' }}">
                    🇳🇬 Local Marketplace
                </a>
            </div>

            {{-- Toolbar --}}
            <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    {{-- Mobile filter button --}}
                    <button @click="filterOpen=true"
                            class="lg:hidden inline-flex items-center gap-2 px-4 py-2 bg-white border-2 border-gray-200 rounded-xl text-sm font-semibold text-gray-700 hover:border-primary hover:text-primary transition shadow-sm">
                        <i class="fas fa-sliders-h text-xs"></i> Filters
                        @if(request()->hasAny(['category','source','min_price','max_price','in_stock']))
                        <span class="w-2 h-2 bg-accent rounded-full"></span>
                        @endif
                    </button>
                    <div>
                        <h2 class="font-bold text-gray-800 text-base">
                            {{ isset($category) ? $category->getTranslatedName() : 'All Products' }}
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">{{ number_format($products->total()) }} products</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    {{-- View toggle --}}
                    <div class="hidden sm:flex items-center bg-gray-100 rounded-xl p-1 gap-1">
                        <button @click="setView('grid')" :class="viewMode==='grid'?'bg-white shadow text-primary':'text-gray-400 hover:text-gray-600'"
                                class="w-8 h-7 rounded-lg flex items-center justify-center transition text-xs">
                            <i class="fas fa-th"></i>
                        </button>
                        <button @click="setView('list')" :class="viewMode==='list'?'bg-white shadow text-primary':'text-gray-400 hover:text-gray-600'"
                                class="w-8 h-7 rounded-lg flex items-center justify-center transition text-xs">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                    {{-- Sort --}}
                    <div class="relative">
                        <i class="fas fa-sort absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        <select onchange="window.location.href='{{ route('products.index') }}?sort='+this.value+'{{ request('q') ? '&q='.urlencode(request('q')) : '' }}{{ isset($category) ? '&category='.$category->slug : '' }}'"
                            class="pl-8 pr-8 py-2 bg-white border-2 border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-primary transition appearance-none cursor-pointer">
                            @foreach(['newest'=>'Newest','price_asc'=>'Price ↑','price_desc'=>'Price ↓','popular'=>'Popular','trending'=>'Trending'] as $val=>$label)
                            <option value="{{ $val }}" {{ request('sort')===$val?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] pointer-events-none"></i>
                    </div>
                </div>
            </div>

            {{-- Active filter chips --}}
            @if(request()->hasAny(['q','source','min_price','max_price','in_stock']) || isset($category))
            <div class="flex flex-wrap gap-2 mb-4">
                @if(request('q'))
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-primary/10 text-primary px-3 py-1.5 rounded-full">
                    <i class="fas fa-search text-[10px]"></i> "{{ request('q') }}"
                    <a href="{{ request()->fullUrlWithoutQuery(['q']) }}" class="text-primary/60 hover:text-primary ml-0.5">×</a>
                </span>
                @endif
                @if(isset($category))
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-accent/10 text-accent px-3 py-1.5 rounded-full">
                    <i class="fas fa-tag text-[10px]"></i> {{ $category->getTranslatedName() }}
                    <a href="{{ route('products.index') }}" class="text-accent/60 hover:text-accent ml-0.5">×</a>
                </span>
                @endif
                @if(request('in_stock'))
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-green-100 text-green-700 px-3 py-1.5 rounded-full">
                    <i class="fas fa-check text-[10px]"></i> In Stock
                    <a href="{{ request()->fullUrlWithoutQuery(['in_stock']) }}" class="text-green-500 hover:text-green-700 ml-0.5">×</a>
                </span>
                @endif
            </div>
            @endif

            @if($products->isEmpty())
            <div class="text-center py-20 text-gray-400 bg-white rounded-2xl shadow-card">
                <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-3xl text-gray-300"></i>
                </div>
                <p class="font-bold text-gray-600 text-base">No products found</p>
                <p class="text-sm mt-2">Try a different search or <a href="{{ route('user.scraper.index') }}" class="text-accent hover:underline font-semibold">paste a product URL</a></p>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 mt-5 bg-primary text-white text-sm font-bold px-5 py-2.5 rounded-xl hover:bg-primary-dark transition">
                    <i class="fas fa-arrow-left text-xs"></i> Browse All Products
                </a>
            </div>
            @else

            {{-- Grid / List view --}}
            <div :class="viewMode==='grid'
                ? 'grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4'
                : 'flex flex-col gap-3'">
                @foreach($products as $product)
                <div :class="viewMode==='list' ? 'w-full' : ''" class="prod-card">
                    @include('partials.product-card', ['product'=>$product,'symbol'=>$symbol,'currency'=>$currency,'listMode'=>false])
                </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $products->links() }}</div>
            @endif

        </div>
    </div>
</div>
@endsection
