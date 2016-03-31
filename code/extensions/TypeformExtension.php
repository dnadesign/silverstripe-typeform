<?php

/**
 * @package silverstripe-typeform
 */

class TypeformExtension extends DataExtension 
{
	
	private static $db = array(
		'TypeformKey' => 'Varchar',
		'TypeformURL' => 'Varchar',
		'TypeformImported' => 'SS_Datetime'
	);

	public function updateCMSFields(FieldList $fields) 
	{
		$fields->addFieldsToTab('Root.Typeform', array(
			new TextField('TypeformURL', 'Typeform URL'),
			$key = new TextField('TypeformKey', 'Typeform UID')
		));
		
		$key->setDescription('The UID of a typeform is found at the end of its URL');
	}


	public function getTypeformUid() 
	{
		return $this->owner->dbObject('TypeformKey')->getValue();
	}

	public function getLastTypeformImportedTimestamp() 
	{
		return $this->owner->dbObject('TypeformImported')->Format('U');
	}

	public function updateLastTypeformImportedTimestamp() 
	{
		$this->owner->TypeformImported = SS_Datetime::now();
		$this->owner->write();
	}
}