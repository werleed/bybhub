@extends('layouts.admin')
@section('title', 'CSV Product Import')
@section('content')

<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.products.index') }}" class="w-9 h-9 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition">
            <i class="fas fa-arrow-left text-sm text-gray-600"></i>
        </a>
        <div>
            <h1 class="text-xl font-black text-gray-900" style="font-family:Poppins,sans-serif">Bulk Import from AliExpress CSV</h1>
            <p class="text-sm text-gray-500 mt-0.5">Upload an AliExpress product export CSV to import multiple products at once.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-start gap-3">
        <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
        <p class="text-sm text-green-800 font-semibold">{{ session('success') }}</p>
    </div>
    @endif

    {{-- How to get the CSV --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
        <h3 class="font-bold text-blue-800 text-sm flex items-center gap-2 mb-2">
            <i class="fas fa-info-circle"></i> How to export from AliExpress
        </h3>
        <ol class="text-xs text-blue-700 space-y-1 list-decimal list-inside">
            <li>Open any AliExpress product search results page</li>
            <li>Use a browser extension like <strong>AliExpress Product CSV Exporter</strong> or <strong>Exportify</strong></li>
            <li>Export as CSV — the file should have columns: Title, Sale price, Main images url, Product description html, Attribute 1/2</li>
            <li>Upload the CSV here — all prices convert from NGN → CNY automatically using your live exchange rate</li>
        </ol>
        <p class="text-xs text-blue-600 mt-2 font-semibold">
            ✅ This CSV from the document above has <strong>12 products</strong> ready to import.
        </p>
    </div>

    {{-- Upload form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5"
         x-data="{ file: null, rows: 0, dragging: false }">

        <form action="{{ route('admin.products.csv-import.process') }}" method="POST"
              enctype="multipart/form-data"
              @submit.prevent="if(!file){ alert('Please select a CSV file.'); return; } $el.submit()">
            @csrf

            {{-- Drop zone --}}
            <div class="mb-4"
                 @dragover.prevent="dragging=true"
                 @dragleave="dragging=false"
                 @drop.prevent="dragging=false; file=$event.dataTransfer.files[0]; $refs.fileInput.files=$event.dataTransfer.files">
                <label :class="dragging ? 'border-primary bg-primary/5' : 'border-gray-300 hover:border-primary'"
                       class="flex flex-col items-center justify-center border-2 border-dashed rounded-2xl p-8 cursor-pointer transition">
                    <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-file-csv text-primary text-2xl"></i>
                    </div>
                    <p class="font-bold text-gray-700 text-sm">
                        <span x-show="!file">Drop your CSV here, or click to browse</span>
                        <span x-show="file" x-text="'Selected: ' + file.name" class="text-primary"></span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">AliExpress export CSV · max 10MB</p>
                    <input type="file" name="csv_file" accept=".csv,text/csv" x-ref="fileInput"
                           @change="file=$event.target.files[0]" class="hidden">
                </label>
                @error('csv_file')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Category --}}
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                    Default Category <span class="text-gray-400 font-normal">(optional — applied to all imported products)</span>
                </label>
                <select name="category_id"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary transition">
                    <option value="">— Uncategorised —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->getTranslatedName() }}</option>
                    @endforeach
                </select>
            </div>

            {{-- What gets imported --}}
            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                <p class="text-xs font-bold text-gray-600 mb-2">What gets imported from each row:</p>
                <div class="grid grid-cols-2 gap-1.5 text-xs text-gray-500">
                    @foreach([
                        ['Title','Product title','text-green-600'],
                        ['Sale price','Converted NGN → CNY','text-green-600'],
                        ['Main images url','Up to 12 images','text-green-600'],
                        ['Product description html','Stripped to plain text','text-green-600'],
                        ['Attribute 1 & 2','Color / Size variants','text-green-600'],
                        ['Product url','Source URL for reference','text-green-600'],
                    ] as [$col,$desc,$color])
                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-check text-{{ str_replace('text-','',$color) }} text-[10px]"></i>
                        <span><strong>{{ $col }}</strong> — {{ $desc }}</span>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-amber-600 mt-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Duplicate products (same source URL) are skipped automatically.
                </p>
            </div>

            <button type="submit"
                    class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3.5 rounded-xl text-sm transition flex items-center justify-center gap-2">
                <i class="fas fa-upload"></i> Import Products from CSV
            </button>
        </form>
    </div>

    {{-- Also link to URL scraper --}}
    <div class="text-center text-sm text-gray-500">
        Want to import one product at a time?
        <a href="{{ route('admin.products.scrape') }}" class="text-accent font-bold hover:underline ml-1">
            Use the URL scraper →
        </a>
    </div>

</div>
@endsection
