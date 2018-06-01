<?php
/*
* Оплата наличными -> Model Extension Payment COD
*/
class ModelExtensionPaymentCash extends Model {
	public function getMethod($total) {
		
		$this->load->language('extension/payment/cash');

		$method_data = array(
			'code'       => 'cash',
			'title'      => $this->language->get('text_title'),
			'terms'      => $this->language->get('text_terns'),
			'sort_order' => $this->config->get('payment_cod_sort_order')
		);
		return $method_data;
	}
}
