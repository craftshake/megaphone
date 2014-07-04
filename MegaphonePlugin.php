<?php

namespace Craft;

class MegaphonePlugin extends BasePlugin
{
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
		return 'http://craftshake.com';
	}

	public function hasCpSection()
	{
		return true;
	}

	public function onAfterInstall()
	{
		craft()->megaphone->resetKey();
	}

	public function defineSettings()
	{
		return array(
			'allowPull' => array(AttributeType::Bool),
			'allowPush' => array(AttributeType::Bool),
			'key' => array(AttributeType::String)
		);
	}
}
