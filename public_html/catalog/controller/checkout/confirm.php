<?php
class ControllerCheckoutConfirm extends Controller {
		/**
	*	Validation and registration of Order
	*/
	public function index() {
		$json=array();
		$json['status'] = false;
		
		// Validation
		if (!empty($this->request->post)) {
			$errors = $this->validateOrder();
			
			if (!count($errors)) {
				$order_id = $this->addOrder();
				$json['status'] = true;
				$json['order_id'] = $order_id;
				$json['message'] = 'Оформлен заказ '.$order_id;
			} else {
				$json['errors']=$errors;
			}
		} else {
			$json['message']='Отсутствуsют данные о заказе';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


	/**
	*	Additional validation for Order
	*/
	private function validateOrder() { 
		$errors = array();
		
		// Validating shipping method
		if (!isset($this->session->data['shipping_method'])) {
			$errors[]='Не указан способ доставки';
		}

		// Validating payment method
		if (!isset($this->session->data['payment_method'])) {
			$errors[]='Не указан способ оплаты';
		}

		// Validate cart has products 
		if (!$this->cart->hasProducts()) {
			$errors[]='Корзина пуста';	
		}
		return $errors;
	}

	/**
	*	Adding Order
	*/
	private function addOrder() {
		$post = $this->request->post;
		$session_data = $this->session->data;

		
		// Order Data array
		$order_data = array();
		
		if (isset($session_data['payment_method']['title'])) {
			$order_data['payment_method'] = $session_data['payment_method']['title'];
		} else {
			$order_data['payment_method'] = 'Наличными'; // by default if something wrong
		}

		if (isset($session_data['payment_method']['code'])) {
			$order_data['payment_code'] = $session_data['payment_method']['code'];
		} else {
			$order_data['payment_code'] = 'cash'; // by default if something wrong
		}

		if (isset($session_data['shipping_method']['title'])) {
			$order_data['shipping_method'] = $session_data['shipping_method']['title'];
		} else {
			$order_data['shipping_method'] = 'Самовывоз'; // by default if something wrong
		}

		if (isset($session_data['shipping_method']['code'])) {
			$order_data['shipping_code'] = $session_data['shipping_method']['code'];
		} else {
			$order_data['shipping_code'] = 'pickup'; // by default if something wrong
		}

		
    // Customer data
    if ($this->customer->isLogged()) {
			$order_data['customer_id'] = $this->customer->getId();
      $order_data['customer_group_id'] = $this->customer->getGroupId();
      $order_data['firstname'] = $this->customer->getFirstName();
			$order_data['lastname'] = $this->customer->getLastName();
			$order_data['email'] = $this->customer->getEmail();
			$order_data['telephone'] = $this->customer->getTelephone();
		} else {
			$order_data['customer_id'] = 0;
      $order_data['customer_group_id'] = 0;
			$order_data['firstname'] = $post['firstname'];
			$order_data['lastname'] = $post['lastname'];
			$order_data['email'] = $post['email'];
			$order_data['telephone'] = $post['telephone'];
		}
		
		// TODO Shipping address
    if ($session_data['shipping_method']['delivery']) {
      
      if (isset($post['shipping_address'])) {
        $order_data['shipping_address'] = $post['shipping_address'];
      } else {
        $order_data['shipping_address'] = ''; 
      }

    } else {
    
    }

    // TODO
    $order_data['shipping_city'] = ''; 
    $order_data['shipping_postcode'] = ''; 
    $order_data['shipping_country'] = 'Россия'; 
    $order_data['shipping_country_id'] = 0; 
    $order_data['shipping_zone'] = ''; 
    $order_data['shipping_zone_id'] = ''; 
    $order_data['shipping_address'] = ''; 


    // Additional data
		if (isset($post['comment'])) {
			$order_data['comment'] = $post['comment'];
		} else {
			$order_data['comment'] = ''; 
		}

		
		

		// Products data
		$order_data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
      $option_data = array();

      foreach ($product['option'] as $option) {
        $option_data[] = array(
          'product_option_id'       => $option['product_option_id'],
          'product_option_value_id' => $option['product_option_value_id'],
          'option_id'               => $option['option_id'],
          'option_value_id'         => $option['option_value_id'],
          'name'                    => $option['name'],
          'value'                   => $option['value'],
          'type'                    => $option['type']
        );
      }

      $order_data['products'][] = array(
        'product_id' => $product['product_id'],
        'name'       => $product['name'],
        'model'      => $product['model'],
        'option'     => $option_data,
        'quantity'   => $product['quantity'],
        'subtract'   => $product['subtract'],
        'price'      => $product['price'],
        'total'      => $product['total']
      );
    }

		
		// Totals data
		$totals = array();
		$total = 0;
		$total_data = array(
			'totals' => &$totals,
			'total'  => &$total

		);

		$this->load->model('setting/extension');
		
		$sort_order = array(); 
   	
   	$results = $this->model_setting_extension->getExtensions('total');

		foreach ($results as $key => $value) {
      $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
    }

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
      if ($this->config->get('total_' . $result['code'] . '_status')) {
        $this->load->model('extension/total/' . $result['code']);
		    $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
      }
    }

		$sort_order = array();
		
		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);
		
		$order_data['totals'] = $totals;
		$order_data['total'] = $total_data['total'];


		// Additional order data
		$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
    $order_data['currency_code'] = $this->session->data['currency'];
    $order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
		

		// Additional data about client
		$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

    if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
      $order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
      $order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
    } else {
      $order_data['forwarded_ip'] = '';
    }

    if (isset($this->request->server['HTTP_USER_AGENT'])) {
      $order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
    } else {
      $order_data['user_agent'] = '';
    }

    if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
      $order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
    } else {
      $order_data['accept_language'] = '';
    }


		$this->load->model('checkout/order');
		$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);
		
		return $this->session->data['order_id'];
	}
}
