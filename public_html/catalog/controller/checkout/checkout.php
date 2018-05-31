<?php
class ControllerCheckoutCheckout extends Controller {
	
	public function index() {
		// Language
		$this->load->language('checkout/checkout');
		$this->document->setTitle($this->language->get('heading_title'));


		// Scripts 
		$this->document->addScript('catalog/view/javascript/order.js');


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

				$price = $this->currency->format($product['price'], $this->session->data['currency']);
				$total = $this->currency->format($product['price'] * $product['quantity'], $this->session->data['currency']);
				
				$data['products'][] = array(
					'cart_id'   	=> $product['cart_id'],
					'product_id'  => $product['product_id'],
					'thumb'     	=> $thumb,
					'name'      	=> $product['name'],
					'description'	=> strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')),
					'quantity'  	=> $product['quantity'],
					'price'     	=> $price,
					'total'     	=> $total,
					'href'      	=> $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}

			/*
			// ------- Если клиент залогинен, получаем имя, телефон
			if ($this->customer->isLogged()) {
				$this->session->data['firstname']=$this->customer->getFirstName();
				$this->session->data['lastname']=$this->customer->getLastName();
				$this->session->data['phone']=$this->customer->getTelephone();
				$this->session->data['email']=$this->customer->getEmail();
				
				$this->load->model('account/address');
				$addresses = $this->model_account_address->getAddresses(); // Default address
				if ($addresses) {
					$this->session->data['addresses']=$addresses;
				}
			} 

			
			if ($this->customer->isLogged()) {
				$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
				
				$data['customer_id'] = $this->customer->getId();
				$data['customer_group_id'] = $customer_info['customer_group_id'];
				$data['firstname'] = $customer_info['firstname'];
				$data['lastname'] = $customer_info['lastname'];
				$data['email'] = $customer_info['email'];
				$data['telephone'] = $customer_info['telephone'];

			} 
			
			elseif (isset($this->session->data['guest'])) {
				$data['customer_id'] = 0;
				$data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
				$data['firstname'] = $this->session->data['guest']['firstname'];
				$data['lastname'] = $this->session->data['guest']['lastname'];
				$data['email'] = $this->session->data['guest']['email'];
				$data['telephone'] = $this->session->data['guest']['telephone'];
			}
			*/

			// Способы оплаты и доставки
			$data['shipping_methods'] = $this->getShippingMethods();
			$data['payment_methods'] = $this->getPaymentMethods($total);
			
			
			





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

	}

	// Получение способов оплаты
	public function getPaymentMethods($total) {
		// Способы оплаты ----------------------------
		$payment_methods = array();
		$results = $this->model_setting_extension->getExtensions('payment');

		foreach ($results as $result) {
			if ($this->config->get('payment_' . $result['code'] . '_status')) {
				$this->load->model('extension/payment/' . $result['code']);
				$method = $this->{'model_extension_payment_' . $result['code']}->getMethod($total);
				if ($method) {
					$payment_methods[$result['code']] = $method;
				}
			}
		}

		$sort_order = array();
		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $payment_methods);
		$this->session->data['payment_methods'] = $payment_methods;

		return $payment_methods;
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

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}