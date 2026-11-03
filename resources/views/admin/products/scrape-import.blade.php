@extends('layouts.admin')
@section('title', 'Import Product via URL')
@section('content')

<div class="max-w-3xl mx-auto space-y-6" x-data="scraperImport()" x-init="init()">

    {{-- Page header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.products.index') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition">
            <i class="fas fa-arrow-left text-sm text-gray-600"></i>
        </a>
        <div>
            <h1 class="text-xl font-black text-gray-900" style="font-family:Poppins,sans-serif">Import Product via URL</h1>
            <p class="text-sm text-gray-500 mt-0.5">Paste an AliExpress, Alibaba, 1688, or Taobao link — we'll auto-fill the product details.</p>
        </div>
    </div>

    {{-- URL fetch bar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Product URL</label>
        <div class="flex gap-3">
            <div class="flex-1 relative">
                <i class="fas fa-link absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                <input type="url" x-model="url"
                       @keydown.enter.prevent="fetch()"
                       placeholder="https://www.aliexpress.com/item/... or alibaba.com / 1688.com / taobao.com"
                       class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm focus:outline-none focus:border-primary transition">
            </div>
            <button @click="fetch()" :disabled="loading || !url"
                    class="flex-shrink-0 bg-primary hover:bg-primary-dark disabled:opacity-50 text-white font-bold px-5 py-3 rounded-xl text-sm transition flex items-center gap-2">
                <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-cloud-download-alt'"></i>
                <span x-text="loading ? 'Fetching…' : 'Fetch'"></span>
            </button>
        </div>

        {{-- Source badges --}}
        <div class="flex flex-wrap gap-2 mt-3">
            @foreach(['AliExpress' => '#e44d26', 'Alibaba' => '#ff6600', '1688.com' => '#e2231a', 'Taobao' => '#ff5000'] as $src => $color)
            <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full text-white" style="background:{{ $color }}">
                <i class="fas fa-check-circle text-[9px]"></i> {{ $src }}
            </span>
            @endforeach
        </div>

        {{-- Error state --}}
        <div x-show="error" x-transition class="mt-4 bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700 flex items-start gap-2">
            <i class="fas fa-exclamation-circle text-red-400 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="font-semibold" x-text="error"></p>
                <p class="text-xs mt-0.5 text-red-600">You can still fill in the details manually below.</p>
            </div>
        </div>
    </div>

    {{-- Product form (shown after fetch or when manual) --}}
    <div x-show="showForm" x-transition class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-5">

        <div class="flex items-center justify-between">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-box text-primary text-sm"></i> Product Details
            </h2>
            <span x-show="scraped.source" class="text-xs font-bold px-2.5 py-1 rounded-full bg-primary/10 text-primary" x-text="'Source: ' + scraped.source"></span>
        </div>

        {{-- Image preview --}}
        <div x-show="scraped.images && scraped.images.length">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Images <span class="font-normal text-gray-400">(first 8)</span></p>
            <div class="flex gap-2 flex-wrap">
                <template x-for="(img, i) in scraped.images.slice(0,8)" :key="i">
                    <div class="relative group">
                        <img :src="img" class="w-16 h-16 rounded-xl object-cover border-2 border-gray-100" loading="lazy">
                        <button type="button" @click="removeImage(i)"
                                class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full text-[9px] hidden group-hover:flex items-center justify-center">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <form action="{{ route('admin.products.scrape.store') }}" method="POST" @submit.prevent="submitForm($event)">
            @csrf

            {{-- Hidden fields for scraped data --}}
            <input type="hidden" name="source"      :value="scraped.source">
            <input type="hidden" name="source_url"  :value="scraped.source_url || url">
            <input type="hidden" name="images"      :value="JSON.stringify(scraped.images || [])">
            <input type="hidden" name="variants"    :value="JSON.stringify(scraped.variants || [])">

            <div class="space-y-4">
                {{-- Title --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Title *</label>
                    <input type="text" name="title" x-model="scraped.title" required
                           class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition"
                           placeholder="Product title">
                </div>

                {{-- Price + Category --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Price CNY *</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm">¥</span>
                            <input type="number" name="price_cny" x-model="scraped.price_cny" step="0.01" min="0" required
                                   class="w-full border-2 border-gray-200 rounded-xl pl-8 pr-4 py-2.5 text-sm focus:outline-none focus:border-primary transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Weight (kg)</label>
                        <input type="number" name="weight_kg" x-model="scraped.weight_kg" step="0.01" min="0"
                               class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">MOQ</label>
                        <input type="number" name="moq" x-model="scraped.moq" min="1"
                               class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition">
                    </div>
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Category</label>
                    <select name="category_id"
                            class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition">
                        <option value="">— Uncategorised —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->getTranslatedName() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Description</label>
                    <textarea name="description" x-model="scraped.description" rows="4"
                              class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition resize-none"
                              placeholder="Product description..."></textarea>
                </div>

                {{-- Variants summary --}}
                <div x-show="scraped.variants && scraped.variants.length">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                        Variants detected — <span x-text="scraped.variants ? scraped.variants.length : 0"></span> prop(s)
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="v in (scraped.variants || [])" :key="v.name">
                            <span class="text-xs bg-primary/10 text-primary font-semibold px-3 py-1 rounded-full" x-text="v.name + ' (' + (v.options?.length || 0) + ')'"></span>
                        </template>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Variants are saved automatically. You can refine them after import.</p>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex gap-3 pt-2">
                <button type="submit" :disabled="saving"
                        class="flex-1 bg-primary hover:bg-primary-dark disabled:opacity-50 text-white font-bold py-3 rounded-xl text-sm transition flex items-center justify-center gap-2">
                    <i class="fas" :class="saving ? 'fa-spinner fa-spin' : 'fa-check-circle'"></i>
                    <span x-text="saving ? 'Saving…' : 'Import & Save Product'"></span>
                </button>
                <a href="{{ route('admin.products.index') }}" class="px-5 py-3 border-2 border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:border-gray-300 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- Manual entry fallback --}}
    <div x-show="!showForm" class="text-center py-10 text-gray-400">
        <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-link text-2xl text-gray-300"></i>
        </div>
        <p class="font-medium text-sm">Paste a product URL above and click Fetch</p>
        <button @click="showForm=true; scraped.source='manual'"
                class="mt-3 text-sm text-accent hover:underline font-semibold">
            Or enter product details manually →
        </button>
    </div>

</div>

@push('scripts')
<script>
function scraperImport() {
    return {
        url: '',
        loading: false,
        saving: false,
        error: null,
        showForm: false,
        scraped: {
            source: '', source_url: '', title: '', price_cny: '', price_max: null,
            weight_kg: '', moq: 1, description: '', images: [], variants: [],
            price_note: null
        },

        init() {
            // Pre-fill URL from query string if present
            const params = new URLSearchParams(location.search);
            if (params.get('url')) { this.url = params.get('url'); this.fetch(); }
        },

        async fetch() {
            if (!this.url) return;
            this.loading = true;
            this.error   = null;
            this.showForm = false;
            try {
                const res = await fetch('{{ route('admin.products.scrape.preview') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ url: this.url }),
                });
                const data = await res.json();
                if (data.ok) {
                    this.scraped = {
                        source:      data.source      || 'manual',
                        source_url:  data.source_url  || this.url,
                        title:       data.title       || '',
                        price_cny:   data.price_cny   || '',
                        price_max:   data.price_max   || null,
                        weight_kg:   data.weight_kg   || '',
                        moq:         data.moq         || 1,
                        description: data.description || '',
                        images:      data.images      || [],
                        variants:    data.variants    || [],
                        price_note:  data.price_note  || null,
                    };
                    this.showForm = true;
                } else {
                    this.error    = data.message || 'Could not fetch product data.';
                    this.showForm = data.manual || false;
                    if (this.showForm) {
                        this.scraped.source     = data.source     || 'manual';
                        this.scraped.source_url = data.url        || this.url;
                    }
                }
            } catch (e) {
                this.error    = 'Network error. Please check your connection and try again.';
                this.showForm = true;
                this.scraped.source     = 'manual';
                this.scraped.source_url = this.url;
            }
            this.loading = false;
        },

        removeImage(index) {
            this.scraped.images.splice(index, 1);
        },

        async submitForm(event) {
            this.saving = true;
            event.target.submit();
        }
    };
}
</script>
@endpush

@endsection
