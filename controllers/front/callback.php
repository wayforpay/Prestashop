<?php

require_once(dirname(__FILE__) . '../../../wayforpay.php');
require_once(dirname(__FILE__) . '../../../wayforpay.cls.php');

class WayforpayCallbackModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public $display_header = false;
    public $display_footer = false;
    public $ssl = true;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        try {

            $data = json_decode(file_get_contents("php://input"), true);

            $order_id = !empty($data['orderReference']) ? $data['orderReference'] : null;
            $order = new OrderCore(intval($order_id));
            if (empty($order)) {
                die('Заказ не найден');
            }

            $wayForPayCls = new WayForPayCls();

            $isPaymentValid = $wayForPayCls->isPaymentValid($data);
            if ($isPaymentValid !== true) {
                exit($isPaymentValid);
            }

            list($orderId,) = explode(WayForPayCls::ORDER_SEPARATOR, $data['orderReference']);
            $history = new OrderHistory();
            $history->id_order = $orderId;
            $history->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), $orderId);
            $history->addWithemail(true, array(
                'order_name' => $orderId
            ));

            echo $wayForPayCls->getAnswerToGateWay($data);
            exit();
        } catch (Exception $e) {
            exit(get_class($e) . ': ' . $e->getMessage());
        }
    }
}