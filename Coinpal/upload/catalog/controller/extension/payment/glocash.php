<?php
class ControllerExtensionPaymentGlocash extends Controller {
	protected $pm_id = '';
	public function index() {
		$logger = new Log('glocash.log');
		
		$data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$logger->write('beforehand order created:'.json_encode($order_info));
		$this->Dblog(0,'beforehand order created:'.json_encode($order_info));
		
		$orderid="OC".$order_info['order_id'].rand(10000000,99999999);
		$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_glocash_unpaid_status_id'), "order create");

		$terminal=$this->config->get('payment_glocash_terminal');
		if (!$this->config->get('payment_glocash_test')) {
			$gatewayUrl= 'https://pay.'.$terminal.'.com/gateway/payment/index';
		} else {
			$gatewayUrl= 'https://sandbox.'.$terminal.'.com/gateway/payment/index';
		}
        //$gatewayUrl = 'http://pay.v2gc.test/gateway/payment/index';
        $order_products = $this->model_checkout_order->getOrderProducts($order_info['order_id']);
        $goodsName = '';
        if (!empty($order_products)) {
            $productsName = [];
            foreach ($order_products as $item) {
                $productsName[] = $item['name'] . ' x ' . $item['quantity'];
            }
            $goodsName = implode(';', $productsName);
        }

        $param = array(
            'version'=>'2',
            'requestId'=>$orderid,
            'merchantNo'=>$this->config->get('payment_glocash_merchantid'),
            'orderNo'=>$orderid,
            'orderCurrencyType'=>'fiat',
            'orderAmount'=>$this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false),
            'orderCurrency'=>$order_info['currency_code'],
            'payerEmail'=>$order_info['email'],
            'payerIP'=>$_SERVER['REMOTE_ADDR'],
            'successUrl'=>$this->url->link('checkout/success', '', true),
            'redirectURL'=>$this->url->link('checkout/success', '', true),
            'cancelURL'=>$this->url->link('checkout/success', '', true),
            'notifyURL'=>$this->url->link('extension/payment/glocash/notify', '', true),
        );

        $param['sign'] = hash("sha256",
            $this->config->get('payment_glocash_secretkey').
            $param['requestId'].
            $param['merchantNo'].
            $param['orderNo'].
            $param['orderAmount'].
            $param['orderCurrency']
        );

        $gatewayUrl="https://pay.coinpal.io/gateway/pay/checkout";
		
		$logger->write('#order'.$param['orderNo'].' beforehand order get url param:'.json_encode($param));
		$this->Dblog($param['orderNo'],'beforehand order get url param:'.json_encode($param));
		
		$httpCode = $this->paycurl($gatewayUrl, http_build_query($param), $result);
		$datas = json_decode($result, true);
		
		$logger->write('beforehand order payment url:'.$gatewayUrl.' method:post Request:'.json_encode($param).' Result:'.$result);
		$this->Dblog($param['orderNo'],'url:'.$gatewayUrl.' beforehand order paycurl:'.$result);
		
		$action="";
		if ($httpCode!=200 || empty($datas['nextStepContent'])) {
			// 请求失败
			$action=$this->url->link('extension/payment/glocashfailure', 'REQ_ERROR='.$datas['respMessage'], true);
		}
		else{
			$action=$datas['nextStepContent'];
		}
		//$action=str_replace("https","http",$action);    //测试
		
		//跳转到付款页
		$data['action'] = $action;
		$data['source'] = 'opencart';		
		
		//扔入页面表单提交
		$version_oc = substr(VERSION, 0, 3);
		$logger->write('oc version:'.$version_oc);
		$this->Dblog($param['orderNo'],'oc version:'.$version_oc);
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/glocash.twig')) {
			return $this->load->view($this->config->get('config_template') . '/template/extension/payment/glocash.twig', $data);
		} else {
			return $this->load->view('extension/payment/glocash', $data);
		}
		
		/*if($version_oc == "2.3")
		{
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/glocash.twig')) {
				return $this->load->view($this->config->get('config_template') . '/template/extension/payment/glocash.twig', $data);
			} else {
				return $this->load->view('extension/payment/glocash', $data);
			}
		}
		else
		{
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/glocash')) {
				return $this->load->view($this->config->get('config_template') . '/template/extension/payment/glocash', $data);
			} else {
				return $this->load->view('default/template/extension/payment/glocash', $data);
			}
		}*/
	}	
	
	/**
	 * 支付curl提交
	 * @param $url
	 * @param $postData
	 * @param $result
	 * akirametero
	 */
	private function paycurl( $url, $postData, &$result ){
		$options = array();
		if (!empty($postData)) {
			$options[CURLOPT_CUSTOMREQUEST] = 'POST';
			$options[CURLOPT_POSTFIELDS] = $postData;
		}
		$options[CURLOPT_USERAGENT] = 'Glocash/v2.*/CURL';
		$options[CURLOPT_ENCODING] = 'gzip,deflate';
		$options[CURLOPT_HTTPHEADER] = [
		'Accept: text/html,application/xhtml+xml,application/xml',
		'Accept-Language: en-US,en',
		'Pragma: no-cache',
		'Cache-Control: no-cache'
				];
		$options[CURLOPT_RETURNTRANSFER] = 1;
		$options[CURLOPT_HEADER] = 0;
		if (substr($url,0,5)=='https') {
			$options[CURLOPT_SSL_VERIFYPEER] = false;
		}
		$ch = curl_init($url);
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $httpCode;
	}

	public function notify() {
		
		$logger = new Log('glocash.log');
		$this->load->model('checkout/order');
		try {
									
			$valid = $this->validatePSNSIGN($this->request->post);
			$track_id =empty($this->request->post['orderNo'])?"0":$this->request->post['orderNo'];
			$track_id=str_replace("OC","",$track_id);
			
			$this->Dblog($track_id,'notify:'.json_encode($this->request->post));
			
			$order_info = $this->model_checkout_order->getOrder($track_id);
//			if ($this->request->post['PGW_CURRENCY'] == $this->request->post['BIL_CURRENCY'] && $order_info["total"] != $this->request->post['PGW_PRICE']) {
//				$comment = "order:".$order_info["order_id"]." grandTotal=".$order_info["total"]." , no equal to PGW_PRICE=:".$this->request->post['PGW_PRICE'];
//				$this->Dblog($track_id,$comment);
//				$logger->write($comment);
//			}
//			else{
				
				if(!$valid){
					$commit=$this->language->get('text_pw_mismatch');
					if($commit=="text_pw_mismatch")
						$commit="Validate does not match. Order requires investigation.";
					
					$logcommit='Validation failed order_status_id:'.$this->config->get('config_order_status_id').' Result:'.json_encode($this->request->post);
					$logger->write($logcommit);
					$this->Dblog($track_id,$logcommit);
					
					$this->model_checkout_order->addOrderHistory($track_id, $this->config->get('config_order_status_id'), $commit);
					$this->response->setOutput('verify failed');
				}
				else{
					$logger->write('notify Result:'.json_encode($this->request->post));
					
					$state = $this->request->post['status'];
					$message = '';

                    if (isset($this->request->post['status'])) {
                        $message .= 'status: ' . $this->request->post['status'] . "\n";
                    }

                    if (isset($this->request->post['orderNo'])) {
                        $message .= 'orderNo: ' . $this->request->post['orderNo'] . "\n";
                    }

                    if (isset($this->request->post['orderAmount'])) {
                        $message .= 'orderAmount: ' . $this->request->post['orderAmount'] . "\n";
                    }

                    if (isset($this->request->post['orderCurrency'])) {
                        $message .= 'orderCurrency: ' . $this->request->post['orderCurrency'] . "\n";
                    }

                    if (isset($this->request->post['reference'])) {
                        $message .= 'reference: ' . $this->request->post['reference'] . "\n";
                    }

                    if (isset($this->request->post['sign'])) {
                        $message .= 'sign: ' . $this->request->post['sign'] . "\n";
                    }
						
					$status_list = array(
							'unpaid' => $this->config->get('payment_glocash_unpaid_status_id'),
							'paid' => $this->config->get('payment_glocash_paid_status_id'),
							'pending' => $this->config->get('payment_glocash_pending_status_id'),
							'cancelled' => $this->config->get('payment_glocash_cancelled_status_id'),
							'failed' => $this->config->get('payment_glocash_failed_status_id'),
							'refunding' => $this->config->get('payment_glocash_refunding_status_id'),
							'refunded' => $this->config->get('payment_glocash_refunded_status_id'),
							'complaint' => $this->config->get('payment_glocash_complaint_status_id'),
							'chargeback' => $this->config->get('payment_glocash_chargeback_status_id')
					);

					$logger->write('notify log orderid:'.$track_id.' status:'.$status_list[$state].' message:'.$message);
					$this->Dblog($track_id,'notify orderid:'.$track_id.' s:'.$status_list[$state].' m:'.$message);
					
					$this->model_checkout_order->addOrderHistory($track_id, $status_list[$state], $message);
					$this->response->setOutput('success');
				}
				
//			}
			
			
			
		}catch (\Exception $e){
			$logger->write('notify Exception:'.$e->getMessage());
			$this->Dblog(0,'notify Exception:'.$e->getMessage());
			$this->model_checkout_order->addOrderHistory($track_id, $this->config->get('config_order_status_id'), $this->language->get('text_pw_mismatch'));
			$this->response->setOutput('verify failed');
		}
		

	}
	
	
	// 验证 付款结果/PSN 提交的REQ_SIGN 是否合法
	public function validatePSNSIGN($param){
		// REQ_SIGN = SHA256 ( SECRET_KEY + REQ_TIMES + REQ_EMAIL + CUS_EMAIL + TNS_GCID + BIL_STATUS + BIL_METHOD + PGW_PRICE + PGW_CURRENCY )
        $sign = hash("sha256",
            $this->config->get('payment_glocash_secretkey').
            $param['requestId'].
            $param["merchantNo"].
            $param["orderNo"].
            $param["orderAmount"].
            $param["orderCurrency"]
        );
        return $sign==$param['sign'];
	}
	
	public function Dblog($orderId, $param)
	{
		$logger = new Log('glocash.log');
		try{
			if(is_string($param)){
				$message = $param;
			}else{
				$message = json_encode($param);
			}
				
			$this->db->query('
	            CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'glocash_log` (
				`id_log` int(10) unsigned NOT NULL AUTO_INCREMENT,
	            `id_order` varchar(50) NOT NULL,
	            `message` text NOT NULL,
	            `date_add` datetime NOT NULL,
	            PRIMARY KEY (`id_log`),
	            KEY `id_order` (`id_order`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8');
				
			$sql="insert into ".DB_PREFIX."glocash_log(id_order,message,date_add) values('".$orderId."','".$message."','".date("Y-m-d H:i:s", time())."') ";
	
			$this->db->query($sql);
		}
		catch (\exception $err){
			$logger->write('dblog error:'.$err->getMessage());
		}
	
	}
	
}