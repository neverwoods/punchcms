<?php
header('Content-Type: text/javascript');

session_save_path($_SERVER["DOCUMENT_ROOT"] . "/sessions");
session_start();

require_once('./inc.constantes.php');
require_once('../libraries/lib.language.php');

$objLang = null;
if (array_key_exists("objLang", $_SESSION)) $objLang = unserialize($_SESSION["objLang"]);
if (!is_object($objLang)) {
	require_once('../config.php');
	$objLang = new Language($_CONF['app']['defaultLang'], $_CONF['app']['langPath']);
}

?>

function StorageItem() {
	this.id = 0;
	this.checked = false;
}

StorageItem.remove = function(intId, strRedirect) {
	var blnConfirm = confirm("<?php echo $objLang->get("storageItemRemoveAlert", "alert") ?>");

	if (blnConfirm == true) {
		strReturnTo = "";
		document.location.href = "?cid=<?php echo NAV_PCMS_STORAGE ?>&eid=" + intId + "&cmd=<?php echo CMD_REMOVE ?>&returnTo=" + strReturnTo;
	}
}

StorageItem.duplicate = function(intId, strRedirect) {
	strReturnTo = "";
	document.location.href = "?cid=<?php echo NAV_PCMS_STORAGE ?>&eid=" + intId + "&cmd=<?php echo CMD_DUPLICATE ?>&returnTo=" + strReturnTo;
}

StorageItem.multiSelect = function() {
	(this.checked) ? this.checked = false : this.checked = true;

	//*** Get all checkbox fields.
	arrCheckbox = $(".multiitem").get();

	//*** Loop through the fields to check or uncheck.
	for (i = 0; i < arrCheckbox.length; i++) {
		arrCheckbox[i].checked = this.checked;
	}
}

StorageItem.multiDo = function(objField, strAction) {
	var arrChecked = new Array();

	//*** Get all checkbox fields.
	arrCheckbox = $(".multiitem").get();

	//*** Loop through the fields to find the checked ones.
	for (i = 0; i < arrCheckbox.length; i++) {
		if (arrCheckbox[i].checked) {
			var strId = arrCheckbox[i].id.substr(5);
			arrChecked.push(strId);
		}
	}

	//*** Any fields checked?
	if (arrChecked.length > 0) {
		//*** Build URI and redirect.
		var strIds = arrChecked.join(',');

		switch (strAction) {
			case "delete":
				if (arrChecked.length > 1) {
					var blnConfirm = confirm("<?php echo $objLang->get("storageItemsRemoveAlert", "alert") ?>");
				} else {
					var blnConfirm = confirm("<?php echo $objLang->get("storageItemRemoveAlert", "alert") ?>");
				}

				if (blnConfirm == true) {
					document.location.href = "?cid=<?php echo NAV_PCMS_STORAGE ?>&eid=" + strIds + "&cmd=<?php echo CMD_REMOVE ?>";
				} else {
					//*** Reset pulldown.
					objField.selectedIndex = 0;
				}
				break;

			case "duplicate":
				document.location.href = "?cid=<?php echo NAV_PCMS_STORAGE ?>&eid=" + strIds + "&cmd=<?php echo CMD_DUPLICATE ?>";
				break;

			case "activate":
				document.location.href = "?cid=<?php echo NAV_PCMS_STORAGE ?>&eid=" + strIds + "&cmd=<?php echo CMD_ACTIVATE ?>";
				break;

			case "deactivate":
				document.location.href = "?cid=<?php echo NAV_PCMS_STORAGE ?>&eid=" + strIds + "&cmd=<?php echo CMD_DEACTIVATE ?>";
				break;
		}
	} else {
		//*** Alert and reset pulldown.
		alert("<?php echo $objLang->get("multiItemEmpty", "alert") ?>");
		objField.selectedIndex = 0;
	}
}
