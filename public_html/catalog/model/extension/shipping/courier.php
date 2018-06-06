<?php
class ModelExtensionShippingCourier extends Model {
	function getQuote() {
		$this->load->language('extension/shipping/courier');

		$method_data = array(
			'code'		=> 'courier',
			'title'		=> $this->language->get('text_title'),
			'desc'		=> $this->language->get('text_description'),
			'cost'		=> 350.00,
			'delivery'=> true,
			'text'		=> $this->currency->format(350.00, $this->session->data['currency']),
			'sort_order' => $this->config->get('shipping_courier_sort_order')
		);

		return $method_data;
	}
}