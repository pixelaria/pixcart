<?php
class ControllerExtensionModuleCategories extends Controller {
	public function index() {
		$this->load->model('catalog/category');
		$this->load->model('tool/image');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories($this->config->get('config_main_category'));

		foreach ($categories as $category) {
			$_image = $this->model_tool_image->original($category['image']);
			$_image = "background-image:url('".$_image."')";
			
			$data['categories'][] = array(
				'category_id' => $category['category_id'],
				'image'       => $_image,
				'name'        => $category['name'],
				'preview'			=> $category['preview'],
				'href'        => $this->url->link('product/category', 'path=' . $category['category_id'])
			);
		}

		return $this->load->view('extension/module/categories', $data);
	}
}