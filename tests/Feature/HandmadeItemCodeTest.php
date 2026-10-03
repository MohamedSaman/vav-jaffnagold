<?php

namespace Tests\Feature;

use App\Models\HandmadeItem;
use Tests\TestCase;

class HandmadeItemCodeTest extends TestCase
{
    public function test_it_generates_code_with_product_name_and_sequence(): void
    {
        $code = HandmadeItem::generateItemCode('KODI');
        $this->assertEquals('SL-KODI-001', $code);
    }

    public function test_it_formats_lowercase_and_multi_word_names(): void
    {
        $code = HandmadeItem::generateItemCode('peacock ring');
        $this->assertEquals('SL-PEACOCK-RING-001', $code);

        $code2 = HandmadeItem::generateItemCode('6KN KODI');
        $this->assertEquals('SL-6KN-KODI-001', $code2);
    }

    public function test_it_handles_special_characters_and_whitespace(): void
    {
        $code = HandmadeItem::generateItemCode('  KODI #1 / Gold  ');
        $this->assertEquals('SL-KODI-1-GOLD-001', $code);
    }

    public function test_it_falls_back_to_item_when_name_is_empty_or_null(): void
    {
        $this->assertStringStartsWith('SL-ITEM-', HandmadeItem::generateItemCode(''));
        $this->assertStringStartsWith('SL-ITEM-', HandmadeItem::generateItemCode(null));
    }

    public function test_it_increments_sequence_when_codes_are_excluded_in_batch(): void
    {
        $code1 = HandmadeItem::generateItemCode('KODI', []);
        $this->assertEquals('SL-KODI-001', $code1);

        $code2 = HandmadeItem::generateItemCode('KODI', [$code1]);
        $this->assertEquals('SL-KODI-002', $code2);

        $code3 = HandmadeItem::generateItemCode('KODI', [$code1, $code2]);
        $this->assertEquals('SL-KODI-003', $code3);

        // A different product in the same batch starts at 001
        $codeRing = HandmadeItem::generateItemCode('RING', [$code1, $code2, $code3]);
        $this->assertEquals('SL-RING-001', $codeRing);
    }

    public function test_it_keeps_code_length_safe_for_very_long_product_names(): void
    {
        $longName = 'Very Extra Super Long Luxury Royal Traditional Necklace Handcrafted Gold';
        $code = HandmadeItem::generateItemCode($longName);
        $this->assertLessThanOrEqual(50, strlen($code));
        $this->assertStringStartsWith('SL-', $code);
    }
}
