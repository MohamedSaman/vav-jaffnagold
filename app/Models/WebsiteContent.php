<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteContent extends Model
{
    use HasFactory;

    protected $table = 'website_contents';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a website content value by key, with a fallback.
     */
    public static function getValue(string $key, $default = null)
    {
        $content = self::where('key', $key)->first();
        if (! $content) {
            return $default;
        }

        if ($content->type === 'json') {
            return json_decode($content->value, true) ?: $default;
        }

        return $content->value;
    }

    /**
     * Set a website content value by key.
     */
    public static function setValue(string $key, $value, string $type = 'text', ?string $description = null)
    {
        $val = is_array($value) ? json_encode($value) : $value;

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $val,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /* ═══════════════════════════════════════════════════════════════════
       1. WEBSITE PAYMENT METHODS CONFIGURATION
       ═══════════════════════════════════════════════════════════════════ */

    public static function getDefaultPaymentMethods(): array
    {
        return [
            [
                'id' => 'cod',
                'code' => 'COD',
                'name' => 'Cash On Delivery (COD)',
                'description' => 'Pay cash when your jewelry parcel is safely delivered to your doorstep.',
                'instructions' => 'Please keep the exact cash amount ready for the delivery courier upon arrival.',
                'is_active' => true,
                'is_cod' => true,
                'icon' => 'bi-cash-coin',
                'badge' => 'Available Islandwide',
            ],
            [
                'id' => 'card',
                'code' => 'Card',
                'name' => 'Credit / Debit Card',
                'description' => 'Pay online securely using Visa, MasterCard, or American Express.',
                'instructions' => 'Card details are processed with encrypted 256-bit SSL security.',
                'is_active' => true,
                'is_cod' => false,
                'icon' => 'bi-credit-card-2-front',
                'badge' => 'Instant & Secure',
            ],
            [
                'id' => 'bank_transfer',
                'code' => 'Bank Transfer',
                'name' => 'Direct Bank Transfer',
                'description' => 'Direct deposit / online bank transfer to our Jaffna Gold Commercial Bank account.',
                'instructions' => "Bank: Commercial Bank of Ceylon\nBranch: Jaffna Main Branch\nAccount Name: Jaffna Gold (Pvt) Ltd\nAccount Number: 1000234567\n*Please share your deposit slip on WhatsApp (+94 76 191 9650) with your Order ID.",
                'is_active' => true,
                'is_cod' => false,
                'icon' => 'bi-bank',
                'badge' => 'Direct Deposit',
            ],
        ];
    }

    public static function getPaymentMethods(): array
    {
        try {
            $methods = self::getValue('payment_methods');
            if (is_array($methods) && ! empty($methods)) {
                return $methods;
            }
        } catch (\Throwable $e) {
            // DB fallback
        }

        return self::getDefaultPaymentMethods();
    }

    public static function getActivePaymentMethods(): array
    {
        $all = self::getPaymentMethods();

        return array_values(array_filter($all, fn ($m) => ! empty($m['is_active'])));
    }

    public static function savePaymentMethods(array $methods): void
    {
        self::setValue('payment_methods', $methods, 'json', 'Website checkout payment methods configuration');
    }

    /* ═══════════════════════════════════════════════════════════════════
       2. SHIPPING & VARIABLE DELIVERY CHARGES CONFIGURATION
       ═══════════════════════════════════════════════════════════════════ */

    public static array $allCountries = [
        'Sri Lanka',
        'United Kingdom',
        'Canada',
        'Australia',
        'United States',
        'France',
        'Germany',
        'Switzerland',
        'Norway',
        'India',
        'United Arab Emirates',
        'Singapore',
        'Malaysia',
        'New Zealand',
        'Italy',
        'Netherlands',
        'Sweden',
        'Denmark',
        'Qatar',
        'Saudi Arabia',
        'Kuwait',
        'Oman',
        'Bahrain',
        'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Austria', 'Azerbaijan',
        'Bahamas', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi',
        'Cambodia', 'Cameroon', 'Cape Verde', 'Central African Republic', 'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo', 'Costa Rica', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic',
        'Djibouti', 'Dominica', 'Dominican Republic', 'East Timor', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Eswatini', 'Ethiopia',
        'Fiji', 'Finland', 'Gabon', 'Gambia', 'Georgia', 'Ghana', 'Greece', 'Grenada', 'Guatemala', 'Guinea', 'Guyana',
        'Haiti', 'Honduras', 'Hungary', 'Iceland', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Ivory Coast',
        'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Kosovo', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg',
        'Madagascar', 'Malawi', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius', 'Mexico', 'Micronesia', 'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco', 'Mozambique', 'Myanmar',
        'Namibia', 'Nauru', 'Nepal', 'Nicaragua', 'Niger', 'Nigeria', 'North Korea', 'North Macedonia',
        'Pakistan', 'Palau', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Poland', 'Portugal',
        'Romania', 'Russia', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Korea', 'South Sudan', 'Spain', 'Suriname', 'Syria',
        'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Togo', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Tuvalu',
        'Uganda', 'Ukraine', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe',
    ];

    public static function getDefaultShippingSettings(): array
    {
        return [
            'shipping_enabled' => true, // Access to STOP shipping charges (all orders free)
            'default_charge' => 450.00, // Standard islandwide courier fee for Sri Lanka
            'local_charge' => 250.00, // Variable local delivery in Jaffna
            'local_cities' => 'Jaffna, Nallur, Chavakachcheri, Point Pedro, Chunnakam, Karainagar, Velanai, Kayts, Valvettithurai, Kopay, Tellippalai, Manipay, Vaddukoddai, Atchuvely, Kodikamam, Sandilipay, Pandateruppu, Uduvil, Maruthankerny, Ilavalai',
            'free_shipping_enabled' => true,
            'free_shipping_threshold' => 15000.00, // Free delivery for orders over LKR 15,000 (Sri Lanka)
            'estimated_days' => '2 - 4 Business Days Islandwide',
            'custom_rates' => [], // Variable domestic city rates: [['city' => 'Colombo', 'rate' => 500]]

            // International Country-Specific Shipping
            'international_default_charge' => 4500.00, // Default fee for unlisted countries
            'international_estimated_days' => '5 - 10 Business Days via DHL Express',
            'country_rates' => [
                ['country' => 'United Kingdom', 'rate' => 4500.00],
                ['country' => 'Canada', 'rate' => 5500.00],
                ['country' => 'Australia', 'rate' => 5000.00],
                ['country' => 'United States', 'rate' => 5500.00],
                ['country' => 'France', 'rate' => 4500.00],
                ['country' => 'Germany', 'rate' => 4500.00],
                ['country' => 'Switzerland', 'rate' => 5000.00],
                ['country' => 'Norway', 'rate' => 5200.00],
                ['country' => 'India', 'rate' => 3000.00],
                ['country' => 'United Arab Emirates', 'rate' => 3500.00],
                ['country' => 'Singapore', 'rate' => 3800.00],
                ['country' => 'Malaysia', 'rate' => 3800.00],
                ['country' => 'New Zealand', 'rate' => 5200.00],
                ['country' => 'Italy', 'rate' => 4500.00],
                ['country' => 'Netherlands', 'rate' => 4500.00],
                ['country' => 'Sweden', 'rate' => 4800.00],
                ['country' => 'Denmark', 'rate' => 4800.00],
                ['country' => 'Qatar', 'rate' => 3500.00],
                ['country' => 'Saudi Arabia', 'rate' => 3800.00],
                ['country' => 'Kuwait', 'rate' => 3800.00],
            ],
        ];
    }

    public static function getShippingSettings(): array
    {
        try {
            $saved = self::getValue('shipping_settings');
            if (is_array($saved) && ! empty($saved)) {
                return array_merge(self::getDefaultShippingSettings(), $saved);
            }
        } catch (\Throwable $e) {
            // DB fallback
        }

        return self::getDefaultShippingSettings();
    }

    public static function saveShippingSettings(array $settings): void
    {
        self::setValue('shipping_settings', $settings, 'json', 'Website shipping and variable delivery rates configuration');
    }

    public static function calculateShippingCharge(float $subtotal, ?string $city = null, ?string $country = 'Sri Lanka'): array
    {
        $settings = self::getShippingSettings();
        $targetCountry = trim($country ?: 'Sri Lanka');

        // 1. If shipping charges are stopped/disabled:
        if (empty($settings['shipping_enabled'])) {
            return [
                'amount' => 0.0,
                'is_free' => true,
                'label' => 'Free Delivery',
                'reason' => 'Store Promotional Free Delivery',
            ];
        }

        // 2. International Country-Specific Shipping:
        if (strcasecmp($targetCountry, 'Sri Lanka') !== 0) {
            $countryRates = $settings['country_rates'] ?? [];
            $matchedRate = null;

            if (is_array($countryRates)) {
                foreach ($countryRates as $cr) {
                    if (strcasecmp(trim($cr['country'] ?? ''), $targetCountry) === 0 && isset($cr['rate'])) {
                        $matchedRate = (float) $cr['rate'];
                        break;
                    }
                }
            }

            $intlDays = $settings['international_estimated_days'] ?? '5 - 10 Business Days via Courier';

            if ($matchedRate !== null) {
                return [
                    'amount' => $matchedRate,
                    'is_free' => ($matchedRate <= 0),
                    'label' => $targetCountry.' International Courier',
                    'reason' => $intlDays,
                ];
            }

            // Fallback to default international rate
            $defaultIntl = (float) ($settings['international_default_charge'] ?? 4500);

            return [
                'amount' => $defaultIntl,
                'is_free' => ($defaultIntl <= 0),
                'label' => $targetCountry.' International Courier',
                'reason' => $intlDays,
            ];
        }

        // 3. Domestic (Sri Lanka) Delivery:
        $freeEnabled = ! empty($settings['free_shipping_enabled']);
        $threshold = (float) ($settings['free_shipping_threshold'] ?? 0);
        if ($freeEnabled && $threshold > 0 && $subtotal >= $threshold) {
            return [
                'amount' => 0.0,
                'is_free' => true,
                'label' => 'Free Delivery',
                'reason' => 'Free Delivery on orders over LKR '.number_format($threshold),
            ];
        }

        $charge = (float) ($settings['default_charge'] ?? 450);
        $label = 'Standard Delivery (Sri Lanka)';
        $reason = $settings['estimated_days'] ?? 'Islandwide Delivery';

        if (! empty($city)) {
            $cleanCity = trim(strtolower($city));

            // Check custom rates first
            if (! empty($settings['custom_rates']) && is_array($settings['custom_rates'])) {
                foreach ($settings['custom_rates'] as $cr) {
                    if (strtolower(trim($cr['city'] ?? '')) === $cleanCity && isset($cr['rate'])) {
                        $rate = (float) $cr['rate'];

                        return [
                            'amount' => $rate,
                            'is_free' => ($rate <= 0),
                            'label' => ($cr['city'] ?? $city).' Rate',
                            'reason' => $settings['estimated_days'] ?? 'Delivered by courier',
                        ];
                    }
                }
            }

            // Check Jaffna local delivery
            $localCities = array_map('trim', explode(',', strtolower($settings['local_cities'] ?? '')));
            if (in_array($cleanCity, $localCities)) {
                $charge = (float) ($settings['local_charge'] ?? 250);
                $label = 'Jaffna Local Delivery';
                $reason = 'Fast 1-2 Day Local Courier';
            }
        }

        return [
            'amount' => $charge,
            'is_free' => ($charge <= 0),
            'label' => $label,
            'reason' => $reason,
        ];
    }

    /* ═══════════════════════════════════════════════════════════════════
       3. ORDER & STORE SETTINGS CONFIGURATION
       ═══════════════════════════════════════════════════════════════════ */

    public static function getDefaultOrderSettings(): array
    {
        return [
            'orders_enabled' => true, // Master switch: accept orders or stop/pause
            'paused_message' => 'Online orders are temporarily paused for routine maintenance. You can still browse our collections and contact us via WhatsApp to place orders.',
            'min_order_enabled' => false,
            'min_order_amount' => 1000.00,
            'allow_guest_checkout' => true, // Instant checkout without prior registration
            'auto_confirm_orders' => false,
            'notification_email' => 'orders@jaffnagold.com',
            'notification_phone' => '+94 76 191 9650',
            'support_whatsapp' => '+94 76 191 9650',
            'delivery_note' => 'Carefully hand-packaged in luxury tamper-proof gift box with hallmarked purity certificate.',
        ];
    }

    public static function getOrderSettings(): array
    {
        try {
            $saved = self::getValue('order_settings');
            if (is_array($saved) && ! empty($saved)) {
                return array_merge(self::getDefaultOrderSettings(), $saved);
            }
        } catch (\Throwable $e) {
            // DB fallback
        }

        return self::getDefaultOrderSettings();
    }

    public static function saveOrderSettings(array $settings): void
    {
        self::setValue('order_settings', $settings, 'json', 'Website order placement and checkout controls');
    }
}
