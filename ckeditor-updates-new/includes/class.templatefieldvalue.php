<?php

/**
 * 
 * Handles TemplateFieldValue properties and methods.
 * @author felix
 * @version 0.1.0
 *
 */
class TemplateFieldValue extends DBA_TemplateFieldValue {

	public function duplicate() {
		if ($this->id > 0) {
			$objReturn = parent::duplicate();
			return $objReturn;
		}

		return NULL;
	}

}

?>