<?php

/**
 * @package silverstripe-typeform
 */

class TypeformSiteConfigExtension extends DataExtension 
{
	
	private static $db = array(
		'TypeformApiKey' => 'Varchar'
	);

	public function updateCMSFields(FieldList $fields) 
	{
		$fields->addFieldsToTab('Root.Typeform', array(
			$key = new TextField('TypeformApiKey')
		));

		$key->setDescription('Typeform API key');
	}
}