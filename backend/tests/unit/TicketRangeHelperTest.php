<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @coversNothing
 */
final class TicketRangeHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('cias');
    }

    public function testParseRangesProducesBounds(): void
    {
        $ranges = parseTicketRanges('100-102, 105, 200-202');

        $this->assertCount(3, $ranges);
        $this->assertSame(['start' => 100, 'end' => 102], $ranges[0]);
        $this->assertSame(['start' => 105, 'end' => 105], $ranges[1]);
        $this->assertSame(['start' => 200, 'end' => 202], $ranges[2]);
    }

    public function testIsTicketInRangeChecksBoundaries(): void
    {
        $rangeString = '100-110,200-210';

        $this->assertTrue(isTicketInRange(100, $rangeString));
        $this->assertTrue(isTicketInRange(205, $rangeString));
        $this->assertFalse(isTicketInRange(115, $rangeString));
        $this->assertFalse(isTicketInRange(300, $rangeString));
    }

    public function testEmptyValueReturnsEmptyArray(): void
    {
        $this->assertSame([], parseTicketRanges(''));
    }
}