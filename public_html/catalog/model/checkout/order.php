<?php
class ModelCheckoutOrder extends Model {

	// Add order (products,options,totals)
	public function addOrder($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "order` SET ".
			"   invoice_prefix = '" . $this->db->escape($data['invoice_prefix']) . 
			"', customer_id = '" . (int)$data['customer_id'] . 
			"', customer_group_id = '" . (int)$data['customer_group_id'] . 
			"', firstname = '" . $this->db->escape($data['firstname']) . 
			"', lastname = '" . $this->db->escape($data['lastname']) . 
			"', email = '" . $this->db->escape($data['email']) . 
			"', telephone = '" . $this->db->escape($data['telephone']) . 
			"', payment_method = '" . $this->db->escape($data['payment_method']) . 
			"', payment_code = '" . $this->db->escape($data['payment_code']) . 
			"', shipping_address = '" . $this->db->escape($data['shipping_address']) . 
			"', shipping_city = '" . $this->db->escape($data['shipping_city']) . 
			"', shipping_postcode = '" . $this->db->escape($data['shipping_postcode']) . 
			"', shipping_country = '" . $this->db->escape($data['shipping_country']) . 
			"', shipping_country_id = '" . (int)$data['shipping_country_id'] . 
			"', shipping_zone = '" . $this->db->escape($data['shipping_zone']) . 
			"', shipping_zone_id = '" . (int)$data['shipping_zone_id'] . 
			"', shipping_method = '" . $this->db->escape($data['shipping_method']) . 
			"', shipping_code = '" . $this->db->escape($data['shipping_code']) . 
			"', comment = '" . $this->db->escape($data['comment']) . 
			"', total = '" . (float)$data['total'] . 
			"', currency_id = '" . (int)$data['currency_id'] . 
			"', currency_code = '" . $this->db->escape($data['currency_code']) . 
			"', currency_value = '" . (float)$data['currency_value'] . 
			"', ip = '" . $this->db->escape($data['ip']) . 
			"', forwarded_ip = '" .  $this->db->escape($data['forwarded_ip']) . 
			"', user_agent = '" . $this->db->escape($data['user_agent']) . 
			"', accept_language = '" . $this->db->escape($data['accept_language']) . 
			"', date_added = NOW(), date_modified = NOW()");

		$order_id = $this->db->getLastId();

		// Products
		if (isset($data['products'])) {
			foreach ($data['products'] as $product) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_product SET ".
					"  order_id = '" . (int)$order_id . 
					"', product_id = '" . (int)$product['product_id'] . 
					"', name = '" . $this->db->escape($product['name']) . 
					"', model = '" . $this->db->escape($product['model']) . 
					"', quantity = '" . (int)$product['quantity'] . 
					"', price = '" . (float)$product['price'] . 
					"', total = '" . (float)$product['total'] . "'");

				$order_product_id = $this->db->getLastId();

				foreach ($product['option'] as $option) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "order_option SET ".
						"  order_id = '" . (int)$order_id . 
						"', order_product_id = '" . (int)$order_product_id . 
						"', product_option_id = '" . (int)$option['product_option_id'] . 
						"', product_option_value_id = '" . (int)$option['product_option_value_id'] . 
						"', name = '" . $this->db->escape($option['name']) . 
						"', `value` = '" . $this->db->escape($option['value']) . 
						"', `type` = '" . $this->db->escape($option['type']) . "'");
				}
			}
		}

		// Totals
		if (isset($data['totals'])) {
			foreach ($data['totals'] as $total) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_total SET ".
					"  order_id = '" . (int)$order_id . 
					"', code = '" . $this->db->escape($total['code']) . 
					"', title = '" . $this->db->escape($total['title']) . 
					"', `value` = '" . (float)$total['value'] . 
					"', sort_order = '" . (int)$total['sort_order'] . "'");
			}
		}

		return $order_id;
	}


}