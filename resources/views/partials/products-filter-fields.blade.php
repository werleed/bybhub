{{-- Shared filter fields used by both desktop sidebar and mobile drawer --}}
{{-- Wrap in a <form> before including this partial --}}

<div class="space-y-5">
    {{-- Category --}}
    <div>
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Category</p>
        <div class="space-y-0.5 max-h-52 overflow-y-auto pr-1">
            <a href="{{ route('products.index') }}"
               class="flex justify-between items-center text-sm py-1.5 px-2 rounded-lg transition {{ !isset($category) ? 'text-primary font-bold bg-primary/5' : 'text-gray-600 hover:bg-gray-50' }}">
                All Products
                <i class="fas fa-chevron-right text-[9px] opacity-40"></i>
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('products.category', $cat->slug) }}"
               class="flex justify-between items-center text-sm py-1.5 px-2 rounded-lg transition {{ isset($category) && $category->id === $cat->id ? 'text-primary font-bold bg-primary/5' : 'text-gray-600 hover:bg-gray-50' }}">
                <span class="truncate">{{ $cat->getTranslatedName() }}</span>
                <span class="text-xs text-gray-400 flex-shrink-0 ml-1 bg-gray-100 px-1.5 py-0.5 rounded-full">{{ $cat->products_count }}</span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Source --}}
    <div>
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Source</p>
        <div class="grid grid-cols-2 gap-1.5">
            @foreach(['' => 'All', '1688' => '1688.com', 'taobao' => 'Taobao', 'alibaba' => 'Alibaba', 'aliexpress' => 'AliExpress', 'manual' => 'BYBHUB'] as $val => $label)
            <a href="{{ route('products.index', array_merge(request()->except('source','page'), $val ? ['source' => $val] : [])) }}"
               class="text-center text-xs font-semibold py-1.5 px-2 rounded-lg border transition
                      {{ request('source', '') === $val ? 'bg-primary text-white border-primary' : 'bg-white text-gray-600 border-gray-200 hover:border-primary hover:text-primary' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- In stock --}}
    <label class="flex items-center gap-2.5 cursor-pointer group">
        <div class="relative">
            <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }}
                   class="sr-only peer">
            <div class="w-9 h-5 bg-gray-200 peer-checked:bg-primary rounded-full transition-colors"></div>
            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
        </div>
        <span class="text-sm text-gray-600 group-hover:text-primary transition">In Stock Only</span>
    </label>

    {{-- Price range --}}
    <div>
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Price ({{ $symbol ?? '₦' }})</p>
        <div class="flex gap-2">
            <input type="number" name="min_price" placeholder="Min" value="{{ request('min_price') }}"
                class="w-1/2 border-2 border-gray-100 bg-gray-50 rounded-xl px-2.5 py-2 text-xs focus:outline-none focus:border-primary transition">
            <input type="number" name="max_price" placeholder="Max" value="{{ request('max_price') }}"
                class="w-1/2 border-2 border-gray-100 bg-gray-50 rounded-xl px-2.5 py-2 text-xs focus:outline-none focus:border-primary transition">
        </div>
    </div>
</div>
