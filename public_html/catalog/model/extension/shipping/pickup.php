<?php
class ModelExtensionShippingPickup extends Model {
	function getQuote() {
		$this->load->language('extension/shipping/pickup');

		$method_data = array(
			'code'		=> 'pickup',
			'title'		=> $this->language->get('text_title'),
			'desc'		=> $this->language->get('text_description'),
			'cost'		=> 0.00,
			'text'		=> $this->currency->format(0.00, $this->session->data['currency']),
			'sort_order' => $this->config->get('shipping_pickup_sort_order')
		);

		return $method_data;
	}
}