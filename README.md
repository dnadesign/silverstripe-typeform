# SilverStripe Typeform


## Maintainer Contact

* Will Rossiter (Nickname: wrossiter, willr) <will.rossiter@dna.co.nz>

## Requirements

* SilverStripe 3.1

## Documentation

This module provides integration with Typeform.com and SilverStripe in a way 
that SilverStripe `Page` objects can have a linked Typeform Form and submissions
made through Typeform are brought into SilverStripe to be managed through a
`ModelAdmin` interface.

## Installation

	composer require "dnadesign/silverstripe-typeform"

## Usage

Add the `TypeformPageExtension` extension to your formable page type. For 
example, in mysite/_config/extensions.yml

	Page:
	  extensions:
	    - TypeformPageExtension

Rebuild the database and complete the new Typeform tab in the CMS.

To sync submissions call `dev/tasks/SyncTypeformSubmissions`. You can also sync
individual forms (say on submission callback) by creating an action and manually
invoking the `SyncTypeformSubmissions_Single` class

	$sync = new SyncTypeformSubmissions_Single($this->TypeformKey);
	$results = $sync->syncComments($this);

	
	
