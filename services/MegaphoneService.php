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

	public function prepare()
	{
		try
		{
			// Get full power
			craft()->config->maxPowerCaptain();

			$result['success'] = true;

			return $result;
		}
		catch(\Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}

	public function download($remote, $key)
	{
		$result['success'] = true;

		return $result;
	}

	public function backupDatabase()
	{
		return craft()->updates->backupDatabase();
	}

	public function updateDatabase()
	{
		$result['success'] = true;

		return $result;
	}

	public function replaceStrings()
	{
		$result['success'] = true;

		return $result;
	}

	public function clean()
	{
		$result['success'] = true;

		return $result;
	}
}
