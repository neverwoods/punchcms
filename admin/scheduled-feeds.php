<?php

require_once('includes/init.php');

set_time_limit(60 * 60 * 5); //*** 5 hours max execution time.

$intId = (int)request("feedId", 0);

//*** Render Dynamic elements.
$objAccounts = Account::select();
foreach ($objAccounts as $objAccount) {
	$_CONF['app']['account'] = $objAccount;
	
	$objFeeds = Feed::selectActive();
	foreach ($objFeeds as $objFeed) {
		if (($intId > 0 && $intId === (int)$objFeed->getId()) || $intId === 0) {
			$objFeed->updateElements();
		}
	}
}

echo "Scheduled feed sync finished.";

?>