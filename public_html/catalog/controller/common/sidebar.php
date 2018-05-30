<?php
class ControllerCommonSidebar extends Controller {
	public function index() {
		$this->load->language('common/sidebar');
		
		// Menu
		$this->load->model('catalog/category');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories($this->config->get('config_main_category'));

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2
				$children_data = array();

				// Level 1
				$data['categories'][] = array(
					'name'     => $category['name'],
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}

		// Static links
		$data['cart'] = $this->url->link('checkout/cart');
		$data['contact'] = $this->url->link('information/contact');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['special'] = $this->url->link('product/search');

		// Dynamic links
		$this->load->model('catalog/information');

		$data['informations'] = array();

		$sidebar_informations = $this->model_catalog_information->getSidebarInformations();
		

		foreach ($sidebar_informations as $information) {
			$data['informations'][] = array(
				'title' => $information['title'],
				'href'  => $this->url->link('information/information', 'information_id=' . $information['information_id'])
			);
		}

		return $this->load->view('common/sidebar', $data);
	}
}
