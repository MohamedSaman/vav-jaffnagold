<div class="container-fluid py-4">
    <!-- Header Block -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark font-inter">
                <i class="bi bi-sliders2 text-primary me-2"></i> Custom Products & Pricing Setup
            </h1>
            <p class="text-muted mb-0">Manage custom jewelry pieces (Kodi, Bangles, Rings), set piece prices and available sizes/lengths.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">

            <!-- Add Product Button -->
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" wire:click="openCreateModal">
                <i class="bi bi-plus-lg"></i>
                <span>Add Custom Product</span>
            </button>

            <!-- Link to Requests -->
            <a href="{{ route('admin.custom-requests') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-inbox"></i>
                <span>View Inquiries</span>
            </a>
        </div>
    </div>

    <!-- Search & Category Filters -->
    <div class="card border shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Search custom jewelry by name, Tamil name, or category..." wire:model.live.debounce.300ms="search">
                        @if($search)
                            <button class="btn btn-outline-secondary border-start-0" type="button" wire:click="$set('search', '')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <select class="form-select" wire:model.live="selectedCategory">
                        <option value="all">All Categories</option>
                        <option value="kodi">Kodi (Traditional Chains)</option>
                        <option value="bangle">SL Bangles (Lock & Spring)</option>
                        <option value="ring">Rings (Thakar & Traditional)</option>
                        <option value="bridal_thali">Bridal Thali Sets</option>
                        <option value="necklace">Necklaces & Sets</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Product List Table -->
    <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0 text-dark">
                <i class="bi bi-collection text-primary me-2"></i> Custom Products Catalogue ({{ $products->total() }})
            </h5>
            <div class="text-muted small">
                Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} items
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 text-muted" style="letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-4" style="width: 70px;">Item</th>
                        <th>Name & Details</th>
                        <th>Category</th>
                        <th>Available Sizes / Lengths</th>
                        <th>Price Range</th>
                        <th>Inquiries</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="ps-4">
                                <div class="position-relative d-inline-block">
                                    @if($product->image)
                                        <img src="{{ asset($product->image) }}" class="rounded border shadow-sm" style="width: 50px; height: 50px; object-fit: cover;" alt="{{ $product->name }}">
                                    @else
                                        <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px;">
                                            <i class="bi bi-gem"></i>
                                        </div>
                                    @endif
                                    @if(!empty($product->gallery_images) && count($product->gallery_images) > 0)
                                        <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-dark text-white border" style="font-size: 0.65rem; transform: translate(25%, 25%);" title="{{ count($product->gallery_images) }} additional gallery photos">
                                            +{{ count($product->gallery_images) }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $product->name }}</div>
                                @if($product->tamil_name)
                                    <div class="text-warning-emphasis fw-medium small">{{ $product->tamil_name }}</div>
                                @endif
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    {{ $product->purity }} • Est. {{ $product->making_days }} days crafting
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">{{ $product->category_label }}</span>
                            </td>

                            <td>
                                @if(!empty($product->pown_options))
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($product->pown_options as $opt)
                                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.72rem;">{{ $opt }}{{ $product->category === 'kodi' ? '"' : '' }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">Standard</span>
                                @endif
                            </td>

                            <td>
                                <div class="fw-bold text-success font-inter">
                                    LKR {{ number_format($product->price_range_min, 0) }}
                                </div>
                                <div class="text-muted small">
                                    to LKR {{ number_format($product->price_range_max, 0) }}
                                </div>
                            </td>

                            <td>
                                @if($product->inquiries_count > 0)
                                    <a href="{{ route('admin.custom-requests', ['search' => $product->name]) }}" class="badge bg-primary text-white text-decoration-none">
                                        {{ $product->inquiries_count }} requests
                                    </a>
                                @else
                                    <span class="text-muted small">0</span>
                                @endif
                            </td>

                            <td>
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="activeSwitch{{ $product->id }}" 
                                           {{ $product->is_active ? 'checked' : '' }} wire:click="toggleActive({{ $product->id }})">
                                    <label class="form-check-label small {{ $product->is_active ? 'text-success fw-semibold' : 'text-muted' }}" for="activeSwitch{{ $product->id }}">
                                        {{ $product->is_active ? 'Active' : 'Disabled' }}
                                    </label>
                                </div>
                                @if($product->is_featured)
                                    <span class="badge bg-warning text-dark" style="font-size: 0.65rem;"><i class="bi bi-star-fill"></i> Featured</span>
                                @endif
                            </td>

                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1" wire:click="openEditModal({{ $product->id }})">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Set Price & Specs</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-gem display-4 d-block mb-3 text-secondary"></i>
                                <div class="h5 fw-bold text-dark">No custom products found</div>
                                <p class="mb-0 text-muted">Try adjusting your search criteria or add a new custom piece.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Edit / Create Product Modal -->
    @if($showProductModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1055;" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <!-- Modal Header -->
                    <div class="modal-header bg-dark text-white py-3 px-4" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                        <h5 class="modal-title fw-bold mb-0 text-white font-inter">
                            <i class="bi bi-sliders2 text-warning me-2"></i>
                            {{ $isEditing ? 'Edit Custom Product & Pricing: ' . $name : 'Add New Custom Product' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeProductModal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-4">
                        <form wire:submit.prevent="saveProduct" enctype="multipart/form-data">
                            <div class="row g-3">
                                <!-- Name (English) -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Product Title (English) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Star Kodi" wire:model="name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Name (Tamil) -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Tamil Title (தமிழ் பெயர்)</label>
                                    <input type="text" class="form-control @error('tamil_name') is-invalid @enderror" placeholder="e.g. ஸ்டார் கொடி" wire:model="tamil_name">
                                    @error('tamil_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Category Identifier -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Category Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category') is-invalid @enderror" wire:model="category">
                                        <option value="kodi">kodi (Chains / கொடி)</option>
                                        <option value="bangle">bangle (SL Bangles / வளையல்)</option>
                                        <option value="ring">ring (Rings / மோதிரம்)</option>
                                        <option value="bridal_thali">bridal_thali (Bridal Thali Sets / தாலி)</option>
                                        <option value="necklace">necklace (Necklaces & Attigai)</option>
                                    </select>
                                    @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Category Label -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Category Display Label <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('category_label') is-invalid @enderror" placeholder="e.g. Kodi (Traditional Chains)" wire:model="category_label">
                                    @error('category_label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Gold Purity & Making Days -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Gold Purity <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('purity') is-invalid @enderror" placeholder="22K (916 Hallmarked)" wire:model="purity">
                                    @error('purity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Crafting Timeframe (Days) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('making_days') is-invalid @enderror" min="1" max="90" wire:model="making_days">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    @error('making_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Price Range (Min & Max LKR) -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Minimum Price (LKR) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">LKR</span>
                                        <input type="number" step="100" class="form-control fw-bold text-success @error('price_range_min') is-invalid @enderror" placeholder="e.g. 4500" wire:model="price_range_min">
                                    </div>
                                    @error('price_range_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Maximum Price (LKR) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">LKR</span>
                                        <input type="number" step="100" class="form-control fw-bold text-success @error('price_range_max') is-invalid @enderror" placeholder="e.g. 8500" wire:model="price_range_max">
                                    </div>
                                    @error('price_range_max') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Available Sizes / Options -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-dark">Available Sizes / Lengths (comma-separated)</label>
                                    <input type="text" class="form-control @error('pown_options_input') is-invalid @enderror" placeholder="e.g. 20, 22, 24, 26 or 2.4, 2.6, 2.8" wire:model="pown_options_input">
                                    <div class="form-text small text-muted">Customer size/length options displayed on the website.</div>
                                    @error('pown_options_input') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Main Image & Multiple Gallery Uploads -->
                                <div class="col-12 mt-3 p-3 bg-light rounded border">
                                    <div class="row g-3">
                                        <!-- Main / Cover Photograph -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
                                                <span>Cover / Main Photograph</span>
                                                @if($current_image || $image_file)
                                                    <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" wire:click="removeMainImage">
                                                        <i class="bi bi-trash"></i> Remove Cover
                                                    </button>
                                                @endif
                                            </label>
                                            <input type="file" class="form-control form-control-sm @error('image_file') is-invalid @enderror" wire:model="image_file" accept="image/*">
                                            @error('image_file') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                            <div class="mt-2">
                                                <div wire:loading wire:target="image_file" class="text-primary small mb-1">
                                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span> Uploading cover photo...
                                                </div>
                                                @if($image_file)
                                                    @php
                                                        try {
                                                            $previewUrl = $image_file->temporaryUrl();
                                                        } catch (\Throwable $e) {
                                                            $previewUrl = null;
                                                        }
                                                    @endphp
                                                    @if($previewUrl)
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img src="{{ $previewUrl }}" class="rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;" alt="Cover Preview">
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle">New Cover Photo</span>
                                                        </div>
                                                    @endif
                                                @elseif($current_image)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="{{ asset($current_image) }}" class="rounded border shadow-sm" style="width: 60px; height: 60px; object-fit: cover;" alt="Current Cover">
                                                        <span class="badge bg-secondary-subtle text-secondary border">Current Cover</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Multiple Gallery Images Upload -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-dark d-flex justify-content-between align-items-center">
                                                <span>Gallery Images (Upload multiple photos)</span>
                                                <span class="badge bg-primary-subtle text-primary">Multi-upload</span>
                                            </label>
                                            <input type="file" multiple class="form-control form-control-sm @error('gallery_files.*') is-invalid @enderror" wire:model="gallery_files" accept="image/*">
                                            <div class="form-text small text-muted">You can select more than 2 images at once.</div>
                                            @error('gallery_files.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                                            <div wire:loading wire:target="gallery_files" class="text-primary small my-2">
                                                <span class="spinner-border spinner-border-sm me-1" role="status"></span> Uploading gallery images...
                                            </div>
                                        </div>

                                        <!-- Gallery Previews Grid (Current + Newly Selected) -->
                                        <div class="col-12">
                                            @php
                                                $hasGallery = (!empty($current_gallery) && count($current_gallery) > 0) || (!empty($gallery_files) && count($gallery_files) > 0);
                                            @endphp
                                            @if($hasGallery)
                                                <label class="form-label fw-bold small text-dark mb-2">
                                                    Gallery Image Previews (Options to preview and remove):
                                                </label>
                                                <div class="d-flex flex-wrap gap-2 p-2 bg-white rounded border">
                                                    {{-- Saved Gallery Images --}}
                                                    @foreach($current_gallery as $idx => $gImg)
                                                        <div class="position-relative border rounded p-1 bg-light text-center" style="width: 82px;" wire:key="curr-gal-{{ $idx }}">
                                                            <img src="{{ asset($gImg) }}" class="rounded" style="width: 72px; height: 72px; object-fit: cover;" alt="Gallery {{ $idx + 1 }}">
                                                            <button type="button" 
                                                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 rounded-circle d-flex align-items-center justify-content-center shadow"
                                                                    style="width: 20px; height: 20px; transform: translate(30%, -30%); font-size: 11px;"
                                                                    wire:click="removeGalleryImage({{ $idx }})"
                                                                    title="Remove this photo">
                                                                <i class="bi bi-x"></i>
                                                            </button>
                                                            <span class="badge bg-secondary text-white mt-1" style="font-size: 0.65rem;">#{{ $idx + 1 }}</span>
                                                        </div>
                                                    @endforeach

                                                    {{-- Newly Uploaded Gallery Files --}}
                                                    @foreach($gallery_files as $idx => $gFile)
                                                        @php
                                                            try {
                                                                $tmpGUrl = $gFile->temporaryUrl();
                                                            } catch (\Throwable $e) {
                                                                $tmpGUrl = null;
                                                            }
                                                        @endphp
                                                        @if($tmpGUrl)
                                                            <div class="position-relative border border-primary rounded p-1 bg-primary-subtle text-center" style="width: 82px;" wire:key="new-gal-{{ $idx }}">
                                                                <img src="{{ $tmpGUrl }}" class="rounded" style="width: 72px; height: 72px; object-fit: cover;" alt="New Upload {{ $idx + 1 }}">
                                                                <button type="button" 
                                                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 rounded-circle d-flex align-items-center justify-content-center shadow"
                                                                        style="width: 20px; height: 20px; transform: translate(30%, -30%); font-size: 11px;"
                                                                        wire:click="removeNewGalleryFile({{ $idx }})"
                                                                        title="Cancel this upload">
                                                                    <i class="bi bi-x"></i>
                                                                </button>
                                                                <span class="badge bg-primary text-white mt-1" style="font-size: 0.65rem;">New</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Sort Order & Switches -->
                                <div class="col-md-6 mt-3">
                                    <label class="form-label fw-semibold small text-dark">Display Sort Order</label>
                                    <input type="number" class="form-control" wire:model="sort_order">

                                    <div class="d-flex gap-4 mt-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="modalActive" wire:model="is_active">
                                            <label class="form-check-label fw-semibold small" for="modalActive">Show on Storefront</label>
                                        </div>

                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="modalFeatured" wire:model="is_featured">
                                            <label class="form-check-label fw-semibold small" for="modalFeatured">Feature on Top</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-md-6 mt-3">
                                    <label class="form-label fw-semibold small text-dark">Description & Craftsmanship Details</label>
                                    <textarea class="form-control" rows="3" placeholder="Handcrafted with traditional Jaffna goldsmith techniques..." wire:model="description"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer px-0 pb-0 mt-4 border-top pt-3">
                                <button type="button" class="btn btn-secondary" wire:click="closeProductModal">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled" wire:target="saveProduct,image_file,gallery_files">
                                    <span wire:loading.remove wire:target="saveProduct,image_file,gallery_files">Save Changes</span>
                                    <span wire:loading wire:target="saveProduct">Saving...</span>
                                    <span wire:loading wire:target="image_file">Uploading photo...</span>
                                    <span wire:loading wire:target="gallery_files">Uploading gallery photos...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
