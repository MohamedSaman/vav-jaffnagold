<?php

namespace Database\Seeders;

use App\Models\WebsiteContent;
use Illuminate\Database\Seeder;

class WebsiteContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seeds = [
            [
                'key' => 'company_name',
                'value' => 'Jaffna Gold',
                'type' => 'text',
                'description' => 'The brand name of the shop',
            ],
            [
                'key' => 'phone',
                'value' => '+94 21 222 2222',
                'type' => 'text',
                'description' => 'Contact phone number displayed on website',
            ],
            [
                'key' => 'email',
                'value' => 'info@jaffnagold.com',
                'type' => 'text',
                'description' => 'Contact email displayed on website',
            ],
            [
                'key' => 'address',
                'value' => '123 Hospital Road, Jaffna, Sri Lanka',
                'type' => 'text',
                'description' => 'Physical store address',
            ],
            [
                'key' => 'announcement_text',
                'value' => 'Complimentary Shipping On Signature Orders | Handcrafted Luxury From Jaffna',
                'type' => 'text',
                'description' => 'Text shown in the top header announcement bar',
            ],
            [
                'key' => 'hero_headline',
                'value' => 'Heritage & Modern Elegance',
                'type' => 'text',
                'description' => 'Main headline on the homepage hero banner',
            ],
            [
                'key' => 'hero_subheadline',
                'value' => 'Curated pieces crafted with generational artistry and modern perfection.',
                'type' => 'text',
                'description' => 'Subheadline on the homepage hero banner',
            ],
            [
                'key' => 'about_intro',
                'value' => 'Since our inception, Jaffna Gold has represented the peak of Jaffna craftsmanship. Every piece of jewelry tells a story of heritage, dedication, and precision.',
                'type' => 'text',
                'description' => 'Introduction paragraph on the About Us page',
            ],
            [
                'key' => 'about_details',
                'value' => 'Our artisans carry forward techniques that have been refined over generations. We combine 22K gold hallmarked purity with contemporary designs, ensuring each ornament is a timeless masterpiece.',
                'type' => 'text',
                'description' => 'Detailed paragraph on the About Us page',
            ],
            [
                'key' => 'footer_copyright',
                'value' => '© 2026 Jaffna Gold. All Rights Reserved.',
                'type' => 'text',
                'description' => 'Copyright notice in the footer',
            ]
        ];

        foreach ($seeds as $seed) {
            WebsiteContent::updateOrCreate(
                ['key' => $seed['key']],
                [
                    'value' => $seed['value'],
                    'type' => $seed['type'],
                    'description' => $seed['description']
                ]
            );
        }
    }
}
