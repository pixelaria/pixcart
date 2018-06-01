<?php
/**
* Оплата наличными 
*/
class ControllerExtensionPaymentCash extends Controller {
	public function confirm() {
		$json = array();
		
		if ($this->session->data['payment_method']['code'] == 'cash') {
			$this->load->model('checkout/order');
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_cash_order_status_id')); 

			$json['redirect'] = $this->url->link('checkout/success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}
}
