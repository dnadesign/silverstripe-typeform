<?php

/**
 * @package typeform
 */
class TypeformQuestion extends DataObject {
	
	private static $db = array(
		'Reference' => 'Varchar(255)',
		'Title' => 'Varchar(255)',
		'ParentID' => 'Int'
	);
}