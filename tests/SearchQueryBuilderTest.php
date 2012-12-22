<?php

require_once('../src/SearchQueryBuilder.php');
require_once('PHPUnit/Autoload.php');

class SearchQueryBuilderTest extends PHPUnit_Framework_TestCase {
    /**
     * @var SearchQueryBuilder
     */
    private $queryBuilder;

    protected function setUp() {
        $this->queryBuilder = SearchQueryBuilder::getInstance();
    }

    private function build() {
        return $this->queryBuilder->build();
    }

    /**
     *
     */
    public function testAddOverall() {
        $this->queryBuilder->addCategories(array('stat.AP', 'stat.CO'));
        $this->queryBuilder->addAuthors(array('bousso', 'wong'));
        $this->queryBuilder->setTitle('apples rule');
        $this->queryBuilder->setAbstract('apples drop from the sky');

        $query = $this->build();
        $expectedQuery = 'au:bousso+AND+au:wong+AND+cat:stat.AP+AND+cat:stat.CO+AND+ti:apples+AND+rule+AND+abs:apples+'
                           . 'AND+drop+AND+from+AND+the+AND+sky';
        $this->assertEquals($expectedQuery, $query);
    }

    /**
     * Testing to see if adding multiple categories work.
     */
    public function testAddCategories() {
        $this->queryBuilder->addCategories(array('stat.AP', 'stat.CO'));
        $query = $this->build();
        $this->assertEquals('cat:stat.AP+AND+cat:stat.CO', $query);
    }

    /**
     * Test to see if adding a category works.
     */
    public function testAddCategory() {
        $this->queryBuilder->addCategory('stat.AP');
        $query = $this->build();
        $this->assertEquals('cat:stat.AP', $query);

        $this->queryBuilder->addCategory('stat.AP');
        $query = $this->build();
        $this->assertEquals('cat:stat.AP', $query);
    }

    /**
     * Test to see if setting the abstract works.
     */
    public function testSetAbstract() {
        $this->queryBuilder->setAbstract('everybody should dance now');
        $query = $this->build();
        $this->assertEquals('abs:everybody+AND+should+AND+dance+AND+now', $query);

        $this->queryBuilder->setAbstract('everybody should rock now');
        $query = $this->build();
        $this->assertEquals('abs:everybody+AND+should+AND+rock+AND+now', $query);
    }

    /**
     * Test to see if setting the title works.
     */
    public function testSetTitle() {
        $this->queryBuilder->setTitle('happiness rocks');
        $query = $this->build();
        $this->assertEquals('ti:happiness+AND+rocks', $query);

        $this->queryBuilder->setTitle('sadness rocks');
        $query = $this->build();
        $this->assertEquals('ti:sadness+AND+rocks', $query);
    }

    /**
     * Test to check if adding multiple authors is good.
     */
    public function testAddAuthors() {
        $this->queryBuilder->addAuthor('bousso');
        $this->queryBuilder->addAuthor('wong');
        $query = $this->build();
        $this->assertEquals('au:bousso+AND+au:wong', $query);

        $this->queryBuilder->reset();
        $this->queryBuilder->addAuthors(array('bousso', 'wong'));
        $query = $this->build();
        $this->assertEquals('au:bousso+AND+au:wong', $query);

        $this->queryBuilder->reset();
        $this->queryBuilder->addAuthors(array('bousso', 'bousso'));
        $query = $this->build();
        $this->assertEquals('au:bousso', $query);
    }

    /**
     * Test to check if adding an author is good.
     */
    public function testAddAuthor() {
        $this->queryBuilder->addAuthor('bousso');
        $query = $this->queryBuilder->build();
        $this->assertEquals('au:bousso', $query);

        $this->queryBuilder->addAuthor('bousso');
        $query = $this->build();
        $this->assertEquals('au:bousso', $query);
    }
}

?>