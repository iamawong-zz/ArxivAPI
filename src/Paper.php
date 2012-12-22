<?php

class Paper {
    /**
     * @var String title of the paper.
     */
    public $title;

    /**
     * @var String summary of the paper.
     */
    public $summary;

    /**
     * @var Array of the authors.
     */
    public $authors;

    /**
     * @var String link of the paper.
     */
    public $link;

    /**
     * @var Array categories of the paper.
     */
    public $categories;

    /**
     * @var DateTime published time of the paper.
     */
    public $publishedDate;

    /**
     * @param $title
     * @param $summary
     * @param $authors
     * @param $link
     * @param $publishedDate
     * @param null $categories
     */
    public function __construct($title, $summary, $authors, $link, $publishedDate, $categories = null) {
        $this->title = $title;
        $this->summary = $summary;
        $this->authors = $authors;
        $this->link = $link;
        $this->publishedDate = $publishedDate;
        $this->categories = $categories;
    }

    /**
     * The id of the paper is also the link of the paper, according to Arxiv.
     * @return String
     */
    public function getId() {
        return $this->link;
    }
}

?>