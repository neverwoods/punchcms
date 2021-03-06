<?php

/***
 *
 * ElementField DBA Class.
 *
 */

class DBA_ElementField extends DBA__Object {
	protected $id = NULL;
	protected $elementid = 0;
	protected $templatefieldid = 0;
	protected $fieldtypeid = 0;
	protected $originalname = "";
	protected $username = "";

	//*** Constructor.
	public function DBA_ElementField() {
		self::$__object = "ElementField";
		self::$__table = "pcms_element_field";
	}

	//*** Static inherited functions.
	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		self::$__object = "ElementField";
		self::$__table = "pcms_element_field";

		return parent::selectByPK($varValue, $arrFields, $accountId);
	}

	public static function select($strSql = "") {
		self::$__object = "ElementField";
		self::$__table = "pcms_element_field";

		return parent::select($strSql);
	}

	public static function doDelete($varValue) {
		self::$__object = "ElementField";
		self::$__table = "pcms_element_field";

		return parent::doDelete($varValue);
	}

	public function save($blnSaveModifiedDate = true) {
		self::$__object = "ElementField";
		self::$__table = "pcms_element_field";

		return parent::save($blnSaveModifiedDate);
	}

	public function delete($accountId = NULL) {
		self::$__object = "ElementField";
		self::$__table = "pcms_element_field";

		return parent::delete($accountId);
	}

	public function duplicate() {
		self::$__object = "ElementField";
		self::$__table = "pcms_element_field";

		return parent::duplicate();
	}
}

?>