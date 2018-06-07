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


	public function getOrder($order_id) {
    $order_query = $this->db->query("SELECT *, (SELECT os.name FROM `" . DB_PREFIX . "order_status` os WHERE os.order_status_id = o.order_status_id) AS order_status FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

    if ($order_query->num_rows) {
      return array(
        'order_id'                => $order_query->row['order_id'],
        'invoice_no'              => $order_query->row['invoice_no'],
        'invoice_prefix'          => $order_query->row['invoice_prefix'],
        'customer_id'             => $order_query->row['customer_id'],
        'firstname'               => $order_query->row['firstname'],
        'lastname'                => $order_query->row['lastname'],
        'email'                   => $order_query->row['email'],
        'telephone'               => $order_query->row['telephone'],
        
        'payment_method'          => $order_query->row['payment_method'],
        'payment_code'            => $order_query->row['payment_code'],
        
        'shipping_address'        => $order_query->row['shipping_address'],
        'shipping_postcode'       => $order_query->row['shipping_postcode'],
        'shipping_city'           => $order_query->row['shipping_city'],
        'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
        'shipping_zone'           => $order_query->row['shipping_zone'],
        'shipping_country_id'     => $order_query->row['shipping_country_id'],
        'shipping_country'        => $order_query->row['shipping_country'],
        'shipping_method'         => $order_query->row['shipping_method'],
        'shipping_code'           => $order_query->row['shipping_code'],
        
        'comment'                 => $order_query->row['comment'],
        'total'                   => $order_query->row['total'],
        'order_status_id'         => $order_query->row['order_status_id'],
        'order_status'            => $order_query->row['order_status'],
        'currency_id'             => $order_query->row['currency_id'],
        'currency_code'           => $order_query->row['currency_code'],
        'currency_value'          => $order_query->row['currency_value'],
        'ip'                      => $order_query->row['ip'],
        'forwarded_ip'            => $order_query->row['forwarded_ip'],
        'user_agent'              => $order_query->row['user_agent'],
        'accept_language'         => $order_query->row['accept_language'],
        'date_added'              => $order_query->row['date_added'],
        'date_modified'           => $order_query->row['date_modified']
      );
    } else {
      return false;
    }
  }

  public function getOrderProducts($order_id) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
    
    return $query->rows;
  }
  
  public function getOrderOptions($order_id, $order_product_id) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");
    
    return $query->rows;
  }
  
  public function getOrderTotals($order_id) {
    $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order ASC");
    
    return $query->rows;
  }
  
	// Add order history (changing status of order)
	public function addOrderHistory($order_id, $order_status_id, $comment = '', $notify = false, $override = false) {
    $order_info = $this->getOrder($order_id);
    
    if ($order_info) {
      // Fraud Detection
      $this->load->model('account/customer');

      $customer_info = $this->model_account_customer->getCustomer($order_info['customer_id']);

      if ($customer_info && $customer_info['safe']) {
        $safe = true;
      } else {
        $safe = false;
      }

      // Only do the fraud check if the customer is not on the safe list and the order status is changing into the complete or process order status
      if (!$safe && !$override && in_array($order_status_id, array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')))) {
        // Anti-Fraud
        $this->load->model('setting/extension');

        $extensions = $this->model_setting_extension->getExtensions('fraud');

        foreach ($extensions as $extension) {
          if ($this->config->get('fraud_' . $extension['code'] . '_status')) {
            $this->load->model('extension/fraud/' . $extension['code']);

            if (property_exists($this->{'model_extension_fraud_' . $extension['code']}, 'check')) {
              $fraud_status_id = $this->{'model_extension_fraud_' . $extension['code']}->check($order_info);
  
              if ($fraud_status_id) {
                $order_status_id = $fraud_status_id;
              }
            }
          }
        }
      }

      // If current order status is not processing or complete but new status is processing or complete then commence completing the order
      if (!in_array($order_info['order_status_id'], array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status'))) && in_array($order_status_id, array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')))) {
        // Redeem coupon and reward points
        $order_totals = $this->getOrderTotals($order_id);

        foreach ($order_totals as $order_total) {
          $this->load->model('extension/total/' . $order_total['code']);

          if (property_exists($this->{'model_extension_total_' . $order_total['code']}, 'confirm')) {
            // Confirm coupon and reward points
            $fraud_status_id = $this->{'model_extension_total_' . $order_total['code']}->confirm($order_info, $order_total);
            
            // If the balance on the coupon and reward points is not enough to cover the transaction or has already been used then the fraud order status is returned.
            if ($fraud_status_id) {
              $order_status_id = $fraud_status_id;
            }
          }
        }

        // Stock subtraction
        $order_products = $this->getOrderProducts($order_id);

        foreach ($order_products as $order_product) {
          $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");

          $order_options = $this->getOrderOptions($order_id, $order_product['order_product_id']);

          foreach ($order_options as $order_option) {
            $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "' AND subtract = '1'");
          }
        }
      }

      // Update the DB with the new statuses
      $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

      $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");

      // If old order status is the processing or complete status but new status is not then commence restock, and remove coupon and reward history
      if (in_array($order_info['order_status_id'], array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status'))) && !in_array($order_status_id, array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status')))) {
        // Restock
        $order_products = $this->getOrderProducts($order_id);

        foreach($order_products as $order_product) {
          $this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = (quantity + " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");

          $order_options = $this->getOrderOptions($order_id, $order_product['order_product_id']);

          foreach ($order_options as $order_option) {
            $this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET quantity = (quantity + " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "' AND subtract = '1'");
          }
        }

        // Remove coupon and reward points history
        $order_totals = $this->getOrderTotals($order_id);
        
        foreach ($order_totals as $order_total) {
          $this->load->model('extension/total/' . $order_total['code']);

          if (property_exists($this->{'model_extension_total_' . $order_total['code']}, 'unconfirm')) {
            $this->{'model_extension_total_' . $order_total['code']}->unconfirm($order_id);
          }
        }
			}

      $this->cache->delete('product');
    }
  }

}