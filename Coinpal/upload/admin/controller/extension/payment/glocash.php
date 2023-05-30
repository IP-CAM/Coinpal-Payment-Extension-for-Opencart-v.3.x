<?php
class ControllerExtensionPaymentGlocash extends Controller {
	private $error = array();
	public function index() {
		$this->load->language('extension/payment/glocash');
		$logger = new Log('glocash.log');
		
		$title = $this->language->get('heading_title');
		$class_name = get_class($this);
		$index = strrpos($class_name, 'Glocash');
		$id_raw = strtolower(substr($class_name, $index));
		$id = 'payment_' . $id_raw;
		$channel = false;
		if (strlen($class_name) - $index > 7) {
			$channel = true;
			$title = substr($class_name, $index + 7) . ' (via Glocash)';   //主要支付下面是否包含其他支付，是的显示
		}
		
		$data['pm'] = $id;
		
		$this->document->setTitle($title);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$logger->write('modify payment information:'.json_encode($this->request->post));
			
			$this->model_setting_setting->editSetting($id, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		//管理界面显示项
		$data['heading_title'] = $title;
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_testmode_on'] = $this->language->get('text_testmode_on');
		$data['text_testmode_off'] = $this->language->get('text_testmode_off');

		$data['entry_email'] = $this->language->get('entry_email');
		$data['entry_secretkey'] = $this->language->get('entry_secretkey');
		$data['entry_appid'] = $this->language->get('entry_appid');
		$data['entry_method'] = $this->language->get('entry_method');
		$data['entry_3ds'] = $this->language->get('entry_3ds');
		$data['entry_terminal'] = $this->language->get('entry_terminal');
		$data['entry_test'] = $this->language->get('entry_test');
		
		$data['entry_unpaid_status'] = $this->language->get('entry_unpaid_status');
		$data['entry_paid_status'] = $this->language->get('entry_paid_status');
		$data['entry_pending_status'] = $this->language->get('entry_pending_status');
		$data['entry_cancelled_status'] = $this->language->get('entry_cancelled_status');
		$data['entry_failed_status'] = $this->language->get('entry_failed_status');
		$data['entry_refunding_status'] = $this->language->get('entry_refunding_status');
		$data['entry_refunded_status'] = $this->language->get('entry_refunded_status');
		$data['entry_complaint_status'] = $this->language->get('entry_complaint_status');
		$data['entry_chargeback_status'] = $this->language->get('entry_chargeback_status');

		
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');


		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

        if (isset($this->error['currtitle'])) {
            $data['error_currtitle'] = $this->error['currtitle'];
        } else {
            $data['error_currtitle'] = '';
        }

        if (isset($this->error['merchantid'])) {
            $data['error_merchantid'] = $this->error['merchantid'];
        } else {
            $data['error_merchantid'] = '';
        }

		if (isset($this->error['secretkey'])) {
			$data['error_secretkey'] = $this->error['secretkey'];
		} else {
			$data['error_secretkey'] = '';
		}

//        if (isset($this->error['email'])) {
//            $data['error_email'] = $this->error['email'];
//        } else {
//            $data['error_email'] = '';
//        }
//
//		if (isset($this->error['appid'])) {
//			$data['error_appid'] = $this->error['appid'];
//		} else {
//			$data['error_appid'] = '';
//		}
//
//		if (isset($this->error['method'])) {
//			$data['error_method'] = $this->error['method'];
//		} else {
//			$data['error_method'] = '';
//		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $title,
			'href' => $this->url->link('extension/payment/' . $id_raw, 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['action'] = $this->url->link('extension/payment/' . $id_raw, 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL');


        if (isset($this->request->post['payment_glocash_currtitle'])) {
            $data['payment_glocash_currtitle'] = $this->request->post['payment_glocash_currtitle'];
        } else {
            $data['payment_glocash_currtitle'] = $this->config->get('payment_glocash_currtitle');
        }

        if (isset($this->request->post['payment_glocash_merchantid'])) {
            $data['payment_glocash_merchantid'] = $this->request->post['payment_glocash_merchantid'];
        } else {
            $data['payment_glocash_merchantid'] = $this->config->get('payment_glocash_merchantid');
        }

		if (isset($this->request->post['glocash_secretkey'])) {
			$data['payment_glocash_secretkey'] = $this->request->post['payment_glocash_secretkey'];
		} else {
			$data['payment_glocash_secretkey'] = $this->config->get('payment_glocash_secretkey');
		}

//        if (isset($this->request->post['payment_glocash_email'])) {
//			$data['payment_glocash_email'] = $this->request->post['payment_glocash_email'];
//		} else {
//			$data['payment_glocash_email'] = $this->config->get('payment_glocash_email');
//		}
//
//        if (isset($this->request->post['glocash_appid'])) {
//            $data['payment_glocash_appid'] = $this->request->post['payment_glocash_appid'];
//        } else {
//            $data['payment_glocash_appid'] = $this->config->get('payment_glocash_appid');
//        }
//
//		if (isset($this->request->post['glocash_method'])) {
//			$data['payment_glocash_method'] = $this->request->post['payment_glocash_method'];
//		} else {
//			$data['payment_glocash_method'] = $this->config->get('payment_glocash_method');
//		}
//
//		if (isset($this->request->post['glocash_3ds'])) {
//			$data['payment_glocash_3ds'] = $this->request->post['payment_glocash_3ds'];
//		} else {
//			$data['payment_glocash_3ds'] = $this->config->get('payment_glocash_3ds');
//		}
//
//		if (isset($this->request->post['glocash_terminal'])) {
//			$data['payment_glocash_terminal'] = $this->request->post['payment_glocash_terminal'];
//		} else {
//			$data['payment_glocash_terminal'] = $this->config->get('payment_glocash_terminal');
//		}
//
//		if (isset($this->request->post['payment_glocash_test'])) {
//			$data['payment_glocash_test'] = $this->request->post['payment_glocash_test'];
//		} else {
//			$data['payment_glocash_test'] = $this->config->get('payment_glocash_test');
//		}
//
//		if (isset($this->request->post['payment_glocash_total'])) {
//			$data['payment_glocash_total'] = $this->request->post['payment_glocash_total'];
//		} else {
//			$data['payment_glocash_total'] = $this->config->get('payment_glocash_total');
//		}

		if (isset($this->request->post['payment_glocash_unpaid_status_id'])) {
			$data['payment_glocash_unpaid_status_id'] = $this->request->post['payment_glocash_unpaid_status_id'];
		} else {
			$data['payment_glocash_unpaid_status_id'] = $this->config->get('payment_glocash_unpaid_status_id');
		}
		
		if (isset($this->request->post['payment_glocash_paid_status_id'])) {
			$data['payment_glocash_paid_status_id'] = $this->request->post['payment_glocash_paid_status_id'];
		} else {
			$data['payment_glocash_paid_status_id'] = $this->config->get('payment_glocash_paid_status_id');
		}
		
		if (isset($this->request->post['payment_glocash_pending_status_id'])) {
			$data['payment_glocash_pending_status_id'] = $this->request->post['payment_glocash_pending_status_id'];
		} else {
			$data['payment_glocash_pending_status_id'] = $this->config->get('payment_glocash_pending_status_id');
		}
		
		if (isset($this->request->post['payment_glocash_cancelled_status_id'])) {
			$data['payment_glocash_cancelled_status_id'] = $this->request->post['payment_glocash_cancelled_status_id'];
		} else {
			$data['payment_glocash_cancelled_status_id'] = $this->config->get('payment_glocash_cancelled_status_id');
		}
		
		if (isset($this->request->post['payment_glocash_failed_status_id'])) {
			$data['payment_glocash_failed_status_id'] = $this->request->post['payment_glocash_failed_status_id'];
		} else {
			$data['payment_glocash_failed_status_id'] = $this->config->get('payment_glocash_failed_status_id');
		}
		
		if (isset($this->request->post['payment_glocash_refunding_status_id'])) {
			$data['payment_glocash_refunding_status_id'] = $this->request->post['payment_glocash_refunding_status_id'];
		} else {
			$data['payment_glocash_refunding_status_id'] = $this->config->get('payment_glocash_refunding_status_id');
		}
		
		if (isset($this->request->post['payment_glocash_refunded_status_id'])) {
			$data['payment_glocash_refunded_status_id'] = $this->request->post['payment_glocash_refunded_status_id'];
		} else {
			$data['payment_glocash_refunded_status_id'] = $this->config->get('payment_glocash_refunded_status_id');
		}
		
		if (isset($this->request->post['payment_glocash_complaint_status_id'])) {
			$data['payment_glocash_complaint_status_id'] = $this->request->post['payment_glocash_complaint_status_id'];
		} else {
			$data['payment_glocash_complaint_status_id'] = $this->config->get('payment_glocash_complaint_status_id');
		}
		
		if (isset($this->request->post['payment_glocash_chargeback_status_id'])) {
			$data['payment_glocash_chargeback_status_id'] = $this->request->post['payment_glocash_chargeback_status_id'];
		} else {
			$data['payment_glocash_chargeback_status_id'] = $this->config->get('payment_glocash_chargeback_status_id');
		}
		
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$geo_zone_id = $id . '_geo_zone_id';
		if (isset($this->request->post[$geo_zone_id])) {
			$data[$geo_zone_id] = $this->request->post[$geo_zone_id];
		} else {
			$data[$geo_zone_id] = $this->config->get($geo_zone_id);
		}
		$data['current_geo_zone_id'] = $data[$geo_zone_id];

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$pm_status = $id . '_status';
		if (isset($this->request->post[$pm_status])) {
			$data[$pm_status] = $this->request->post[$pm_status];
		} else {
			$data[$pm_status] = $this->config->get($pm_status);
		}
		$data['pm_status_name'] = $pm_status;
		$data['current_pm_status'] = $data[$pm_status];

		$pm_sortorder = $id . '_sort_order';
		if (isset($this->request->post[$pm_sortorder])) {
			$data[$pm_sortorder] = $this->request->post[$pm_sortorder];
		} else {
			$data[$pm_sortorder] = $this->config->get($pm_sortorder);
		}
		$data['current_sort_order'] = $data[$pm_sortorder];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$logger->write('payment param_admin :'.json_encode($data));
		
		$this->response->setOutput(
				$this->load->view($channel ? 'extension/payment/glocash_channel' : 'extension/payment/glocash', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/glocash')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

        if (isset($this->request->post['payment_glocash_currtitle']) && !$this->request->post['payment_glocash_currtitle']) {
            $this->error['currtitle'] = $this->language->get('error_currtitle');
        }

        if (isset($this->request->post['payment_glocash_merchantid']) && !$this->request->post['payment_glocash_merchantid']) {
            $this->error['merchantid'] = $this->language->get('error_merchantid');
        }

		if (isset($this->request->post['payment_glocash_secretkey']) && !$this->request->post['payment_glocash_secretkey']) {
			$this->error['secretkey'] = $this->language->get('error_secretkey');
		}

//        if (isset($this->request->post['payment_glocash_email']) && empty($this->request->post['payment_glocash_email'])) {
//			$this->error['email'] = $this->language->get('error_email');
//		}
//
//		if (isset($this->request->post['payment_glocash_appid']) && !$this->request->post['payment_glocash_appid']) {
//			$this->error['appid'] = $this->language->get('error_appid');
//		}
//
//		if (isset($this->request->post['payment_glocash_method']) && !$this->request->post['payment_glocash_method']) {
//			$this->error['method'] = $this->language->get('error_method');
//		}
		

		return !$this->error;
	}
	
	
	public function install() {

		try{
				
			$this->db->query('
	            CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'glocash_log` (
				`id_log` int(10) unsigned NOT NULL AUTO_INCREMENT,
	            `id_order` varchar(50) NOT NULL,
	            `message` text NOT NULL,
	            `date_add` datetime NOT NULL,
	            PRIMARY KEY (`id_log`),
	            KEY `id_order` (`id_order`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8');
				
		}
		catch (\exception $err){
			
		}
		
	}
	
	
	public function uninstall() {

		try{
				
			$this->db->query('DROP TABLE `'.DB_PREFIX.'glocash_log`');
				
		}
		catch (\exception $err){

		}
		
	}
	
}