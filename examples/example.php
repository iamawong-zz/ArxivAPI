<?php

require_once("../src/ArxivAPI.php");
require_once("../src/SearchQueryBuilder.php");

$queryBuilder = SearchQueryBuilder::getInstance();
$queryBuilder->addAuthor('bousso');
$queryBuilder->setTitle('arrow of time');

$api = new ArxivAPI();
$papersWithBuilder = $api->getPapersWithBuilder($queryBuilder);

$papersWithParams = $api->getPapers(array(
    'author' => 'bousso', 'title' => 'arrow of time'
));

$boussoAndZukowskiPapers = $api->getAuthorsPapers(array(
   'bousso', 'zukowski'
));
?>