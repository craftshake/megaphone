<?php

namespace Craft;

class MegaphonePlugin extends BasePlugin {

	public function getName()
	{
		return Craft::t('Megaphone');
	}

	public function getVersion()
	{
		return '0.9.1';
	}

	public function getDeveloper()
	{
		return 'Mario Friz';
	}

	public function getDeveloperUrl()
	{
		return 'http://builtbysplash.com';
	}

	public function hasCpSection()
	{
		return true;
	}

}