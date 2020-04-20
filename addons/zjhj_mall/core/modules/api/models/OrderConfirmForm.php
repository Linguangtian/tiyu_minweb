<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/7/20
 * Time: 10:25
 */

namespace app\modules\api\models;

use app\utils\PinterOrder;
use app\models\Level;
use app\models\Order;
use app\models\OrderDetail;
use app\models\PrinterSetting;
use app\models\User;
use app\models\MchPlugin;
use app\models\UserShareMoney;
use app\models\Setting;
use app\models\Option;
use app\models\Mch;
use app\utils\SendMail;
use app\utils\Sms;


class OrderConfirmForm extends ApiModel
{
    public $store_id;
    public $user_id;
    public $order_id;
    public $share_setting;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $order = Order::findOne([
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'id' => $this->order_id,
            'is_send' => 1,
            'is_delete' => 0,
        ]);
        if (!$order) {
            return [
                'code' => 1,
                'msg' => '订单不存在'
            ];
        }
        $order->is_confirm = 1;
        $order->confirm_time = time();
        if ($order->pay_type == 2) {
            $order->is_pay = 1;
            $order->pay_time = time();
        }
        /*
                $user = User::findOne(['id' => $order->user_id, 'store_id' => $this->store_id]);
                $order_money = Order::find()->where(['store_id' => $this->store_id, 'user_id' => $user->id, 'is_delete' => 0])
                    ->andWhere(['is_pay' => 1, 'is_confirm' => 1])->select([
                        'sum(pay_price)'
                    ])->scalar();
                $next_level = Level::find()->where(['store_id' => $this->store_id, 'is_delete' => 0,'status'=>1])
                    ->andWhere(['<', 'money', $order_money])->orderBy(['level' => SORT_DESC])->asArray()->one();
                if ($user->level < $next_level['level']) {
                    $user->level = $next_level['level'];
                    $user->save();
                }
        */

        if ($order->save()) {
           // $this->share_money($this->order_id);
            $this->bond_free($this->order_id);

            $mail = new SendMail($this->store_id,$this->order_id,0,$this->user_id);
            $mail->SendMailConfirm();

            //发送短信通知
            Sms::sendSms($this->store_id,$this->user_id,'tpl_complete');


            $printer_order = new PinterOrder($this->store_id, $order->id, 'confirm', 0);
            $res = $printer_order->print_order();
            return [
                'code' => 0,
                'msg' => '已确认收货'
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '确认收货失败'
            ];
        }
    }


    private function bond_free($id){

        $order = OrderDetail::findOne(['order_id'=>$id]);

        $setting = Option::get('mch_setting', $this->store->id, 'mch', []);
        $bond_stable = trim(isset($setting['bond_stable']) ? $setting['bond_stable'] : '');
        $bond_install = trim(isset($setting['bond_install']) ? $setting['bond_install'] : '');
        $bond_service = trim(isset($setting['bond_service']) ? $setting['bond_service'] : '');
        $bond_original = trim(isset($setting['bond_original']) ? $setting['bond_original'] : '');
        $bond_security = trim(isset($setting['bond_security']) ? $setting['bond_security'] : '');




        if($order['goods_id']==$bond_stable){
            mch::updateAll(['bond_stable' =>1], ['store_id'=> $this->store_id,'user_id'=>$this->user_id]);
        }elseif($order['goods_id']==$bond_install){
            mch::updateAll(['bond_install' =>1], ['store_id'=> $this->store_id,'user_id'=>$this->user_id]);
        }elseif($order['goods_id']==$bond_service){

            mch::updateAll(['bond_service' =>1], ['store_id'=> $this->store_id,'user_id'=>$this->user_id]);
        }elseif($order['goods_id']==$bond_original){
            mch::updateAll(['bond_original' =>1], ['store_id'=> $this->store_id,'user_id'=>$this->user_id]);
        }elseif($order['goods_id']==$bond_security){
            mch::updateAll(['bond_security' =>1], ['store_id'=> $this->store_id,'user_id'=>$this->user_id]);
        }


    }


    private function share_money($id)
    {
        $this->share_setting = Setting::findOne(['store_id' => $this->store_id]);
        $order = Order::findOne($id);
        if ($order->mch_id > 0) {
            $mchPlugin = MchPlugin::findOne(['mch_id' => $order->mch_id, 'store_id' => $this->store_id]);
            if(!$mchPlugin || $mchPlugin->is_share == 0){
                return ;
            }
            $mchSetting = MchSetting::findOne(['mch_id' => $order->mch_id, 'store_id' => $this->store_id]);
            if (!$mchSetting || $mchSetting->is_share == 0) {
                return;
            }
        }
        if ($this->share_setting->level == 0) {
            return;
        }
        if ($order->is_price != 0) {
            return;
        }
        //分销商自购返利
        $order->share_price = 0;
        if ($order->rebate > 0) {
            $user = User::findOne(['id' => $order->user_id]);
            $user->total_price += doubleval($order->rebate);
            $user->price += doubleval($order->rebate);
            $user->save();
            $order->is_price = 1;
            $order->share_price += doubleval($order->rebate);
            UserShareMoney::set($order->rebate, $user->id, $order->id, 0, 4, $order->store_id, 0);
        }
        //仅自购
        if ($this->share_setting->level == 4) {
            $order->save();
            return;
        }
        //一级佣金发放
        if ($this->share_setting->level >= 1) {
            $user_1 = User::findOne($order->parent_id);
            if (!$user_1) {
                $order->save();
                return;
            }
            $user_1->total_price += $order->first_price;
            $user_1->price += $order->first_price;
            $user_1->save();
            UserShareMoney::set($order->first_price, $user_1->id, $order->id, 0, 1, $this->store_id, 0);
            $order->is_price = 1;
            $order->share_price += doubleval($order->first_price);
        }
        //二级佣金发放
        if ($this->share_setting->level >= 2) {
            $user_2 = User::findOne($order->parent_id_1);
            if (!$user_2) {
                if ($user_1->parent_id != 0 && $order->parent_id_1 == 0) {
                    $res = self::money($user_1->parent_id, $order->second_price);
                    UserShareMoney::set($order->second_price, $user_1->parent_id, $order->id, 0, 2, $this->store_id, 0);
                    if ($res['parent_id'] != 0 && $this->share_setting->level == 3) {
                        $res = self::money($res['parent_id'], $order->third_price);
                        UserShareMoney::set($order->third_price, $res['parent_id'], $order->id, 0, 3, $this->store_id, 0);
                    }
                }
                $order->save();
                return;
            }
            $user_2->total_price += $order->second_price;
            $user_2->price += $order->second_price;
            $user_2->save();
            UserShareMoney::set($order->second_price, $user_2->id, $order->id, 0, 2, $this->store_id, 0);
            $order->share_price += doubleval($order->second_price);
        }
        //三级佣金发放
        if ($this->share_setting->level >= 3) {
            $user_3 = User::findOne($order->parent_id_2);
            if (!$user_3) {
                if ($user_2->parent_id != 0 && $order->parent_id_2 == 0) {
                    self::money($user_2->parent_id, $order->third_price);
                    UserShareMoney::set($order->third_price, $user_2->parent_id, $order->id, 0, 3, $this->store_id, 0);
                }
                $order->save();
                return;
            }
            $user_3->total_price += $order->third_price;
            $user_3->price += $order->third_price;
            $user_3->save();
            UserShareMoney::set($order->third_price, $user_3->id, $order->id, 0, 3, $this->store_id, 0);
            $order->share_price += doubleval($order->third_price);
        }
        $order->save();
        return;
    }

}
