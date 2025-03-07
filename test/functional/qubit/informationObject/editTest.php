<?php

$app = 'qubit';

include dirname(__FILE__).'/../../../bootstrap/functional.php';

$browser = new QubitTestFunctional(new sfBrowser());

$browser
    ->info('Information object without parent is 404')
    ->get(QubitInformationObject::ROOT_ID.';edit/isad')
    ->with('request')->begin()
    ->isParameter('module', 'sfIsadPlugin')
    ->isParameter('action', 'edit')
    ->end()
    ->with('response')->begin()
    ->isStatusCode(404)
    ->end();
