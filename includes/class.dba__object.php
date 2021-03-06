<?php

/* General DBA Object Class v0.2.1
 * Holds the properties and methods of a DBA objects.
 *
 * CHANGELOG
 * version 0.2.1, 07 July 2010
 *   FIX: Fixed the delete method. Account Id had a faulty check.
 * version 0.2.0, 03 August 2009
 *   FIX: Fixed the __set method. Booleans get converted to 0 or 1.
 * version 0.1.9, 23 March 2009
 *   FIX: Fixed the quote method. Supports "0" as value now.
 * version 0.1.8, 13 June 2008
 *   FIX: Fixed the save method.
 *   UPD: Updated to the same codebase as the client object.
 * version 0.1.4, 12 May 2008
 *   FIX: Fixed the delete method to return the number of updated rows.
 *   FIX: Fixed the doDelete method to return the number of updated rows.
 *   FIX: Fixed the save method to return the number of updated rows.
 *   FIX: Fixed the select method to return correctly other then select queries.
 * version 0.1.3, 14 Aug 2007
 *   ADD: Added the quote method.
 * version 0.1.2, 12 Jun 2007
 *   BUG: Fixed UTF-8 encoding.
 * version 0.1.1, 04 Oct 2006
 *   NEW: Created class.
 */

class DBA__Object {
	public static $__object = "";
	public static $__table = "";
	private static $__debug = false;
	protected $sort = 0;
	protected $created = "0000-00-00 00:00:00";
	protected $modified = NULL;

	public function __get($property) {
		/* Handle Property calls to database fields. */

		$property = strtolower($property);

		if (property_exists($this, $property)) {
			return $this->$property;
		} else {
			if (self::$__debug === true) echo "Property Error in " . get_class($this) . "::get({$property}) on line " . __LINE__ . ".\n";
		}
	}

	public function __set($property, $value) {
		/* Handle Property calls to database fields. */

		$property = strtolower($property);

		if (property_exists($this, $property)) {
			if (is_bool($value)) {
				$value = ($value) ? 1 : 0;
			}
			$this->$property = $value;
		} else {
			if (self::$__debug === true) echo "Property Error in " . get_class($this) . "::set({$property}) on line " . __LINE__ . ".\n";
		}
	}

	public function __call($method, $values) {
		/* Handle Method calls to database fields. */

		if (substr($method, 0, 3) == "get") {
			$property = substr($method, 3);
			return $this->$property;
		}

		if (substr($method, 0, 3) == "set") {
			$property = substr($method, 3);
			$this->$property = $values[0];
			return;
		}

		if (self::$__debug === true) echo "Method Error in " . get_class($this) . "::{$method} on line " . __LINE__ . ".\n";
	}

	public function save($blnSaveModifiedDate = true) {
		/* Save the current object to the database. */
		global $DBAConn;

		//*** Load all properties from the class;
		$objClass = new ReflectionClass(self::$__object);
		$objProperties = $objClass->getProperties();
		if ($blnSaveModifiedDate) {
			$this->modified = NULL;
		}

		if ($this->id > 0) {
			//*** Build the query for an UPDATE call.
			$strSql = "UPDATE " . self::$__table . " SET ";

			for ($i = 0; $i < count($objProperties); $i++) {
				if ($objProperties[$i]->isProtected()) {
					$strProperty = $objProperties[$i]->name;
					$strSql .= "`" . $strProperty . "` = ";
					$strSql .= (is_null($this->$strProperty)) ? "NULL, " : str_replace("%", "%%", self::quote($this->$strProperty)) . ", ";
				}
			}

			$strSql = substr($strSql, 0, strlen($strSql) - 2);
			$strSql .= " WHERE `id` = %s";

			$strSql = sprintf($strSql, self::quote($this->id));
		} else {
			//*** Set the global property "created".
			if (!PEAR::isError($DBAConn)) {
				$this->created = strftime("%Y-%m-%d %H:%M:%S", strtotime("now"));
			}

			//*** Build the query for an INSERT call.
			$strSql = "INSERT INTO " . self::$__table . " (";

			if (!PEAR::isError($DBAConn)) {
				for ($i = 0; $i < count($objProperties); $i++) {
					if ($DBAConn->phptype == "mssql") {
						if ($objProperties[$i]->isProtected() && $objProperties[$i]->name != 'id') {
							$strSql .= $objProperties[$i]->name . ", ";
						}
					} else {
						if ($objProperties[$i]->isProtected()) {
							$strSql .= "`" . $objProperties[$i]->name . "`, ";
						}
					}
				}
			}

			$strSql = substr($strSql, 0, strlen($strSql) - 2);
			$strSql .= ") VALUES (";

			if (!PEAR::isError($DBAConn)) {
				for ($i = 0; $i < count($objProperties); $i++) {
					if ($DBAConn->phptype == "mssql") {
						if ($objProperties[$i]->isProtected() && $objProperties[$i]->name != 'id') {
							$strProperty = $objProperties[$i]->name;
							if ($strProperty !== "id") {
								if ($this->$strProperty === "newid()") {
									$strSql .= "newid()";
								} else {
									$strSql .= (is_null($this->$strProperty)) ? "getdate()" : self::quote($this->$strProperty);
								}
								$strSql .= ", ";
							}
						}
					} else {
						if ($objProperties[$i]->isProtected()) {
							$strProperty = $objProperties[$i]->name;
							$strSql .= (is_null($this->$strProperty)) ? "NULL" : self::quote($this->$strProperty);
							$strSql .= ", ";
						}
					}
				}
			}

			$strSql = substr($strSql, 0, strlen($strSql) - 2);
			$strSql .= ")";
		}

		if (self::$__debug === true) echo self::$__object . ".save() : " . $strSql . "<br />";

		if (PEAR::isError($DBAConn)) {
			die ("Connection Error in " . self::$__object . "::save on line " . __LINE__ . ". (" . $DBAConn->getMessage() . ")<br /><b>Error Details</b>: " . $DBAConn->toString());
		}

		$objResult = $DBAConn->exec($strSql);

		if (PEAR::isError($objResult)) {
			die ("Database Error in " . self::$__object . "::save on line " . __LINE__ . ". (" . $objResult->getMessage() . ")<br /><b>Error Details</b>: " . $objResult->toString() . "<br />Trying to execute: " . $strSql);
		}

		$intReturn = $objResult;

		//*** Get the PK from the Database if we just inserted a new record.
		if (!$this->id > 0) {
			$this->id = $DBAConn->lastInsertID(self::$__table, 'id');
		}

		return $intReturn;
	}

	public function delete($accountId = NULL) {
		/* Delete the current object from the database. */
		global $DBAConn;

		if ($this->id > 0) {
			$strSql = sprintf("DELETE FROM " . self::$__table . " WHERE id = %s", self::quote($this->id));
			if (is_numeric($accountId)) {
				$strSql .= sprintf(" AND `accountId` = %s", self::quote($accountId));
			}

			if (self::$__debug === true) echo self::$__object . ".delete() : " . $strSql . "<br />";

			if (PEAR::isError($DBAConn)) {
				die ("Connection Error in " . self::$__object . "::delete on line " . __LINE__ . ". (" . $DBAConn->getMessage() . ")<br /><b>Error Details</b>: " . $DBAConn->toString());
			}

			$objResult = $DBAConn->exec($strSql);

			if (PEAR::isError($objResult)) {
				die ("Database Error in " . self::$__object . "::delete on line " . __LINE__ . ". (" . $objResult->getMessage() . ")<br /><b>Error Details</b>: " . $objResult->toString() . "<br />Trying to execute: " . $strSql);
			}

			$intReturn = $objResult;

			return $intReturn;
		}
	}

	public function duplicate() {
		/* Duplicate the current object in the database. */
		global $DBAConn;

		if ($this->id > 0) {
			$intId = $this->id;
			$objClass = new ReflectionClass(self::$__object);
			$objProperties = $objClass->getProperties();

			//*** Set the global property "created",
			$this->created = strftime("%Y-%m-%d %H:%M:%S", strtotime("now"));

			//*** Set the "id" and "modified" property to NULL.
			$this->id = NULL;
			$this->modified = NULL;

			//*** Build the query for an INSERT call.
			$strSql = "INSERT INTO " . self::$__table . " (";

			for ($i = 0; $i < count($objProperties); $i++) {
				if ($objProperties[$i]->isProtected()) {
					$strSql .= "`" . $objProperties[$i]->name . "`, ";
				}
			}

			$strSql = substr($strSql, 0, strlen($strSql) - 2);
			$strSql .= ") VALUES (";

			for ($i = 0; $i < count($objProperties); $i++) {
				if ($objProperties[$i]->isProtected()) {
					$strProperty = $objProperties[$i]->name;
					$strSql .= (is_null($this->$strProperty)) ? "NULL" : self::quote($this->$strProperty);
					$strSql .= ", ";
				}
			}

			$strSql = substr($strSql, 0, strlen($strSql) - 2);
			$strSql .= ")";

			if (self::$__debug === true) echo self::$__object . ".duplicate() : " . $strSql . "<br />";

			if (PEAR::isError($DBAConn)) {
				die ("Connection Error in " . self::$__object . "::duplicate on line " . __LINE__ . ". (" . $DBAConn->getMessage() . ")<br /><b>Error Details</b>: " . $DBAConn->toString());
			}

			$objResult = $DBAConn->query($strSql);

			if (PEAR::isError($objResult)) {
				die ("Database Error in " . self::$__object . "::duplicate on line " . __LINE__ . ". (" . $objResult->getMessage() . ")<br /><b>Error Details</b>: " . $objResult->toString() . "<br />Trying to execute: " . $strSql);
			}

			//*** Get the PK from the Database if we just inserted a new record.
			if (!$this->id > 0) {
                $this->id = $DBAConn->lastInsertID(self::$__table, 'id');
			}

			//*** Get an instance of the duplicate object;
			$objMethod = $objClass->getMethod("selectByPK");
			$objReturn = $objMethod->invoke(NULL, $this->id);

			//*** Reset the "id" property.
			$this->id = $intId;

			return $objReturn;
		}

		return NULL;
	}

	public static function selectByPK($varValue, $arrFields = array(), $accountId = NULL) {
		/* Get one or multiple records from the database using the
		 * primary key and convert them to objects.
		 *
		 * Method arguments are:
		 * - single integer: Returns a single DBA object or NULL.
		 * - array with multiple integers: Returns a DBA collection.
		 */
		global $DBAConn;

		$varReturn = NULL;

		//*** Check if specific fields should be selected.
		if (is_array($arrFields) && count($arrFields) > 0) {
			$strSql = "SELECT `" . implode("`, `", $arrFields) . "` ";
		} else {
			$strSql = "SELECT * ";
		}

		if (is_array($varValue)) {
			//*** Select multiple records from the database.
			$strSql .= " FROM " . self::$__table . " WHERE id IN ('" . implode("','", $varValue) . "')";
			if (isset($accountId)) {
				$strSql .= sprintf(" AND `accountId` = %s", self::quote($accountId));
			}
			$strSql .= " ORDER BY sort";
		} else if ($varValue > -1) {
			//*** Select a single record from the database.
			$strSql .= sprintf(" FROM " . self::$__table . " WHERE `id` = %s", self::quote($varValue));
			if (isset($accountId)) {
				$strSql .= sprintf(" AND `accountId` = %s", self::quote($accountId));
			}
		} else {
			unset($strSql);
		}

		if (self::$__debug === true) echo self::$__object . ".selectByPk() : " . $strSql . "<br />";

		if (isset($strSql)) {
			if (PEAR::isError($DBAConn)) {
				die ("Connection Error in " . self::$__object . "::selectByPK on line " . __LINE__ . ". (" . $DBAConn->getMessage() . ")<br /><b>Error Details</b>: " . $DBAConn->toString());
			}

			$objResult = $DBAConn->query($strSql);

			if (PEAR::isError($objResult)) {
				die ("Database Error in " . self::$__object . "::selectByPK on line " . __LINE__ . ". (" . $objResult->getMessage() . ")<br /><b>Error Details</b>: " . $objResult->toString() . "<br />Trying to execute: " . $strSql);
			}

			if (is_array($varValue)) {
				//*** Multiple records returned. Build Collection.
				$objCollection = new DBA__Collection();
				$objClass = new ReflectionClass(self::$__object);

				while ($objRow = $objResult->fetchRow(MDB2_FETCHMODE_ASSOC)) {
					$objRecord = $objClass->newInstance();

					foreach ($objRow as $column => $value) {
						if (is_null($value)) {
							$value = "";
						}
						if 	(is_callable(array($objRecord, $column))) $objRecord->$column = $value;
					}

					$objCollection->addObject($objRecord);
				}

				//*** Return a collection object.
				$varReturn = $objCollection;

			} else if ($objResult->numRows() > 0) {
				//*** Single record returned. Build object.
				$objClass = new ReflectionClass(self::$__object);

				while ($objRow = $objResult->fetchRow(MDB2_FETCHMODE_ASSOC)) {
					$objRecord = $objClass->newInstance();

					foreach ($objRow as $column => $value) {
						if (is_null($value)) {
							$value = "";
						}
						if 	(is_callable(array($objRecord, $column))) $objRecord->$column = $value;
					}
				}

				//*** Return a single object.
				$varReturn = $objRecord;
			}
		}

		return $varReturn;
	}

	public static function select($strSql = "") {
		/* Selects DB records from the database using a SQL query. If the
		 * query is empty all records will be selected.
		 *
		 * Method arguments are:
		 * - SQL query: Returns a DBA collection or NULL.
		 */
		global $DBAConn;

		$objReturn = NULL;

		if (empty($strSql)) {
			//*** Select all records.
			$strSql = "SELECT * FROM " . self::$__table . " ORDER BY sort";
		}

		if (self::$__debug === true) echo self::$__object . ".select() : " . $strSql . "<br />";

		if (PEAR::isError($DBAConn)) {
			die ("Connection Error in " . self::$__object . "::select on line " . __LINE__ . ". (" . $DBAConn->getMessage() . ")<br /><b>Error Details</b>: " . $DBAConn->toString());
		}

		if (strtolower(substr($strSql, 0, 6)) == "select") {
			$objResult =& $DBAConn->query($strSql);
			$strQueryType = "pull";
		} else {
			$objResult =& $DBAConn->exec($strSql);
			$strQueryType = "push";
		}

		if (PEAR::isError($objResult)) {
			die ("Database Error in " . self::$__object . "::select on line " . __LINE__ . ". (" . $objResult->getMessage() . ")<br /><b>Error Details</b>: " . $objResult->toString() . "<br />Trying to execute: " . $strSql);
		}

		switch ($strQueryType) {
			case "pull":
				//*** Multiple records returned. Build Collection.
				$objCollection = new DBA__Collection();
				$objClass = new ReflectionClass(self::$__object);

				if (is_object($objResult)) {
					while ($objRow = $objResult->fetchRow(MDB2_FETCHMODE_ASSOC)) {
						$objRecord = $objClass->newInstance();

						foreach ($objRow as $column => $value) {
							if (is_null($value)) {
								$value = "";
							}

							$objRecord->$column = $value;
						}

						$objCollection->addObject($objRecord);
					}
				}

				//*** Return a collection object.
				$objReturn = $objCollection;
				break;
			case "push":
				//*** Just return the object.
				$objReturn = $objResult;
		}

		return $objReturn;
	}

	public static function doDelete($varValue) {
		/* Delete a record from the database.
		 *
		 * Method arguments are:
		 * - single integer: Deletes a single record by PK.
		 * - single object: Deletes a single record by PK.
		 */

		if (is_int($varValue)) {
			//*** Input value is an integer.
			$objClass = new ReflectionClass(self::$__object);
			$objRecord = $objClass->newInstance();
			$objRecord->setId($varValue);
			$intReturn = $objRecord->delete();

		} else if (is_object($varValue)) {
			//*** Input value is an object.
			$intReturn = $varValue->delete();
		}

		return $intReturn;
	}

	public static function quote($strValue) {
		/*
		 * Quote a value according to the database rules.
		 */
		global $DBAConn;

		//*** Stripslashes.
		if (get_magic_quotes_gpc()) {
		   $strValue = (is_string($strValue)) ? stripslashes($strValue) : $strValue;
		}

		//*** Quote if not integer.
		$strValue = (empty($strValue) && !is_numeric($strValue)) ? "''" : $DBAConn->quote($strValue);
		//$strValue = (empty($strValue) && !is_numeric($strValue)) ? "''" : "'" . $strValue . "'";

		return $strValue;
	}
}

?>
