<?php
class ModelExtensionTotalShipping extends Model {
	public function getTotal($total) {
		if (isset($this->session->data['shipping_method'])) {
			$total['totals'][] = array(
				'code'       => 'shipping',
				'title'      => $this->session->data['shipping_method']['title'],
				'value'      => $this->session->data['shipping_method']['cost'],
				'sort_order' => $this->config->get('total_shipping_sort_order')
			);

			$total['total'] += $this->session->data['shipping_method']['cost'];
		}
	}
}