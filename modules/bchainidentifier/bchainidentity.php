<?php
if (!defined('_CAN_LOAD_FILES_') || !defined('_PS_VERSION_'))
	exit;

/**
 * Class BChainIdentity
 */
class BChainIdentity extends Module {
	/**
	 * BChainIdentity constructor.
	 */
	public function __construct() {
		$this->name = 'bchainidentity';
		$this->version = '1.0.0';
		$this->author = 'BChainIdentity';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
		$this->bootstrap = true;
		
		parent::__construct();

		$this->displayName = $this->l('BChainIdentity');
		$this->description = $this->l('Use the blockchain to prevent fraudulent registrations and logins.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (!Configuration::get('MYMODULE_NAME')) {
			$this->warning = $this->l('No name provided');
		}
	}

	/**
	 * Plugin install method.
	 *
	 * @return bool
	 */
	public function install() {
		if (!parent::install() || !Configuration::updateValue( 'bchainidentity', 'enabled')) {
			return false;
		}
		return true;
	}

	/**
	 * Plugin uninstall method.
	 *
	 * @return bool
	 */
	public function uninstall() {
		if (!parent::uninstall() || !Configuration::deleteByName( 'bchainidentity' )) {
			return false;
		}
		return true;
	}

	/**
	 * Display settings form.
	 *
	 * @return mixed
	 */
	public function displayForm() {
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Einstellungen'),
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('REST-API Hook'),
					'name' => 'REST_HOOK',
					'size' => 20,
					'required' => true
				)
			),
			'submit' => array(
				'title' => $this->l('Speichern'),
				'class' => 'btn btn-default pull-right'
			)
		);

		$helper = new HelperForm();

		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		$helper->title = $this->displayName;
		$helper->show_toolbar = true;
		$helper->toolbar_scroll = true;
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
				array(
					'desc' => $this->l('Save'),
					'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
					          '&token='.Tools::getAdminTokenLite('AdminModules'),
				),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Zurück zur Liste')
			)
		);

		$helper->fields_value['REST_HOOK'] = Configuration::get('REST_HOOK');

		return $helper->generateForm($fields_form);
	}

	/**
	 * Process settings form.
	 *
	 * @return string
	 */
	public function getContent() {
		$output = null;

		if (Tools::isSubmit('submit'.$this->name))
		{
			$my_module_name = strval(Tools::getValue('REST_HOOK'));
			if (!$my_module_name
			    || empty($my_module_name)
			    || !Validate::isGenericName($my_module_name))
				$output .= $this->displayError($this->l('Falscher Konfigurationswert'));
			else
			{
				Configuration::updateValue('REST_HOOK', $my_module_name);
				$output .= $this->displayConfirmation($this->l('Einstellungen geändert'));
			}
		}
		return $output.$this->displayForm();
	}

	/**
	 * Execute CURL request.
	 *
	 * @param array $request
	 * @param string $method
	 *
	 * @return array
	 */
	public function cURL($request = [], $method = 'GET') {
		if($method == "GET") { $post = 0; } else { $post = 1; }

		$url = strval(Tools::getValue('REST_HOOK'));

		$ch = curl_init();

		curl_setopt_array($ch, [
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_URL => $url,
			CURLOPT_POST => $post,
			CURLOPT_POSTFIELDS => http_build_query($request),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 30
		]);

		$server_output = curl_exec ($ch);

		curl_close ($ch);

		if ($data = json_decode($server_output)) { return $data; } else { return false; }
	}
}