<?php

namespace Craft;

class MegaphoneService extends BaseApplicationComponent
{
	public function saveSettings($settings)
	{
		$megaphone = craft()->plugins->getPlugin('megaphone');
		craft()->plugins->savePluginSettings($megaphone, $settings);
	}

	public function resetKey()
	{
		$megaphone = craft()->plugins->getPlugin('megaphone');

		$settings = $megaphone->getSettings();
		$settings['key'] = craft()->security->generateRandomString(16);

		craft()->plugins->savePluginSettings($megaphone, $settings);

		return $settings['key'];
	}

	public function getSettings()
	{
		$megaphone = craft()->plugins->getPlugin('megaphone');
		return $megaphone->getSettings();
	}
}
