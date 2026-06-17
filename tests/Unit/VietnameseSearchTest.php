<?php

namespace Tests\Unit;

use App\Support\VietnameseSearch;
use PHPUnit\Framework\TestCase;

class VietnameseSearchTest extends TestCase
{
    public function test_normalize_unicode_form(): void
    {
        $nfd = "ca\u{0300} chua";
        $nfc = 'cà chua';

        $this->assertSame($nfc, VietnameseSearch::normalize($nfd));
    }

    public function test_is_too_short_uses_multibyte_length(): void
    {
        $this->assertTrue(VietnameseSearch::isTooShort('ứ'));
        $this->assertFalse(VietnameseSearch::isTooShort('ức'));
    }
}
