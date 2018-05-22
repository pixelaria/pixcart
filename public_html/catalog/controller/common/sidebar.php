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

		return $this->load->view('common/sidebar', $data);
	}
}
