<?php
if (!defined('_CAN_LOAD_FILES_') || !defined('_PS_VERSION_'))
	exit;

class BChainIdentity extends Module {
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
	
	public function install() {
		if (!parent::install() || !Configuration::updateValue( 'bchainidentity', 'enabled')) {
			return false;
		}
		return true;
	}
	
	public function uninstall() {
		if (!parent::uninstall() || !Configuration::deleteByName( 'bchainidentity' )) {
			return false;
		}
		return true;
	}
}