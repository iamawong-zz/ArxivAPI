<?php

class SearchQueryBuilder
{
    /**
     * @var SearchQueryBuilder
     */
    private static $instance;

    /**
     * @var Array of strings of the authors.
     */
    private $authors;

    /**
     * @var String of the title.
     */
    private $title;

    /**
     * @var Array of the strings of categories. There can only be the valid categories as listed on arxiv.
     */
    private $categories;

    /**
     * @var String of the abstract.
     */
    private $abstract;

    /**
     * @var Array of the available categories.
     */
    private $categorySet;

    /**
     * Constructor. Here we set up the set of categories.
     */
    private function __construct() {
        $this->setupCategorySet();
    }

    /**
     * Get an instance of the builder.
     *
     * @return SearchQueryBuilder
     */
    public static function getInstance() {
        if (is_null(SearchQueryBuilder::$instance)) {
            SearchQueryBuilder::$instance = new SearchQueryBuilder();
            SearchQueryBuilder::$instance->reset();
            return SearchQueryBuilder::$instance;
        }

        SearchQueryBuilder::$instance->reset();
        return SearchQueryBuilder::$instance;
    }

    /**
     * Add an array of authors to the builder.
     *
     * @param $authors Array of authors.
     */
    public function addAuthors($authors) {
        foreach ($authors as $author) {
            $this->addAuthor($author);
        }
    }

    /**
     * Add an author to the builder.
     *
     * @param $author String of author name
     */
    public function addAuthor($author) {
        if (!in_array($author, $this->authors)) {
            $this->authors[] = $author;
        }
    }

    /**
     * Set the title of the builder.
     *
     * @param $title String representing the title.
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Set the abstract of the builder.
     *
     * @param $abstract String representing the abstract.
     */
    public function setAbstract($abstract) {
        $this->abstract = $abstract;
    }

    /**
     * Add an array of categories to the builder.
     *
     * @param $categories Array of categories.
     */
    public function addCategories($categories) {
        foreach ($categories as $category) {
            $this->addCategory($category);
        }
    }

    /**
     * Add a category to the builder.
     *
     * @param $category String representing the category.
     */
    public function addCategory($category) {
        if ($this->isValidCategory($category) & !in_array($category, $this->categories)) {
            $this->categories[] = $category;
        }
    }

    /**
     * Builds the search query.
     *
     * @return string
     */
    public function build() {
        $searchQuery = array();
        if (0 < count($this->authors)) {
            $searchQuery[] = $this->explodeAndImplodeArray($this->authors, 'au:');
        }

        if (0 < count($this->categories)) {
            $searchQuery[] = $this->explodeAndImplodeArray($this->categories, 'cat:');
        }

        if (0 < strlen($this->title)) {
            $searchQuery[] = 'ti:' . $this->explodeAndImplodeString($this->title, '+AND+');
        }

        if (0 < strlen($this->abstract)) {
            $searchQuery[] = 'abs:' . $this->explodeAndImplodeString($this->abstract, '+AND+');
        }

        return implode('+AND+', $searchQuery);
    }

    /**
     * Helper method to reset the builder's parameters.
     */
    public function reset() {
        $this->authors = array();
        $this->title = "";
        $this->abstract = "";
        $this->categories = array();
    }

    /**
     * Explodes a string with spaces, and then glues it back together with $glue.
     *
     * @param $string string that we are exploding the white spaces
     * @param $glue string the glue we will be using
     * @return string
     */
    private function explodeAndImplodeString($string, $glue) {
        return implode($glue, explode(' ', $string));
    }

    /**
     * Formats an array into the required string.
     *
     * @param $array Array
     * @param $stringAppend String
     * @return string
     */
    private function explodeAndImplodeArray($array, $stringAppend) {
        for ($idx = 0; $idx < count($array); $idx++) {
            $array[$idx] = $stringAppend . $this->explodeAndImplodeString($array[$idx], '+');
        }
        return implode('+AND+', $array);
    }

    /**
     * Sets up the category map with all the categories that are allowed via Arxiv.
     */
    private function setupCategorySet() {
        $this->categorySet = array(
            'stat.AP', 'stat.CO', 'stat.ML', 'stat.ME', 'stat.TH',
            'q-bio.BM', 'q-bio.CB', 'q-bio.GN', 'q-bio.MN', 'q-bio.NC', 'q-bio.OT', 'q-bio.PE', 'q-bio.QM', 'q-bio.SC',
            'q-bio.TO',
            'cs.AR', 'cs.AL', 'cs.CL', 'cs.CC', 'cs.CE', 'cs.CG', 'cs.GT', 'cs.CV', 'cs.CY', 'cs.CR', 'cs.DS', 'cs.DL',
            'cs.DM', 'cs.DC', 'cs.GL', 'cs.GR', 'cs.HC', 'cs.IR', 'cs.IT', 'cs.LG', 'cs.LO', 'cs.MS', 'cs.MA', 'cs.MM',
            'cs.NI', 'cs.NE', 'cs.NA', 'cs.OS', 'cs.OH', 'cs.PF', 'cs.PL', 'cs.RO', 'cs.SE', 'cs.SD', 'cs.SC',
            'nlin.AO', 'nlin.CG', 'nlin.CD', 'nlin.SI', 'nlin.PS',
            'math.AG', 'math.AT', 'math.AP', 'math.CT', 'math.CA', 'math.CO', 'math.AC', 'math.CV', 'math.DG', 'math.DS',
            'math.FA', 'math.GM', 'math.GN', 'math.GT', 'math.GR', 'math.HO', 'math.IT', 'math.KT', 'math.LO', 'math.MP',
            'math.MG', 'math.NT', 'math.NA', 'math.OA', 'math.OC', 'math.PR', 'math.QA', 'math.RT', 'math.RA', 'math.SP',
            'math.ST', 'math.SG',
            'astro-ph', 'gr-qc',
            'cond-mat.dis-nn', 'cond-mat.mes-hall', 'cond-mat.mtrl-sci', 'cond-mat.other', 'cond-mat.soft',
            'cond-math.stat-mech', 'cond-mat.str-el', 'cond-mat.supr-con',
            'hep-ex', 'hep-lat', 'hep-ph', 'hep-th', 'math-ph', 'nucl-ex', 'nucl-th',
            'physics.acc-ph', 'physics.ao-ph', 'physics.atom-ph', 'physics.atm-clus', 'physics.bio-ph', 'physics.chem-ph',
            'physics.class-ph', 'physics.comp-ph', 'physics.data-an', 'physics.flu-dyn', 'physics.gen-ph', 'physics.geo-ph',
            'physics.hist-ph', 'physics.ins-det', 'physics.med-ph', 'physics.optics', 'physics.ed-ph', 'physics.soc-ph',
            'physics.plasm-ph', 'physics.pop-ph', 'physics.space-ph', 'quant-ph'
        );
    }

    /**
     * Determines if the given category is a valid category or not as per Arxiv.
     *
     * @param $category String of the category in question.
     * @return bool
     */
    private function isValidCategory($category) {
        return in_array($category, $this->categorySet);
    }
}
