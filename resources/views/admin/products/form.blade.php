@php($editing = $product->exists)
<x-layouts.admin :title="$editing ? 'Edit product' : 'Add product'">
    <x-slot:actions>
        @if ($editing)
            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i>View in store</a>
            <x-delete-button :action="route('admin.products.destroy', $product)" :confirm="'Delete '.$product->name.'? Past orders keep their copy of it.'" />
        @endif
    </x-slot:actions>

    <form method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="row g-4">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <x-form.input name="name" label="Name" :value="$product->name" required maxlength="255" />
                    <x-form.input name="slug" label="URL slug" :value="$product->slug" help="Leave blank to generate it from the name." />
                    <x-form.textarea name="description" label="Description" :value="$product->description" rows="8" />
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Pricing & inventory</h2>
                    <div class="row">
                        <x-form.input class="col-md-4" name="price" label="Price" type="number" step="0.01" min="0" :prefix="setting('currency_symbol')" :value="$product->price" required />
                        <x-form.input class="col-md-4" name="compare_at_price" label="Compare-at price" type="number" step="0.01" min="0" :prefix="setting('currency_symbol')" :value="$product->compare_at_price" help="Shows as a sale when higher than price." />
                        <x-form.input class="col-md-4" name="stock" label="Stock" type="number" min="0" :value="$product->stock ?? 0" required />
                        <x-form.input class="col-md-6" name="sku" label="SKU" :value="$product->sku" required maxlength="64" />
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Visibility</h2>
                    <x-form.check name="is_active" label="Active (visible in store)" :checked="$product->is_active" />
                    <x-form.check name="is_featured" label="Featured" :checked="$product->is_featured" help="Shows in Featured sliders." />
                    <x-form.select name="category_id" label="Category" required placeholder="Choose a category…" :value="$product->category_id"
                        :options="$departments->mapWithKeys(fn ($d) => [$d->name => $d->categories->pluck('name', 'id')->all()])->all()" />
                </div>
            </div>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Image</h2>
                    <div class="ratio ratio-1x1 bg-body-tertiary rounded mb-3">
                        <img id="image-preview" src="{{ $product->imageUrl() }}" alt="" class="object-fit-cover rounded">
                    </div>
                    <x-form.input name="image" label="Upload image" type="file" accept="image/jpeg,image/png,image/webp" data-image-preview="image-preview" help="JPG, PNG or WebP, up to 4 MB." />
                    @if ($product->image_path)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                            <label class="form-check-label small" for="remove_image">Remove current image</label>
                        </div>
                    @endif
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">{{ $editing ? 'Save product' : 'Create product' }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-link">Back to products</a>
            </div>
        </div>
    </form>
</x-layouts.admin>
