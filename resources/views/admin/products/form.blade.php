@php($editing = $product->exists)
<x-layouts.admin :title="$editing ? 'Edit product' : 'Add a product'" :subtitle="$editing ? $product->name : 'Products placed across your store'" :crumbs="['Products' => route('admin.products.index')]">
    <x-slot:actions>
        <a href="{{ route('admin.products.index') }}" class="btn btn-soft">Discard</a>
        @if ($editing)
            <a href="{{ route('products.show', $product) }}" class="btn btn-soft" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i>View</a>
        @endif
        <button type="submit" form="product-form" class="btn btn-primary">{{ $editing ? 'Save product' : 'Publish product' }}</button>
    </x-slot:actions>

    <form id="product-form" method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="row g-4 g-xl-5">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="col-xl-8">
            <h2 class="h4 fw-extrabold mb-2">Product title</h2>
            <x-form.input name="name" label="Name" :value="$product->name" required maxlength="255" placeholder="Write title here…" class="mb-4" />
            <x-form.input name="slug" label="URL slug" :value="$product->slug" help="Leave blank to generate it from the name." class="mb-4" />

            <h2 class="h4 fw-extrabold mb-2">Product description</h2>
            <x-form.textarea name="description" label="Description" :value="$product->description" rows="9" class="mb-4" />

            <h2 class="h4 fw-extrabold mb-2">Display image</h2>
            <div class="panel panel-body d-flex flex-wrap align-items-center gap-4 mb-4">
                <img id="image-preview" src="{{ $product->imageUrl() }}" alt="" class="thumb" style="width: 8rem; height: 8rem">
                <div class="flex-grow-1">
                    <x-form.input name="image" label="Upload image" type="file" accept="image/jpeg,image/png,image/webp" data-image-preview="image-preview" help="JPG, PNG or WebP, up to 4 MB." class="mb-2" />
                    @if ($product->image_path)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                            <label class="form-check-label small" for="remove_image">Remove current image</label>
                        </div>
                    @endif
                </div>
            </div>

            <h2 class="h4 fw-extrabold mb-2">Inventory &amp; pricing</h2>
            <div class="panel panel-body">
                <div class="row">
                    <x-form.input class="col-md-4" name="price" label="Price" type="number" step="0.01" min="0" :prefix="setting('currency_symbol')" :value="$product->price" required />
                    <x-form.input class="col-md-4" name="compare_at_price" label="Compare-at price" type="number" step="0.01" min="0" :prefix="setting('currency_symbol')" :value="$product->compare_at_price" help="Shows as a sale when higher than price." />
                    <x-form.input class="col-md-4" name="stock" label="Stock" type="number" min="0" :value="$product->stock ?? 0" required />
                    <x-form.input class="col-md-6 mb-0" name="sku" label="SKU" :value="$product->sku" required maxlength="64" />
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="panel panel-body mb-4">
                <h2 class="h4 fw-extrabold mb-3">Organize</h2>
                <x-form.select name="category_id" label="Category" required placeholder="Choose a category…" :value="$product->category_id"
                    :options="$departments->mapWithKeys(fn ($d) => [$d->name => $d->categories->pluck('name', 'id')->all()])->all()" />
                <x-form.check name="is_active" label="Active (visible in store)" :checked="$product->is_active" />
                <x-form.check name="is_featured" label="Featured" :checked="$product->is_featured" help="Shows in Featured sliders." class="mb-0" />
            </div>
        </div>
    </form>
    @if ($editing)
        <div class="mt-3 text-xl-end">
            <x-delete-button :action="route('admin.products.destroy', $product)" :confirm="'Delete '.$product->name.'? Past orders keep their copy of it.'" label="Delete product" />
        </div>
    @endif
</x-layouts.admin>
