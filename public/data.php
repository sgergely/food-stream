<?php

use App\Foursquare\Venue;
use App\Foursquare\VenueOrdering;

header('Content-Type: application/json');
date_default_timezone_set('Europe/Budapest');

include 'config.php';
include '../vendor/autoload.php';

$client = new \TheTwelve\Foursquare\HttpClient\CurlHttpClient('../cacert.pem');
$redirector = new \TheTwelve\Foursquare\Redirector\HeaderRedirector();

$factory = new \TheTwelve\Foursquare\ApiGatewayFactory($client, $redirector);
$factory->setClientCredentials(FSQR_CLIENT_ID, FSQR_CLIENT_SECRET);
$factory->setEndpointUri('https://api.foursquare.com');
$factory->useVersion(2);
$auth = $factory->getAuthenticationGateway(
	'https://foursquare.com/oauth2/authorize',
	'https://foursquare.com/oauth2/access_token',
	REDIRECT_URL
);

$venueList = array();
foreach ($foodLists as $foodType => $foodCategory) {
    $gateway = $factory->getListGateway($foodCategory);
	$list = $gateway->getList();
	if (isset($list->listItems) && $list->listItems->count > 0) {
		foreach ($list->listItems->items as $item) {
			$venueList[] = \App\Foursquare\Venue::create($item);
		}
	}
}

usort($venueList, VenueOrdering::create()->ordering());

echo json_encode($venueList);

/*
object(stdClass)#137 (6) {
	["id"]         => string(24) "4bf58dd8d48988d1d1941735"
	["name"]       => string(20) "Ramen / Noodle House"
	["pluralName"] => string(20) "Ramen / Noodle House"
	["shortName"]  => string(15) "Ramen / Noodles"
	["primary"]    => bool(true)
	["icon"]       => object(stdClass)#138 (2) {
	  ["prefix"]   => string(50) "https://ss1.4sqi.net/img/categories_v2/food/ramen_"
	  ["suffix"]   => string(4) ".png"
	}
}
*/