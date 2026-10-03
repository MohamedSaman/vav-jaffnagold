<div class="container-fluid py-4">
    <!-- Header Block -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-dark font-inter">E-Commerce Website Content</h1>
            <p class="text-muted mb-0">Update text sections, store information, and settings shown on the e-commerce website.</p>
        </div>
    </div>

    <!-- Content Card -->
    <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title fw-bold mb-0 text-dark">Edit Content Settings</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="saveContent">
                <div class="row g-4">
                    <!-- General Settings Section -->
                    <div class="col-12 border-bottom pb-4">
                        <h5 class="fw-bold text-jg-blue mb-3"><i class="bi bi-info-circle me-2"></i> General Info & Header</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Company Name</label>
                                <input type="text" class="form-control" wire:model="company_name">
                                @error('company_name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Announcement Bar Text</label>
                                <input type="text" class="form-control" wire:model="announcement_text">
                                @error('announcement_text') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contact Phone</label>
                                <input type="text" class="form-control" wire:model="phone">
                                @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contact Email</label>
                                <input type="email" class="form-control" wire:model="email">
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Physical Store Address</label>
                                <input type="text" class="form-control" wire:model="address">
                                @error('address') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Homepage Banner Section -->
                    <div class="col-12 border-bottom pb-4">
                        <h5 class="fw-bold text-jg-blue mb-3"><i class="bi bi-image me-2"></i> Homepage Hero Banner</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Hero Headline</label>
                                <input type="text" class="form-control" wire:model="hero_headline">
                                @error('hero_headline') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Hero Subheadline</label>
                                <textarea class="form-control" rows="2" wire:model="hero_subheadline"></textarea>
                                @error('hero_subheadline') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- About Page Section -->
                    <div class="col-12 border-bottom pb-4">
                        <h5 class="fw-bold text-jg-blue mb-3"><i class="bi bi-file-earmark-person me-2"></i> About Us Page Details</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">About Us Intro Paragraph</label>
                                <textarea class="form-control" rows="3" wire:model="about_intro"></textarea>
                                @error('about_intro') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">About Us Details Paragraph</label>
                                <textarea class="form-control" rows="3" wire:model="about_details"></textarea>
                                @error('about_details') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Footer Section -->
                    <div class="col-12 pb-2">
                        <h5 class="fw-bold text-jg-blue mb-3"><i class="bi bi-hr me-2"></i> Footer & Layout Details</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Footer Copyright Text</label>
                                <input type="text" class="form-control" wire:model="footer_copyright">
                                @error('footer_copyright') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-4 mt-3">
                    <button type="button" class="btn btn-outline-secondary px-4" wire:click="loadSettings">Reset Changes</button>
                    <button type="submit" class="btn btn-primary px-4 bg-jg-blue text-white" style="border: none;">Save Content Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
