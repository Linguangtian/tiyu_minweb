<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/23
 * Time: 16:15
 */

namespace app\modules\mch\models;

/**
 * @property \app\models\SmsSetting $sms;
 */
class SmsForm extends MchModel
{
    public $sms;
    public $store_id;

    public $AccessKeyId;
    public $AccessKeySecret;
    public $name;
    public $sign;
    public $tpl;
    public $msg;
    public $status;
    public $mobile;
    public $refund;
    public $code;
    public $apply;
    public $complete;
    public $reservation;
    public $bond_success;
    public $bond_fail;

    public function rules()
    {
        return [
            [['AccessKeyId','AccessKeySecret','name','sign','tpl','status','mobile'],'required','on'=>'SUCCESS'],
            [['AccessKeyId','AccessKeySecret','name','sign','tpl','status','mobile','msg','refund','code','apply','complete','reservation','bond_fail','bond_success'],'trim'],
            [['msg',],'string'],
            [['status'],'in','range'=>[0,1]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'AccessKeyId' => '阿里云AccessKeyId',
            'AccessKeySecret' => '阿里云AccessKeySecret',
            'name' => '短信模板名称',
            'sign' => '短信模板签名',
            'tpl' => '短信模板ID',
            'msg' => '短信模板参数',
            'status' => '开启状态',
            'mobile' => '接受短信手机号',
        ];
    }
    public function save()
    {


        if ($this->validate()) {
            if ($this->sms->isNewRecord) {
                $this->sms->is_delete = 0;
                $this->sms->addtime = time();
                $this->sms->store_id = $this->store_id;
            }
            $this->sms->attributes = $this->attributes;
            foreach ($this->refund as $k => $v) {
                $this->refund[$k] = trim($v);
            }
            $this->sms->tpl_refund = \Yii::$app->serializer->encode($this->refund);
            foreach ($this->code as $k => $v) {
                $this->code[$k] = trim($v);
            }
            $this->sms->tpl_code = \Yii::$app->serializer->encode($this->code);


            foreach ($this->apply as $k => $v) {
                $this->apply[$k] = trim($v);
            }
            $this->sms->tpl_apply = \Yii::$app->serializer->encode($this->apply);

            foreach ($this->complete as $k => $v) {
                $this->complete[$k] = trim($v);
            }
            $this->sms->tpl_complete = \Yii::$app->serializer->encode($this->complete);


            foreach ($this->reservation as $k => $v) {
                $this->reservation[$k] = trim($v);
            }
            $this->sms->tpl_reservation = \Yii::$app->serializer->encode($this->reservation);

            foreach ($this->bond_fail as $k => $v) {
                $this->bond_fail[$k] = trim($v);
            }
            $this->sms->tpl_bond_fail = \Yii::$app->serializer->encode($this->bond_fail);

            foreach ($this->bond_success  as $k => $v) {
                $this->bond_success[$k] = trim($v);
            }
            $this->sms->tpl_bond_success = \Yii::$app->serializer->encode($this->bond_success);


            if ($this->sms->save()) {
                return [
                    'code'=>0,
                    'msg'=>'成功'
                ];
            } else {
                return [
                    'code'=>1,
                    'msg'=>'网络异常'
                ];
            }
        } else {
            return $this->errorResponse;
        }
    }
}
