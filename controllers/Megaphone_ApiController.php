<?php

namespace Craft;

class Megaphone_ApiController extends BaseController
{
	protected $allowAnonymous = true;

	public function actionPrepareForPull()
	{
		$this->requirePostRequest();
		$this->requireKey();

		$return = craft()->megaphone->backupLocalDatabase();

		if (!$return['success'])
		{
			$this->returnErrorJson($return['message']);
		}
		else
		{
			$data['filename'] = $return['dbBackupPath'] . '.sql';
			$this->returnJson(array('success' => true, 'data' => $data));
		}
	}

	public function actionPrepareForPush()
	{
		$this->requirePostRequest();
		$this->requireKey();

		$data = array(
			'siteName' => craft()->getSiteName(),
			'siteUrl' => craft()->getSiteUrl()
		);

		$this->returnJson(array('success' => true, 'data' => $data));
	}

	public function actionBackup()
	{
		$this->actionPrepareForPull();
	}

	public function actionDownload()
	{
		$this->requirePostRequest();
		$this->requireKey();

		$file = craft()->request->getRequiredPost('filename');

		if (($filePath = IOHelper::fileExists(craft()->path->getDbBackupPath() . $file)) == true)
		{
			craft()->request->sendFile(IOHelper::getFileName($filePath), IOHelper::getFileContents($filePath), array('forceDownload' => false));
		}
	}

	public function actionUpload()
	{
		$this->requirePostRequest();
		$this->requireKey();

		$return = craft()->megaphone->receive();

		if (!$return['success'])
		{
			$this->returnErrorJson($return['message']);
		}
		else
		{
			$this->returnJson(array('success' => true));
		}
	}

	public function actionUpdate()
	{
		$this->requirePostRequest();
		$this->requireKey();

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->updateLocalDatabase($data['filename']);

		if (!$return['success'])
		{
			$this->returnErrorJson($return['message']);
		}
		else
		{
			$this->returnJson(array('success' => true));
		}
	}

	public function actionReplace()
	{
		$this->requirePostRequest();
		$this->requireKey();

		$data = craft()->request->getRequiredPost('data');

		$return = craft()->megaphone->replaceLocalStrings($data['siteName'], $data['siteUrl']);

		if (!$return['success'])
		{
			$this->returnErrorJson($return['message']);
		}
		else
		{
			$this->returnJson(array('success' => true));
		}
	}

	public function actionClean()
	{
		$this->requirePostRequest();
		$this->requireKey();

		$data = craft()->request->getRequiredPost('data');

		craft()->megaphone->cleanLocalFiles($data['filename'], $data['dbBackupPath']);

		$this->returnJson(array('success' => true));
	}

	public function actionRollback()
	{
		$this->requirePostRequest();
		$this->requireKey();

		$data = craft()->request->getRequiredPost('data');

		craft()->megaphone->rollbackLocalDatabase($data['dbBackupPath']);

		$this->returnJson(array('success' => true));
	}

	public function requireKey()
	{
		$key = craft()->request->getRequiredPost('key');
		$settings = craft()->megaphone->getSettings();
		if ($key !== $settings->key)
		{
			$this->returnErrorJson(Craft::t('Invalid key.'));
		}
	}
}
