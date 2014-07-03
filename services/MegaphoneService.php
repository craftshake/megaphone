<?php

namespace Craft;

class MegaphoneService extends BaseApplicationComponent
{
	public function saveSettings($settings)
	{
		$megaphone = craft()->plugins->getPlugin('megaphone');
		craft()->plugins->savePluginSettings($megaphone, $settings);
	}

	public function resetKey($key = null)
	{
		$megaphone = craft()->plugins->getPlugin('megaphone');

		$settings = $megaphone->getSettings();
		if ($key == null)
		{
			$settings['key'] = craft()->security->generateRandomString(16);
		}
		else
		{
			$settings['key'] = $key;
		}

		craft()->plugins->savePluginSettings($megaphone, $settings);

		return $settings['key'];
	}

	public function getSettings()
	{
		$megaphone = craft()->plugins->getPlugin('megaphone');
		return $megaphone->getSettings();
	}

	public function preparePull($remote, $key)
	{
		try
		{
			// Prepare remote sql file
			$endpoint = $remote;

			$client = new \Guzzle\Http\Client();
			$request = $client->post($endpoint)
				->setPostField('action', 'megaphone/api/prepareForPull')
				->setPostField('key', $key);

			$response = $client->send($request);

			if ($response->isSuccessful())
			{
				$json = $response->json();

				if (isset($json['error']))
				{
					throw new Exception($json['error']);
				}
				else
				{
					$result['filename'] = $json['data']['filename'];
					$result['success'] = true;

					return $result;
				}
			}
			else
			{
				// We connected but received error
				throw new Exception(Craft::t('Server responded with error.'));
			}

		}
		catch(\Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}

	public function preparePush($data)
	{
		try
		{
			$endpoint = $data['remote'];

			$client = new \Guzzle\Http\Client();
			$request = $client->post($endpoint)
				->setPostField('action', 'megaphone/api/prepareForPush')
				->setPostField('key', $data['key']);

			$response = $client->send($request);

			if ($response->isSuccessful())
			{
				$json = $response->json();

				if (isset($json['error']))
				{
					throw new Exception($json['error']);
				}
				else
				{
					$result['siteName'] = $json['data']['siteName'];
					$result['siteUrl'] = $json['data']['siteUrl'];
				}
			}
			else
			{
				// We connected but received error
				throw new Exception(Craft::t('Server responded with error.'));
			}

			$return = $this->backupLocalDatabase();

			if (!$return['success'])
			{
				throw new Exception($return['message']);
			}
			else
			{
				$result['filename'] = $return['dbBackupPath'] . '.sql';
			}

			$result['success'] = true;

			return $result;
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

	public function upload($data)
	{
		try
		{
			$sourcePath = craft()->path->getDbBackupPath() . $data['filename'];

			$endpoint = $data['remote'];

			$client = new \Guzzle\Http\Client();
			$request = $client->post($endpoint)
				->setPostField('action', 'megaphone/api/upload')
				->setPostField('key', $data['key'])
				->setPostField('filename', $data['filename'])
				->addPostFile('dbfile', $sourcePath);

			$response = $client->send($request);

			if ($response->isSuccessful())
			{
				$json = $response->json();

				if (isset($json['error']))
				{
					throw new Exception($json['error']);
				}
				else
				{
					return array('success' => true);
				}
			}
		}
		catch (\Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}

	public function receive()
	{
		try
		{
			$filePath = craft()->path->getTempPath() . craft()->request->getRequiredPost('filename');

			$file = $_FILES['dbfile'];

			if (empty($file['name']))
			{
				throw new Exception(Craft::t('No file was uploaded'));
			}

			$size = $file['size'];

			// Make sure the file isn't empty
			if (!$size)
			{
				throw new Exception(Craft::t('Uploaded file was empty'));
			}

			move_uploaded_file($file['tmp_name'], $filePath);

			return array('success' => true);
		}
		catch (Exception $e)
		{

		}
	}

	public function backupLocalDatabase()
	{
		return craft()->updates->backupDatabase();
	}

	public function backupRemoteDatabase($data)
	{
		try
		{
			$endpoint = $data['remote'];

			$client = new \Guzzle\Http\Client();
			$request = $client->post($endpoint)
				->setPostField('action', 'megaphone/api/backup')
				->setPostField('key', $data['key'])
				->setPostField('filename', $data['filename']);

			$response = $client->send($request);

			if ($response->isSuccessful())
			{
				$json = $response->json();

				if (isset($json['error']))
				{
					throw new Exception($json['error']);
				}
				else
				{
					$result['success'] = true;
					$result['dbBackupPath'] = $json['data']['filename'];

					return $result;
				}
			}
		}
		catch (\Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}

	public function updateLocalDatabase($file)
	{
		$dbBackup = new DbBackup();
		$filePath = craft()->path->getTempPath() . $file;
		$dbBackup->restore($filePath);

		$result['success'] = true;

		return $result;
	}

	public function updateRemoteDatabase($data)
	{
		try
		{
			$endpoint = $data['remote'];

			$client = new \Guzzle\Http\Client();
			$request = $client->post($endpoint)
				->setPostField('action', 'megaphone/api/update')
				->setPostField('key', $data['key'])
				->setPostField('data', $data);

			$response = $client->send($request);

			if ($response->isSuccessful())
			{
				$json = $response->json();

				if (isset($json['error']))
				{
					throw new Exception($json['error']);
				}
				else
				{
					return array('success' => true);
				}
			}
		}
		catch (\Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}

	public function replaceLocalStrings($siteName, $siteUrl)
	{
		$info = craft()->getInfo();
		$info->siteName = $siteName;
		$info->siteUrl = $siteUrl;

		if (craft()->saveInfo($info))
		{
			return array('success' => true);
		}
		else
		{
			return array('success' => false, 'message' => Craft::t('There was a problem replacing strings.'));
		}
	}

	public function replaceRemoteStrings($data)
	{
		try
		{
			$endpoint = $data['remote'];

			$client = new \Guzzle\Http\Client();
			$request = $client->post($endpoint)
				->setPostField('action', 'megaphone/api/replace')
				->setPostField('key', $data['key'])
				->setPostField('data', $data);

			$response = $client->send($request);

			if ($response->isSuccessful())
			{
				$json = $response->json();

				if (isset($json['error']))
				{
					throw new Exception($json['error']);
				}
				else
				{
					return array('success' => true);
				}
			}
		}
		catch (\Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}

	public function cleanLocalFiles($filename, $dbBackup)
	{
		IOHelper::deleteFile(craft()->path->getTempPath() . $filename, true);
		IOHelper::deleteFile(craft()->path->getDbBackupPath() . $filename, true);

		IOHelper::deleteFile(craft()->path->getTempPath() . $dbBackup, true);
		IOHelper::deleteFile(craft()->path->getDbBackupPath() . $dbBackup, true);

		$result['success'] = true;

		return $result;
	}

	public function cleanRemoteFiles($data)
	{
		try
		{
			$endpoint = $data['remote'];

			$client = new \Guzzle\Http\Client();
			$request = $client->post($endpoint)
				->setPostField('action', 'megaphone/api/clean')
				->setPostField('key', $data['key'])
				->setPostField('data', $data);

			$response = $client->send($request);

			if ($response->isSuccessful())
			{
				$json = $response->json();

				if (isset($json['error']))
				{
					throw new Exception($json['error']);
				}
				else
				{
					return array('success' => true);
				}
			}
		}
		catch (\Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}

	public function rollbackLocalDatabase($backupFile)
	{
		$dbBackup = new DbBackup();
		$filePath = craft()->path->getDbBackupPath() . $backupFile;
		$dbBackup->restore($filePath);

		$result['success'] = true;

		return $result;
	}

	public function rollbackRemoteDatabase($data)
	{
		try
		{
			$endpoint = $data['remote'];

			$client = new \Guzzle\Http\Client();
			$request = $client->post($endpoint)
				->setPostField('action', 'megaphone/api/rollback')
				->setPostField('key', $data['key'])
				->setPostField('data', $data);

			$response = $client->send($request);

			if ($response->isSuccessful())
			{
				$json = $response->json();

				if (isset($json['error']))
				{
					throw new Exception($json['error']);
				}
				else
				{
					return array('success' => true);
				}
			}
		}
		catch (\Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}
}
