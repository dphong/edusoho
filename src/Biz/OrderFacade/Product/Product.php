<?php

namespace Biz\OrderFacade\Product;

use AppBundle\Common\StringToolkit;
use Biz\OrderFacade\Command\Deduct\PickedDeductWrapper;
use Biz\Sms\Service\SmsService;
use Codeages\Biz\Framework\Context\BizAware;

abstract class Product extends BizAware
{
    /**
     * 商品ID
     *
     * @var int
     */
    public $targetId;

    /**
     * 商品类型
     *
     * @var string
     */
    public $targetType;

    /**
     * 商品名称
     *
     * @var string
     */
    public $title;

    /**
     * 商品价格
     *
     * @var float
     */
    public $price;

    /**
     * 可使用的折扣
     *
     * @var array
     */
    public $availableDeducts = array();

    /**
     * 使用到的折扣
     *
     * @var array
     */
    public $pickedDeducts = array();

    /**
     * 返回的链接
     *
     * @var string
     */
    public $backUrl = '';

    /**
     * 成功支付返回链接
     *
     * @var string
     */
    public $successUrl = '';

    /**
     * 最大虚拟币抵扣百分比
     *
     * @var int
     */
    public $maxRate = 100;

    abstract public function init(array $params);

    abstract public function validate();

    public function setAvailableDeduct($params = array())
    {
        /** @var $pickedDeductWrapper PickedDeductWrapper */
        $availableDeductWrapper = $this->biz['order.product.available_deduct_wrapper'];

        $availableDeductWrapper->wrapper($this, $params);
    }

    public function setPickedDeduct($params)
    {
        /** @var $pickedDeductWrapper PickedDeductWrapper */
        $pickedDeductWrapper = $this->biz['order.product.picked_deduct_wrapper'];

        $pickedDeductWrapper->wrapper($this, $params);
    }

    public function getPayablePrice()
    {
        $payablePrice = $this->price;
        foreach ($this->pickedDeducts as $deduct) {
            $payablePrice -= $deduct['deduct_amount'];
        }

        return $payablePrice > 0 ? $payablePrice : 0;
    }

    public function paidCallback($orderItem)
    {
        $this->smsCallback($orderItem);
        $this->callback($orderItem);
    }

    public function callback($orderItem)
    {
    }

    protected function smsCallback($orderItem)
    {
        $smsType = 'sms_'.$this->targetType.'_buy_notify';

        if ($this->getSmsService()->isOpen($smsType)) {
            $userId = $orderItem['user_id'];
            $parameters = array();
            $parameters['order_title'] = $orderItem['title'];
            $parameters['order_title'] = StringToolkit::cutter($parameters['order_title'], 20, 15, 4);
            $parameters['totalPrice'] = $orderItem['order']['pay_amount'].'元';

            $description = $parameters['order_title'].'成功回执';

            $this->getSmsService()->smsSend($smsType, array($userId), $description, $parameters);
        }
    }

    /**
     * @return SmsService
     */
    private function getSmsService()
    {
        return $this->biz->service('Sms:SmsService');
    }
}
