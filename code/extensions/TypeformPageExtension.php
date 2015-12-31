<?php

class TypeformPageExtension extends DataExtension
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
}
