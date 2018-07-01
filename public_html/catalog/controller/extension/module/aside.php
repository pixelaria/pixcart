<?php
class ControllerExtensionModuleAside extends Controller {
	public function index() {
		$this->load->language('extension/module/aside');

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		if (isset($parts[1])) {
			$data['category_id'] = $parts[1];
		} else {
			$data['category_id'] = 0;
		}

		if (isset($parts[2])) {
			$data['child_id'] = $parts[2];
		} else {
			$data['child_id'] = 0;
		}

	
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories($this->config->get('config_main_category'));

		foreach ($categories as $category) {
			$children_data = array();

			$children = $this->model_catalog_category->getCategories($category['category_id']);

			foreach($children as $child) {
				$filter_data = array('filter_category_id' => $child['category_id'], 'filter_sub_category' => true);

				$children_data[] = array(
					'category_id' => $child['category_id'],
					'name' => $child['short_name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
					'href' => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
				);
			}

			$filter_data = array(
				'filter_category_id'  => $category['category_id'],
				'filter_sub_category' => true
			);

			$data['categories'][] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['short_name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
				'children'    => $children_data,
				'href'        => $this->url->link('product/category', 'path=' . $category['category_id'])
			);
		}

		return $this->load->view('extension/module/aside', $data);
	}
}