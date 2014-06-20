<?php

namespace Craft;

class MegaphoneController extends BaseController
{
	public function actionMigrate()
	{
		$this->requirePostRequest();

		$operation = craft()->request->getPost('operation');
		$connectionString = craft()->request->getPost('connectionString');

		$connectionArray = explode("?megaphone=", $connectionString);

		$vars['operation'] = $operation;
		$vars['remote'] = $connectionArray[0];
		$vars['key'] = $connectionArray[1];

		$this->renderTemplate('megaphone/_go', $vars);
	}

	public function actionSaveSettings()
	{
		$this->requirePostRequest();

		$settings['allowPull'] = craft()->request->getPost('allowPull');
		$settings['allowPush'] = craft()->request->getPost('allowPush');

		craft()->megaphone->saveSettings($settings);
	}

	public function actionResetKey()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$key = craft()->megaphone->resetKey();

		$this->returnJson(array(
			'key' => $key,
		));
	}

}
