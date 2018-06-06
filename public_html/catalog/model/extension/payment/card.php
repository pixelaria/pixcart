<?php
class ModelExtensionPaymentCard extends Model {
	public function getMethod() {
		
		$this->load->language('extension/payment/card');

		$method_data = array(
			'code'       => 'card',
			'title'      => $this->language->get('text_title'),
			'terms'      => $this->language->get('text_terms'),
			'sort_order' => $this->config->get('payment_cheque_sort_order')
		);
		return $method_data;
	}
}
