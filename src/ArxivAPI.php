<?php

require_once('Paper.php');

class ArxivAPI {
    /**
     * Base URI to query Arxiv with.
     */
    const baseURI = 'http://export.arxiv.org/api/query?';

    public function __construct() {

    }

    /**
     * Get papers with a query builder.
     *
     * @param $queryBuilder SearchQueryBuilder
     * @param int $start
     * @param int $maxResults
     * @param string $sortBy
     * @param string $sortOrder
     * @return array|null
     */
    public function getPapersWithBuilder($queryBuilder, $start = 0, $maxResults = 10, $sortBy = "relevance",
                                         $sortOrder = "descending") {
        $searchParameters = $this->validateParameters($start, $maxResults, $sortBy, $sortOrder);
        $searchParameters['search_query'] = $queryBuilder->build();

        return $this->getPaperWithSearchParameterArray($searchParameters);
    }

    /**
     * Get papers with an array of query params.
     *
     * @param array $queryParams This should be an array with the right keys in it.
     * @param int $start
     * @param int $maxResults
     * @param string $sortBy
     * @param string $sortOrder
     * @return array|null
     */
    public function getPapers($queryParams, $start = 0, $maxResults = 10, $sortBy = "relevance",
                              $sortOrder = "descending") {
        $queryBuilder = SearchQueryBuilder::getInstance();
        if (in_array('category', $queryParams)) {
            $queryBuilder->addCategory($queryParams['category']);
        }
        if (in_array('categories', $queryParams)) {
            $queryBuilder->addCategories($queryParams['categories']);
        }
        if (in_array('author', $queryParams)) {
            $queryBuilder->addAuthor($queryParams['author']);
        }
        if (in_array('authors', $queryParams)) {
            $queryBuilder->addCategories($queryParams['authors']);
        }
        if (in_array('title', $queryParams)) {
            $queryBuilder->setTitle($queryParams['title']);
        }
        if (in_array('abstract', $queryParams)) {
            $queryBuilder->setAbstract($queryParams['abstract']);
        }

        return $this->getPapersWithBuilder($queryBuilder, $start, $maxResults, $sortBy, $sortOrder);
    }

    /**
     * http://export.arxiv.org/api/query?search_query=au:raphael+bousso+AND+au:zukowski&max_results=1
     *
     * @param array $authors of strings that are the names of the authors
     * @param int $start
     * @param int $maxResults
     * @param string $sortBy
     * @param string $sortOrder
     * @return array|null an array of Paper or null if there is no response.
     */
    public function getAuthorsPapers($authors, $start = 0, $maxResults = 10, $sortBy = "relevance",
                                     $sortOrder = "descending") {
        $queryBuilder = SearchQueryBuilder::getInstance();
        $queryBuilder->addAuthors($authors);

        return $this->getPapersWithBuilder($queryBuilder, $start, $maxResults, $sortBy, $sortOrder);
    }

    /**
     * Helper method to get papers with an array of the search parameters. The array should contain the search_query,
     * sortBy, sortOrder, start, and maxResults
     *
     * @param $searchParameters
     * @return array|null
     */
    private function getPaperWithSearchParameterArray($searchParameters) {
        // Validation that everything is in the searchParameters, just in case.
        if (!in_array('search_query', $searchParameters) || !in_array('sortBy', $searchParameters) ||
            !in_array('sortOrder', $searchParameters) || !in_array('start', $searchParameters) ||
            !in_array('max_results', $searchParameters)) {
            return null;
        }

        $response = $this->makeGetRequest(array(
            'search_query' => $searchParameters['search_query'],
            'sortBy' => $searchParameters['sortBy'],
            'sortOrder' => $searchParameters['sortOrder'],
            'start' => $searchParameters['start'],
            'max_results' => $searchParameters['maxResults']
        ));

        if (is_null($response)) {
            return null;
        }

        $response = simplexml_load_string($response);

        $papers = array();
        foreach ($response->entry as $paper) {
            $title = $this->getStringFromXML($paper->title);
            $summary = $this->getStringFromXML($paper->summary);
            $link = $this->getStringFromXML($paper->id);
            $publishedDate = $this->getDateTimeFromXML($paper->published);
            $authors = array();

            foreach ($paper->author as $author) {
                $authors[] = $this->getStringFromXML($author->name);
            }
            $papers[] = new Paper($title, $summary, $authors, $link, $publishedDate);
        }

        return $papers;
    }

    /**
     * Makes the get request to arxiv.org given the params. It'll construct the query.
     *
     * @param $params array an array of key value queries.
     * @return mixed|null Return the XML response if success, else it'll just return null.
     */
    private function makeGetRequest($params) {
        $curlObj = curl_init();
        $requestURL = ArxivAPI::baseURI;
        foreach ($params as $key => $value) {
            $addon = $key . '=' . $value . '&';
            $requestURL = $requestURL . $addon;
        }
        $requestURL = rtrim($requestURL, '&');

        curl_setopt_array($curlObj, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $requestURL
        ));

        $response = curl_exec($curlObj);

        if ($response) {
            return $response;
        } else {
            return null;
        }
    }

    /**
     * Takes an XML element and extracts the date from it.
     *
     * @param SimpleXMLElement $date
     * @return DateTime
     */
    private function getDateTimeFromXML(SimpleXMLElement $date) {
        $date = $this->getStringFromXML($date);
        $date = substr($date, 0, 10);
        return DateTime::createFromFormat('Y-m-d', $date, new DateTimeZone('America/Los_Angeles'));
    }

    /**
     * Takes a SimpleXMLElement and returns the string that is contained within.
     *
     * @param SimpleXMLElement $simpleXML
     * @return string
     */
    private function getStringFromXML(SimpleXMLElement $simpleXML) {
        return strip_tags($simpleXML->asXML());
    }

    /**
     * Helper method to validate the parameters that are going into the query. We'll return it in an array with the
     * query parameters being the keys.
     *
     * @param $start
     * @param $maxResults
     * @param $sortBy
     * @param $sortOrder
     * @return array
     */
    private function validateParameters($start, $maxResults, $sortBy, $sortOrder) {
        $returnArray = array();
        $returnArray['start'] = $this->numDefault($start, 0, 0);
        $returnArray['maxResults'] = $this->numDefault($maxResults, 0, 10);
        $returnArray['sortBy'] = $this->stringDefault($sortBy, array('relevance', 'lastUpdatedDate', 'submittedDate'),
                'relevance');
        $returnArray['sortOrder'] = $this->stringDefault($sortOrder, array('descending', 'ascending'), 'descending');

        return $returnArray;
    }

    /**
     * Helps validate if a string is inside the check group, if not it'll return the default.
     *
     * @param $string string
     * @param $checkGroup array
     * @param $default string
     *
     * @return string
     */
    private function stringDefault($string, $checkGroup, $default) {
        return in_array($string, $checkGroup) ? $string : $default;
    }

    /**
     * Helps validate if a number is greater than $greaterThan, if not it'll return $default.
     *
     * @param $num int
     * @param $greaterThan int
     * @param $default int
     * @return int
     */
    private function numDefault($num, $greaterThan, $default) {
        return is_numeric($num) && $num > $greaterThan ? $num : $default;
    }
}
?>