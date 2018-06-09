<?php
class ControllerCheckoutCheckout extends Controller {
	
	public function index() {
		// Language
		$this->load->language('checkout/checkout');
		$this->document->setTitle($this->language->get('heading_title'));


		// Scripts 
		$this->document->addScript('catalog/view/javascript/order.js','footer');


		// Breadcrumbs
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text'  => $this->language->get('text_home'),
			'href'  => $this->url->link('common/home'),
			'class' => 'breadcrumbs__link--home'
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => false
		);

		if ($this->cart->hasProducts()) {

			// Загрузка моделей
			$this->load->model('tool/image');
			$this->load->model('account/customer');
			$this->load->model('setting/extension');

			// Получаем продукты 
			$data['products'] = array();
			$products = $this->cart->getProducts();

			foreach ($products as $product) {
				if ($product['image']) {
					$thumb = $this->model_tool_image->resize($product['image'], 100,100);
				} else {
					$thumb = '';
				}

				$total = $this->currency->format($product['price'] * $product['quantity'], $this->session->data['currency']);
				
				$data['products'][] = array(
					'cart_id'   	=> $product['cart_id'],
					'product_id'  => $product['product_id'],
					'thumb'     	=> $thumb,
					'name'      	=> $product['name'],
					'description'	=> strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')),
					'quantity'  	=> $product['quantity'],
					'price'     	=> $product['price'],
					'total'     	=> $total,
					'href'      	=> $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}

			
			// ------- Если клиент залогинен, получаем имя, телефон
			if ($this->customer->isLogged()) {
				$data['firstname']=$this->customer->getFirstName();
				$data['lastname']=$this->customer->getLastName();
				$data['phone']=$this->customer->getTelephone();
				$data['email']=$this->customer->getEmail();
				$data['customer_id']=$this->customer->getId();
				$data['customer_id']=$this->customer->getId();
				$this->load->model('account/address');
				$addresses = $this->model_account_address->getAddresses(); // Default address
				if ($addresses) {
					$this->session->data['addresses']=$addresses;
				}
			} else {
				$data['firstname']='';
				$data['lastname']='';
				$data['phone']='';
				$data['email']='';
				$data['customer_id']=0;
				$data['customer_group_id']=0;

			}
			// Все способы оплаты и доставки
			$data['shipping_methods'] = $this->getShippingMethods();
			$data['payment_methods'] = $this->getPaymentMethods();

			// Текущий способ оплаты
			if (isset($this->session->data['payment_method'])) {
				$data['payment_method'] = $this->session->data['payment_method']['code'];
			} else {
				$this->session->data['payment_method'] = $this->session->data['payment_methods']['cash'];
				$data['payment_method'] = 'cash';
			}

			// Текущий способ доставки
			if (isset($this->session->data['shipping_method'])) {
				$data['shipping_method'] = $this->session->data['shipping_method']['code'];
			} else {
				$this->session->data['shipping_method'] = $this->session->data['shipping_methods']['courier'];
				$data['shipping_method'] = 'courier';
			}

			$data['delivery'] = ($data['shipping_method']=='courier');

			$data['totals'] = $this->getOrderTotals(false);
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
		
		} else {

			unset($this->session->data['success']);
		
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
		}


		

		$this->response->setOutput($this->load->view('checkout/checkout', $data));
	}	

	public function getOrderTotals($encode = true) {
		$totals = array();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
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

				// We have to put the totals in an array so that they pass by reference.
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
			}
		}

		$sort_order = array();

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);

		$json['totals'] = array();

		foreach ($totals as $total) {
			$json['totals'][$total['code']] = array(
				'code' => $total['code'],
				'class' => ($total['code']=='total')?'order__total--total':'',
				'value' => $total['value'],
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
			);
		}
		
		// Возвращаем результат
		if ($encode) {
			$this->response->setOutput(json_encode($json));
		}
		else {
			return $json['totals'];
		}
	}


	// Получение способов доставки
	public function getShippingMethods() {
		$shipping_methods = array();
		$results = $this->model_setting_extension->getExtensions('shipping');



		foreach ($results as $result) {
			if ($this->config->get('shipping_' . $result['code'] . '_status')) {
				$this->load->model('extension/shipping/' . $result['code']);
				$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote('');
				
				if ($quote) {
					$shipping_methods[$result['code']] = $quote;
				}
			}
		}

		$sort_order = array();
		foreach ($shipping_methods as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
		
		array_multisort($sort_order, SORT_ASC, $shipping_methods);
		$this->session->data['shipping_methods'] = $shipping_methods;
		// -------------------------------------------

		return $shipping_methods;
	}

	// Получение способов оплаты
	public function getPaymentMethods() {
		// Способы оплаты ----------------------------
		$payment_methods = array();
		$results = $this->model_setting_extension->getExtensions('payment');

		foreach ($results as $result) {
			if ($this->config->get('payment_' . $result['code'] . '_status')) {
				$this->load->model('extension/payment/' . $result['code']);
				$method = $this->{'model_extension_payment_' . $result['code']}->getMethod();
				if ($method) {
					$payment_methods[$result['code']] = $method;
				}
			}
		}

		$sort_order = array();

		foreach ($payment_methods as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $payment_methods);
		$this->session->data['payment_methods'] = $payment_methods;

		return $payment_methods;
	}


	/**
	*	Setting shipping method of order
	*/
	public function setShippingMethod() {
		$json = array();
		$json['status'] = false;
		
		if (isset($this->request->post['code'])) {
			$code=$this->request->post['code'];
			$shipping_method=$this->session->data['shipping_methods'][$code]; 
			if ($shipping_method) {
				unset($this->session->data['shipping_method']);
				$this->session->data['shipping_method'] = $shipping_method;
				
				$json['status'] = true;
				$json['delivery'] = $shipping_method['delivery'];

			} else {
				$json['error'] = 'Неизвестный способ доставки';
			}
		} else {
			$json['error'] = 'Не указан тип доставки';
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function setPaymentMethod() {
		$json = array();
		$json['status'] = false;

		
		if (isset($this->request->post['code'])) {
			$code=$this->request->post['code'];
			$payment_method=$this->session->data['payment_methods'][$code]; 
			if ($payment_method) {
				unset($this->session->data['payment_method']);
				$this->session->data['payment_method'] = $payment_method;
				$json['status'] = true;
				$json['message'] = 'Установили способ оплаты: '.$code;
			} else {
				$json['error'] = 'Неизвестный способ оплаты';
			}
		} else {
			$json['error'] = 'Не указан тип оплаты';
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function _index() {
		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

		}

		$this->load->language('checkout/checkout');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('catalog/view/javascript/order.js');
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_cart'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['text_checkout_option'] = sprintf($this->language->get('text_checkout_option'), 1);
		$data['text_checkout_account'] = sprintf($this->language->get('text_checkout_account'), 2);
		$data['text_checkout_payment_address'] = sprintf($this->language->get('text_checkout_payment_address'), 2);
		$data['text_checkout_shipping_address'] = sprintf($this->language->get('text_checkout_shipping_address'), 3);
		$data['text_checkout_shipping_method'] = sprintf($this->language->get('text_checkout_shipping_method'), 4);
		
		
		$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 5);
		$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 6);
		
		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} else {
			$data['error_warning'] = '';
		}

		$data['logged'] = $this->customer->isLogged();

		if (isset($this->session->data['account'])) {
			$data['account'] = $this->session->data['account'];
		} else {
			$data['account'] = '';
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('checkout/checkout', $data));
	}

}