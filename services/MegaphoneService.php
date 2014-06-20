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

	public function prepare($remote, $key)
	{
		try
		{
			// Prepare remote sql file
			$endpoint = $remote;

			$client = new \Guzzle\Http\Client();
			$request = $client->post($endpoint)
				->setPostField('action', 'megaphone/api/prepare')
				->setPostField('key', $key);

			$response = $client->send($request);

			if ($response->isSuccessful())
			{
				$json = $response->json();

				$result['filename'] = $json['data']['filename'];
				$result['success'] = true;

				return $result;
			}
			else
			{
				// We connected but error
				return array('success' => false, 'message' => Craft::t('Error preparing .sql file on remote'));
			}

		}
		catch(\Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}

	public function download($remote, $key, $filename)
	{
		try
		{
			$destinationPath = craft()->path->getTempPath() . $filename;

			$endpoint = $remote;

			$client = new \Guzzle\Http\Client();
			$request = $client->post($endpoint)
				->setPostField('action', 'megaphone/api/download')
				->setPostField('key', $key)
				->setPostField('filename', $filename);

			$response = $client->send($request);

			if ($response->isSuccessful())
			{
				$body = $response->getBody();

				// Make sure we're at the beginning of the stream.
				$body->rewind();

				// Write it out to the file
				IOHelper::writeToFile($destinationPath, $body->getStream(), true);

				// Close the stream.
				$body->close();

				$file = IOHelper::getFileName($destinationPath);
			}

			if ($file !== false)
			{
				$result['success'] = true;
				$result['downloadedFile'] = $file;

				return $result;
			}
			else
			{
				throw new Exception(Craft::t('There was a problem downloading the database.'));
			}
		}
		catch (\Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}

	public function backupDatabase()
	{
		return craft()->updates->backupDatabase();
	}

	public function updateDatabase($file)
	{
		$dbBackup = new DbBackup();
		$filePath = craft()->path->getTempPath() . $file;
		$dbBackup->restore($filePath);

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
