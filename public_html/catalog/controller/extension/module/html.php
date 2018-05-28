<?php
class ControllerExtensionModuleHTML extends Controller {
	public function index($setting) {
		if (isset($setting['module_description'])) {
			$data['heading_title'] = html_entity_decode($setting['module_description']['title'], ENT_QUOTES, 'UTF-8');
			$data['html'] = html_entity_decode($setting['module_description']['description'], ENT_QUOTES, 'UTF-8');

			return $this->load->view('extension/module/html', $data);
		}
	}
}