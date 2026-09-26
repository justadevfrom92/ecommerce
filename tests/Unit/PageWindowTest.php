<?php

namespace Tests\Unit;

use App\Support\PageWindow;
use PHPUnit\Framework\TestCase;

class PageWindowTest extends TestCase
{
    public function test_shows_every_page_when_few(): void
    {
        $this->assertSame([1, 2, 3, 4, 5], PageWindow::make(3, 5));
        $this->assertSame(range(1, 9), PageWindow::make(1, 9));
    }

    public function test_start_of_long_list(): void
    {
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, null, 20], PageWindow::make(1, 20));
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, null, 20], PageWindow::make(4, 20));
    }

    public function test_middle_of_long_list(): void
    {
        $this->assertSame([1, null, 8, 9, 10, 11, 12, null, 20], PageWindow::make(10, 20));
    }

    public function test_end_of_long_list(): void
    {
        $this->assertSame([1, null, 14, 15, 16, 17, 18, 19, 20], PageWindow::make(20, 20));
        $this->assertSame([1, null, 14, 15, 16, 17, 18, 19, 20], PageWindow::make(17, 20));
    }
}
