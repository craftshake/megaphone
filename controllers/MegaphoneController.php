<?php

namespace Craft;

class MegaphoneController extends BaseController
{
	protected $allowAnonymous = array('actionMigrate');

	public function actionMigrate()
	{
		$this->requirePostRequest();

		$action = craft()->request->getPost('operation');
		$remote = craft()->request->getPost('connectionString');

		$vars['action'] = $action;
		$vars['remote'] = $remote;

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
