<?php
class ControllerExtensionPaymentGlocashfailure extends Controller {
	public function index() {

		$this->load->language('checkout/failure');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		if(isset($this->request->get["REQ_ERROR"])){
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_failure'),
				'href' => $this->url->link('extension/payment/glocashfailure', 'REQ_ERROR='.$this->request->get["REQ_ERROR"], true)
			);
			$data['text_message'] = '<p>There was a problem processing your payment and the order did not complete.</p><p>Possible reasons are: <b>'.$this->request->get["REQ_ERROR"].'</b></p>';
		}
		else{
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_failure'),
				'href' => $this->url->link('checkout/failure')
			);
			$data['text_message'] = sprintf($this->language->get('text_message'), $this->url->link('information/contact'));
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('extension/payment/glocash_fail', $data));
	}
}