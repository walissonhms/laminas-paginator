<?php

/**
 * @see       https://github.com/laminas/laminas-paginator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-paginator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-paginator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Paginator\ScrollingStyle;

use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Paginator
 * @covers  Laminas\Paginator\ScrollingStyle\Jumping<extended>
 */
class JumpingTest extends TestCase
{
    /**
     * @var \Laminas\Paginator\ScrollingStyle\Jumping
     */
    private $scrollingStyle;
    /**
     * @var \Laminas\Paginator\Paginator
     */
    private $paginator;

    private $expectedRange;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->scrollingStyle = new \Laminas\Paginator\ScrollingStyle\Jumping();
        $this->paginator = new Paginator(new ArrayAdapter(range(1, 101)));
        $this->paginator->setItemCountPerPage(10);
        $this->paginator->setPageRange(10);
        $this->expectedRange = array_combine(range(1, 10), range(1, 10));
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown(): void
    {
        $this->scrollingStyle = null;
        $this->paginator = null;
        parent::tearDown();
    }

    public function testGetsPagesInRangeForFirstPage()
    {
        $this->paginator->setCurrentPageNumber(1);
        $actual = $this->scrollingStyle->getPages($this->paginator);
        $this->assertEquals($this->expectedRange, $actual);
    }

    public function testGetsPagesInRangeForSecondPage()
    {
        $this->paginator->setCurrentPageNumber(2);
        $actual = $this->scrollingStyle->getPages($this->paginator);
        $this->assertEquals($this->expectedRange, $actual);
    }

    public function testGetsPagesInRangeForSecondLastPage()
    {
        $this->paginator->setCurrentPageNumber(10);
        $actual = $this->scrollingStyle->getPages($this->paginator);
        $this->assertEquals($this->expectedRange, $actual);
    }

    public function testGetsPagesInRangeForLastPage()
    {
        $this->paginator->setCurrentPageNumber(11);
        $actual = $this->scrollingStyle->getPages($this->paginator);
        $expected = [11 => 11];
        $this->assertEquals($expected, $actual);
    }

    public function testGetsNextAndPreviousPageForFirstPage()
    {
        $this->paginator->setCurrentPageNumber(1);
        $pages = $this->paginator->getPages('Jumping');

        $this->assertEquals(2, $pages->next);
    }

    public function testGetsNextAndPreviousPageForSecondPage()
    {
        $this->paginator->setCurrentPageNumber(2);
        $pages = $this->paginator->getPages('Jumping');
        $this->assertEquals(1, $pages->previous);
        $this->assertEquals(3, $pages->next);
    }

    public function testGetsNextAndPreviousPageForMiddlePage()
    {
        $this->paginator->setCurrentPageNumber(6);
        $pages = $this->paginator->getPages('Jumping');
        $this->assertEquals(5, $pages->previous);
        $this->assertEquals(7, $pages->next);
    }

    public function testGetsNextAndPreviousPageForSecondLastPage()
    {
        $this->paginator->setCurrentPageNumber(10);
        $pages = $this->paginator->getPages('Jumping');
        $this->assertEquals(9, $pages->previous);
        $this->assertEquals(11, $pages->next);
    }

    public function testGetsNextAndPreviousPageForLastPage()
    {
        $this->paginator->setCurrentPageNumber(11);
        $pages = $this->paginator->getPages('Jumping');
        $this->assertEquals(10, $pages->previous);
    }
}
