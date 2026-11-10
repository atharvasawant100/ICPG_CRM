<?php

namespace Tests\Unit;

use App\Support\NumberHelper;
use PHPUnit\Framework\TestCase;

class NumberHelperTest extends TestCase
{
    public function test_format_indian_compact_thousands(): void
    {
        $this->assertEquals('1k', NumberHelper::formatIndianCompact(1000));
        $this->assertEquals('90k', NumberHelper::formatIndianCompact(90000));
        $this->assertEquals('25k', NumberHelper::formatIndianCompact(24999.99));
        $this->assertEquals('24k', NumberHelper::formatIndianCompact(23999.99));
        $this->assertEquals('12.5k', NumberHelper::formatIndianCompact(12500));
        $this->assertEquals('12.55k', NumberHelper::formatIndianCompact(12550));
    }

    public function test_format_indian_compact_lakhs(): void
    {
        $this->assertEquals('1L', NumberHelper::formatIndianCompact(100000));
        $this->assertEquals('1.9L', NumberHelper::formatIndianCompact(190000));
        $this->assertEquals('25L', NumberHelper::formatIndianCompact(2500000));
        $this->assertEquals('25.5L', NumberHelper::formatIndianCompact(2550000));
    }

    public function test_format_indian_compact_crores(): void
    {
        $this->assertEquals('1Cr', NumberHelper::formatIndianCompact(10000000));
        $this->assertEquals('2.5Cr', NumberHelper::formatIndianCompact(25000000));
    }

    public function test_format_indian_compact_under_one_thousand(): void
    {
        $this->assertEquals('0', NumberHelper::formatIndianCompact(0));
        $this->assertEquals('500', NumberHelper::formatIndianCompact(500));
        $this->assertEquals('999', NumberHelper::formatIndianCompact(999));
        $this->assertEquals('99.50', NumberHelper::formatIndianCompact(99.5));
    }

    public function test_format_indian_compact_negative_numbers(): void
    {
        $this->assertEquals('-1k', NumberHelper::formatIndianCompact(-1000));
        $this->assertEquals('-90k', NumberHelper::formatIndianCompact(-90000));
        $this->assertEquals('-1.9L', NumberHelper::formatIndianCompact(-190000));
    }

    public function test_rupee_indian_prepends_symbol(): void
    {
        $this->assertEquals('₹90k', NumberHelper::rupeeIndian(90000));
        $this->assertEquals('₹1.9L', NumberHelper::rupeeIndian(190000));
        $this->assertEquals('₹1k', NumberHelper::rupeeIndian(1000));
        $this->assertEquals('₹25k', NumberHelper::rupeeIndian(24999.99));
        $this->assertEquals('₹24k', NumberHelper::rupeeIndian(23999.99));
    }
}
