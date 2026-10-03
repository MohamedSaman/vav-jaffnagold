<?php

namespace App\Livewire\Ecommerce\Admin;

use App\Models\WebsiteContent;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('E-Commerce Website Content')]
#[Layout('components.layouts.admin')]
class ContentManagement extends Component
{
    // Content properties
    public $company_name = '';
    public $phone = '';
    public $email = '';
    public $address = '';
    public $announcement_text = '';
    public $hero_headline = '';
    public $hero_subheadline = '';
    public $about_intro = '';
    public $about_details = '';
    public $footer_copyright = '';

    protected $rules = [
        'company_name' => 'required|string|max:255',
        'phone' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'address' => 'required|string',
        'announcement_text' => 'required|string',
        'hero_headline' => 'required|string',
        'hero_subheadline' => 'required|string',
        'about_intro' => 'required|string',
        'about_details' => 'required|string',
        'footer_copyright' => 'required|string',
    ];

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $this->company_name = WebsiteContent::getValue('company_name', 'Jaffna Gold');
        $this->phone = WebsiteContent::getValue('phone', '+94 21 222 2222');
        $this->email = WebsiteContent::getValue('email', 'info@jaffnagold.com');
        $this->address = WebsiteContent::getValue('address', '123 Hospital Road, Jaffna, Sri Lanka');
        $this->announcement_text = WebsiteContent::getValue('announcement_text', 'Complimentary Shipping On Signature Orders | Handcrafted Luxury From Jaffna');
        $this->hero_headline = WebsiteContent::getValue('hero_headline', 'Heritage & Modern Elegance');
        $this->hero_subheadline = WebsiteContent::getValue('hero_subheadline', 'Curated pieces crafted with generational artistry and modern perfection.');
        $this->about_intro = WebsiteContent::getValue('about_intro', 'Since our inception, Jaffna Gold has represented the peak of Jaffna craftsmanship. Every piece of jewelry tells a story of heritage, dedication, and precision.');
        $this->about_details = WebsiteContent::getValue('about_details', 'Our artisans carry forward techniques that have been refined over generations. We combine 22K gold hallmarked purity with contemporary designs, ensuring each ornament is a timeless masterpiece.');
        $this->footer_copyright = WebsiteContent::getValue('footer_copyright', '© 2026 Jaffna Gold. All Rights Reserved.');
    }

    public function saveContent()
    {
        $this->validate();

        WebsiteContent::setValue('company_name', $this->company_name, 'text', 'The brand name of the shop');
        WebsiteContent::setValue('phone', $this->phone, 'text', 'Contact phone number displayed on website');
        WebsiteContent::setValue('email', $this->email, 'text', 'Contact email displayed on website');
        WebsiteContent::setValue('address', $this->address, 'text', 'Physical store address');
        WebsiteContent::setValue('announcement_text', $this->announcement_text, 'text', 'Text shown in the top header announcement bar');
        WebsiteContent::setValue('hero_headline', $this->hero_headline, 'text', 'Main headline on the homepage hero banner');
        WebsiteContent::setValue('hero_subheadline', $this->hero_subheadline, 'text', 'Subheadline on the homepage hero banner');
        WebsiteContent::setValue('about_intro', $this->about_intro, 'text', 'Introduction paragraph on the About Us page');
        WebsiteContent::setValue('about_details', $this->about_details, 'text', 'Detailed paragraph on the About Us page');
        WebsiteContent::setValue('footer_copyright', $this->footer_copyright, 'text', 'Copyright notice in the footer');

        $this->dispatch('showToast', message: 'Website content saved successfully!');
    }

    public function render()
    {
        return view('livewire.ecommerce.admin.content-management');
    }
}
