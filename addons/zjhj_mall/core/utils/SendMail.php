<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/12
 * Time: 14:19
 */

namespace app\utils;

use app\models\Goods;
use app\models\MailSetting;
use app\models\MsGoods;
use app\models\MsOrder;
use app\models\Order;
use app\models\OrderDetail;
use app\models\PtGoods;
use app\models\PtOrder;
use app\models\PtOrderDetail;
use app\models\Store;
use app\models\YyGoods;
use app\models\YyOrder;
use app\models\Mch;
use app\models\User;

class SendMail
{
    public $store_id;
    public $order_id;
    public $type;

    /**
     * SendMail constructor.
     * @param int $store_id
     * @param int $order_id 订单id
     * @param int $type 订单类型 0--商城订单 1--秒杀订单 2--拼团订单 3--预约订单
     *
     */
    public function __construct($store_id, $order_id, $type = 0,$user_id=0)
    {
        $this->store_id = $store_id;
        $this->order_id = $order_id;
        $this->user_id = $user_id;
        $this->type = $type;
    }

    public function send()
    {
        $mail_setting = MailSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1]);
        if (!$mail_setting) {
            return false;
        }
        if ($this->type == 0) {
            $order = Order::findOne(['id' => $this->order_id]);
            $goods_list = $this->getOrderGoodsList($this->order_id);
        } elseif ($this->type == 1) {
            $order = MsOrder::find()->where(['id' => $this->order_id])->asArray()->one();
            $goods_list = $this->getMsOrderGoodsList($this->order_id);
        } elseif ($this->type == 2) {
            $order = PtOrder::findOne(['id' => $this->order_id]);
            $goods_list = $this->getPtOrderGoodsList($this->order_id);
        } elseif ($this->type == 3) {
            $order = YyOrder::find()->where(['id' => $this->order_id])->asArray()->one();
            $goods_list = $this->getYyOrderGoodsList($this->order_id);
        }
        $store = Store::findOne($this->store_id);
        $receive = str_replace("，", ",", $mail_setting->receive_mail);
        $receive_mail = explode(",", $receive);
        $res = true;
        $user=User::findone(['id'=>$order['user_id']]);

          foreach ($receive_mail as $mail) {
            try {
                $mailer = \Yii::$app->mailer;
                $mailer->transport = $mailer->transport->newInstance('smtp.qq.com', 465, 'ssl');
                $mailer->transport->setUsername($mail_setting->send_mail);
                $mailer->transport->setPassword($mail_setting->send_pwd);
                $compose = $mailer->compose('setMail', [
                    'store_name' => $store->name,
                    'goods_list'=>$goods_list,
                    'order'=>$order,
                    'type'=>$this->type,
                    'user_name'=>$user['nickname']
                ]);
                $compose->setFrom($mail_setting->send_mail); //要发送给那个人的邮箱
                $compose->setTo($mail); //要发送给那个人的邮箱
                $compose->setSubject($mail_setting->send_name); //邮件主题
                $res = $compose->send();
            } catch (\Exception $e) {
                \Yii::warning('邮件发送失败：' . $e->getMessage());
            }
        }
        return $res;
    }

    /**
     * @param $order_id
     * @return mixed
     * 拼团订单商品详情
     */
    private function getPtOrderGoodsList($order_id)
    {
        $order_detail_list = PtOrderDetail::find()->alias('od')
            ->leftJoin(['g' => PtGoods::tableName()], 'od.goods_id=g.id')
            ->where([
                'od.is_delete' => 0,
                'od.order_id' => $order_id,
            ])->select('od.*,g.name')->asArray()->all();
        return $order_detail_list;
    }

    /**
     * @param $order_id
     * @return mixed
     * 订单商品详情
     */
    private function getOrderGoodsList($order_id)
    {
        $order_detail_list = OrderDetail::find()->alias('od')
            ->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')
            ->where([
                'od.is_delete' => 0,
                'od.order_id' => $order_id,
            ])->select('od.*,g.name')->asArray()->all();
        return $order_detail_list;
    }

    /**
     * @param $order_id
     * @return mixed
     * 秒杀订单商品详情
     */
    private function getMsOrderGoodsList($order_id)
    {
        $order_detail_list = MsGoods::find()->alias('g')
            ->leftJoin(['o'=>MsOrder::tableName()], 'o.goods_id=g.id and o.id='.$order_id)
            ->where([
                'o.is_delete'=>0,
            ])->select('g.*')->asArray()->all();
        $order_detail_list = MsOrder::find()->alias('o')
            ->leftJoin(['g'=>MsGoods::tableName()], 'g.id=o.goods_id')
            ->where(['o.id'=>$order_id,'o.is_delete'=>0])
            ->select(['o.*','g.name'])->asArray()->all();
        return $order_detail_list;
    }

    /**
     * @param $order_id
     * @return mixed
     * 订单商品详情
     */
    private function getYyOrderGoodsList($order_id)
    {
        $order_detail_list = YyGoods::find()->alias('g')
            ->leftJoin(['o'=>YyOrder::tableName()], 'o.goods_id=g.id and o.id='.$order_id)
            ->where([
                'o.is_delete'=>0,
            ])->select('g.*')->asArray()->one();
        return $order_detail_list;
    }

	
	
	  public function SendMailVisi($data=0){
        $mail_setting = MailSetting::findOne(['store_id' =>$this->store_id, 'is_delete' => 0, 'status' => 1]);
        if (!$mail_setting) {
            return false;
        }
        $store = Store::findOne($this->store_id);
        $yesterday = date('Ymd', strtotime('-1 days'));
        $sql='select * from  ims_wxapp_general_analysis where uniacid=\''.$store['acid'].'\' and ref_date=\''.$yesterday.'\'';
        $data_yesterday=\Yii::$app->db->createCommand($sql)->queryAll();
        $store = Store::findOne($this->store_id);
        $receive = str_replace("，", ",", $mail_setting->receive_mail);
        $receive_mail = explode(",", $receive);
        $res = true;
        foreach ($receive_mail as $mail) {
            try {
                $mailer = \Yii::$app->mailer;
                $mailer->transport = $mailer->transport->newInstance('smtp.qq.com', 465, 'ssl');
                $mailer->transport->setUsername($mail_setting->send_mail);
                $mailer->transport->setPassword($mail_setting->send_pwd);
                $compose = $mailer->compose('dailyVisi', [
                    'store_name' => $store->name,
                    'data' => $data_yesterday['0'],
                    'yesterday' => $yesterday,

                ]);
                $compose->setFrom($mail_setting->send_mail); //要发送给那个人的邮箱
                $compose->setTo($mail); //要发送给那个人的邮箱
                $compose->setSubject($mail_setting->send_name); //邮件主题
                $res = $compose->send();
            } catch (\Exception $e) {
                \Yii::warning('邮件发送失败：' . $e->getMessage());
                return false;
            }
        }
        return $res;



    }
	
    /*
     * 申请入驻
     * */

    public function SendMailApply(){
        $mail_setting = MailSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1]);
        if (!$mail_setting) {
            return false;
        }
        $store = Store::findOne($this->store_id);
        $receive = str_replace("，", ",", $mail_setting->receive_mail);
        $receive_mail = explode(",", $receive);
        $res = true;

        $mch=Mch::findone(['store_id' =>$this->store_id,'user_id'=>$this->user_id]);

        foreach ($receive_mail as $mail) {
            try {
                $mailer = \Yii::$app->mailer;
                $mailer->transport = $mailer->transport->newInstance('smtp.qq.com', 465, 'ssl');
                $mailer->transport->setUsername($mail_setting->send_mail);
                $mailer->transport->setPassword($mail_setting->send_pwd);
                $compose = $mailer->compose('shopApply', [
                    'store_name' => $store->name,
                    'user_name' =>  $mch['realname'],
                    'user_tel' =>  $mch['tel'],
                ]);
                $compose->setFrom($mail_setting->send_mail); //要发送给那个人的邮箱
                $compose->setTo($mail); //要发送给那个人的邮箱
                $compose->setSubject($mail_setting->send_name); //邮件主题
                $res = $compose->send();
            } catch (\Exception $e) {
                \Yii::warning('邮件发送失败：' . $e->getMessage());
                return false;
            }
        }
        return $res;



    }
 /*
     *
     * */

    public function SendMailConfirm(){
        $mail_setting = MailSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1]);
        if (!$mail_setting) {
            return false;
        }
        $store = Store::findOne($this->store_id);
        $receive = str_replace("，", ",", $mail_setting->receive_mail);
        $receive_mail = explode(",", $receive);
        $res = true;


        $order = Order::findOne(['id' => $this->order_id]);
        foreach ($receive_mail as $mail) {
            try {
                $mailer = \Yii::$app->mailer;
                $mailer->transport = $mailer->transport->newInstance('smtp.qq.com', 465, 'ssl');
                $mailer->transport->setUsername($mail_setting->send_mail);
                $mailer->transport->setPassword($mail_setting->send_pwd);
                $compose = $mailer->compose('orderConfirm', [
                    'store_name' => $store->name,
                    'order' =>  $order,
                ]);
                $compose->setFrom($mail_setting->send_mail); //要发送给那个人的邮箱
                $compose->setTo($mail); //要发送给那个人的邮箱
                $compose->setSubject($mail_setting->send_name); //邮件主题
                $res = $compose->send();
            } catch (\Exception $e) {
                \Yii::warning('邮件发送失败：' . $e->getMessage());
                return false;
            }
        }
        return $res;



    }




    /**
     * @return bool
     * 新的售后订单
     */
    public function send_refund()
    {
        $mail_setting = MailSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1]);
        if (!$mail_setting) {
            return false;
        }
        $store = Store::findOne($this->store_id);
        $receive = str_replace("，", ",", $mail_setting->receive_mail);
        $receive_mail = explode(",", $receive);
        $res = true;
        $order = Order::findOne(['id' => $this->order_id]);
        foreach ($receive_mail as $mail) {
            try {
                $mailer = \Yii::$app->mailer;
                $mailer->transport = $mailer->transport->newInstance('smtp.qq.com', 465, 'ssl');
                $mailer->transport->setUsername($mail_setting->send_mail);
                $mailer->transport->setPassword($mail_setting->send_pwd);
                $compose = $mailer->compose('setMailRefund', [
                    'store_name' => $store->name,
                    'order' => $order,
                ]);
                $compose->setFrom($mail_setting->send_mail); //要发送给那个人的邮箱
                $compose->setTo($mail); //要发送给那个人的邮箱
                $compose->setSubject($mail_setting->send_name); //邮件主题
                $res = $compose->send();
            } catch (\Exception $e) {
                \Yii::warning('邮件发送失败：' . $e->getMessage());
                return false;
            }
        }
        return $res;
    }
}
