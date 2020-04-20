<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/3
 * Time: 14:58
 */

namespace app\utils;

use app\models\SmsRecord;
use app\models\SmsSetting;
use Hejiang\Sms\Messages\TemplateMessage;
use Hejiang\Sms\Messages\VerificationCodeMessage;
use Hejiang\Sms\Senders\AlidayuSender;
use Hejiang\Sms\Senders\AliyunSender;
use app\models\user;
use app\models\Mch;

class Sms
{
    /**
     * 发送短信
     *
     * 短信通知
     * @param string $store_id 商铺ID
     * @param string $content 内容，字符串
     * @return array
     */
    public static function send($store_id, $content = null)
    {
        $sms_setting = SmsSetting::findOne(['is_delete' => 0, 'store_id' => $store_id]);
        if ($sms_setting->status == 0) {
            return [
                'code' => 1,
                'msg' => '短信通知服务未开启'
            ];
        }
        $content_sms[$sms_setting->msg] = substr($content, -8);
        $res = null;
        $resp = null;

        $a = str_replace("，", ",", $sms_setting->mobile);
        $g = explode(",", $a);
        foreach ($g as $mobile) {
            if (!$mobile) {
                continue;
            }
            try {
                $sender = new AliyunSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                $messageParams = [
                    'sender' => $sender,
                    'sign' => $sms_setting->sign,
                    'tplId' => $sms_setting->tpl,
                    'tplParams' => $content_sms,
                    'phoneNumber' => $mobile
                ];
                $message = new TemplateMessage($messageParams);
                $res = $message->send();
            } catch (\Exception $e) {
                \Yii::warning("阿里云短信调用失败：" . $e->getMessage());
                try {
                    $sender = new AlidayuSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                    $messageParams = [
                        'sender' => $sender,
                        'sign' => $sms_setting->sign,
                        'tplId' => $sms_setting->tpl,
                        'tplParams' => $content_sms,
                        'phoneNumber' => $mobile
                    ];
                    $message = new TemplateMessage($messageParams);
                    $resp = $message->send();
                } catch (\Exception $r_e) {
                    \Yii::warning("阿里大鱼调用失败：" . $r_e->getMessage());
                    return [
                        'code' => 1,
                        'msg' => $e->getMessage() . $r_e->getMessage()
                    ];
                }
            }
        }
        if (is_array($content_sms)) {
            foreach ($content_sms as $k => $v) {
                $content_sms[$k] = strval($v);
            }
            $content_sms = json_encode($content_sms, JSON_UNESCAPED_UNICODE);
        }
        $smsRecord = new SmsRecord();
        $smsRecord->mobile = $sms_setting->mobile;
        $smsRecord->tpl = $sms_setting->tpl;
        $smsRecord->content = $content_sms;
        $smsRecord->ip = \Yii::$app->request->userIP;
        $smsRecord->addtime = time();
        $smsRecord->save();
        return [
            'code' => 0,
            'msg' => '成功'
        ];
    }

    /**
     * 发送短信  退款通知
     * @param string $store_id 商铺ID
     * @param string $content 内容，字符串
     * @return array
     */
    public static function send_refund($store_id, $content = null)
    {
        $sms_setting = SmsSetting::findOne(['is_delete' => 0, 'store_id' => $store_id]);
        if ($sms_setting->status == 0) {
            return [
                'code' => 1,
                'msg' => '短信通知服务未开启'
            ];
        }
//        $content_sms[$sms_setting->msg] = substr($content, -8);
        $res = null;
        $resp = null;

        $a = str_replace("，", ",", $sms_setting->mobile);
        $g = explode(",", $a);
        $tpl = json_decode($sms_setting->tpl_refund, true);
        if (!is_array($tpl) || !$tpl['tpl']) {
            return [
                'code' => 1,
                'msg' => '未设置退款短信'
            ];
        }
        foreach ($g as $mobile) {
            try {
                $sender = new AliyunSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                $messageParams = [
                    'sender' => $sender,
                    'sign' => $sms_setting->sign,
                    'tplId' => $tpl['tpl'],
                    'tplParams' => [],
                    'phoneNumber' => $mobile
                ];
                $message = new TemplateMessage($messageParams);
                $res = $message->send();
            } catch (\Exception $e) {
//                \Yii::warning("阿里云短信调用失败：" . $e->getMessage());
                try {
                    $sender = new AlidayuSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                    $messageParams = [
                        'sender' => $sender,
                        'sign' => $sms_setting->sign,
                        'tplId' => $tpl['tpl'],
                        'tplParams' => [],
                        'phoneNumber' => $mobile
                    ];

                    $message = new TemplateMessage($messageParams);
                    $res = $message->send();
                } catch (\Exception $r_e) {

                    return [
                        'code' => 1,
                        'msg' => $e->getMessage().$r_e->getMessage()
                    ];
                }
            }
        }
        $smsRecord = new SmsRecord();
        $smsRecord->mobile = $sms_setting->mobile;
        $smsRecord->tpl = $tpl['tpl'];
        $smsRecord->content = '';
        $smsRecord->ip = \Yii::$app->request->userIP;
        $smsRecord->addtime = time();
        $smsRecord->save();
        return [
            'code' => 0,
            'msg' => '成功'
        ];
    }


    /**
     * 发送商家入驻短信通知
     *
     * 短信通知
     * @param string $store_id 商铺ID
     * @param string $content 内容，字符串
     * @return array
     */
    public static function sendSmsApply($store_id, $user_id,$content = null)
    {



        $mch=Mch::findone(['store_id' =>$store_id,'user_id'=>$user_id]);
        $sms_setting = SmsSetting::findOne(['is_delete' => 0, 'store_id' => $store_id]);
        if ($sms_setting->status == 0) {
            return [
                'code' => 1,
                'msg' => '短信通知服务未开启'
            ];
        }

        $res = null;
        $resp = null;

        $a = str_replace("，", ",", $sms_setting->mobile);
        $g = explode(",", $a);
        $tpl = json_decode($sms_setting->tpl_apply, true);
        $content_sms[$tpl['msg']] =substr($mch['realname'], -8); ;

        if (!is_array($tpl) || !$tpl['tpl']) {
            return [
                'code' => 1,
                'msg' => '未设置退款短信'
            ];
        }
        foreach ($g as $mobile) {
            try {
                $sender = new AliyunSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                $messageParams = [
                    'sender' => $sender,
                    'sign' => $sms_setting->sign,//YD独角兽平台
                    'tplId' => $tpl['tpl'],//SMS_161593301
                    'tplParams' => $content_sms,
                    'phoneNumber' => $mobile//18597609706
                ];


                $message = new TemplateMessage($messageParams);

                $res = $message->send();
            } catch (\Exception $e) {
//                \Yii::warning("阿里云短信调用失败：" . $e->getMessage());
                try {
                    $sender = new AlidayuSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                    $messageParams = [
                        'sender' => $sender,
                        'sign' => $sms_setting->sign,
                        'tplId' => $tpl['tpl'],
                        'tplParams' => [],
                        'phoneNumber' => $mobile
                    ];
                    $message = new TemplateMessage($messageParams);


                    $res = $message->send();
                } catch (\Exception $r_e) {

                    return [
                        'code' => 1,
                        'msg' => $e->getMessage().$r_e->getMessage()
                    ];
                }
            }
        }
        $smsRecord = new SmsRecord();
        $smsRecord->mobile = $sms_setting->mobile;
        $smsRecord->tpl = $tpl['tpl'];
        $smsRecord->content = '';
        $smsRecord->ip = \Yii::$app->request->userIP;
        $smsRecord->addtime = time();
        $smsRecord->save();
        return [
            'code' => 0,
            'msg' => '成功'
        ];


    }



    /**
     * 发送用户确认收获
     *
     * 短信通知
     *
     */
    public static function sendSms($store_id, $user_id,$type=null,$content = null)
    {


        $user=User::findone(['id'=>$user_id]);

        $sms_setting = SmsSetting::findOne(['is_delete' => 0, 'store_id' => $store_id]);
        if ($sms_setting->status == 0||!$type) {
            return [
                'code' => 1,
                'msg' => '短信通知服务未开启'
            ];
        }

        $res = null;
        $resp = null;

        $a = str_replace("，", ",", $sms_setting->mobile);
        $g = explode(",", $a);
        $tpl = json_decode($sms_setting->$type, true);
        $content_sms[$tpl['msg']] =substr($user['nickname'], -8);

        if (!is_array($tpl) || !$tpl['tpl']) {
            return [
                'code' => 1,
                'msg' => '未设置退款短信'
            ];
        }
        foreach ($g as $mobile) {
            try {
                $sender = new AliyunSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                $messageParams = [
                    'sender' => $sender,
                    'sign' => $sms_setting->sign,//YD独角兽平台
                    'tplId' => $tpl['tpl'],//SMS_161593301
                    'tplParams' => $content_sms,
                    'phoneNumber' => $mobile//18597609706
                ];


                $message = new TemplateMessage($messageParams);

                $res = $message->send();
            } catch (\Exception $e) {
                try {
                    $sender = new AlidayuSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                    $messageParams = [
                        'sender' => $sender,
                        'sign' => $sms_setting->sign,
                        'tplId' => $tpl['tpl'],
                        'tplParams' => [],
                        'phoneNumber' => $mobile
                    ];
                    $message = new TemplateMessage($messageParams);


                    $res = $message->send();
                } catch (\Exception $r_e) {

                    return [
                        'code' => 1,
                        'msg' => $e->getMessage().$r_e->getMessage()
                    ];
                }
            }
        }
        $smsRecord = new SmsRecord();
        $smsRecord->mobile = $sms_setting->mobile;
        $smsRecord->tpl = $tpl['tpl'];
        $smsRecord->content = '';
        $smsRecord->ip = \Yii::$app->request->userIP;
        $smsRecord->addtime = time();
        $smsRecord->save();
        return [
            'code' => 0,
            'msg' => '成功'
        ];


    }



    /**
     * 发送短信通知
     *
     *
     *
     */
    public static function sendSms_notice($store_id, $mobile,$type=null,$content = null)
    {

        $sms_setting = SmsSetting::findOne(['is_delete' => 0, 'store_id' => $store_id]);
        if ($sms_setting->status == 0||!$type) {
            return [
                'code' => 1,
                'msg' => '短信通知服务未开启'
            ];
        }

        $res = null;
        $resp = null;

        $a = str_replace("，", ",", $mobile);
        $g = explode(",", $a);


        $tpl = json_decode($sms_setting->$type, true);
        if (!is_array($tpl) || !$tpl['tpl']) {
            return [
                'code' => 1,
                'msg' => '未设置短信模板'
            ];
        }

        foreach ($g as $mobile) {
            try {
                $sender = new AliyunSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                $messageParams = [
                    'sender' => $sender,
                    'sign' => $sms_setting->sign,
                    'tplId' => $tpl['tpl'],
                    'tplParams' => [],
                    'phoneNumber' => $mobile
                ];
                $message = new TemplateMessage($messageParams);
                $res = $message->send();
            } catch (\Exception $e) {
//                \Yii::warning("阿里云短信调用失败：" . $e->getMessage());
                try {
                    $sender = new AlidayuSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                    $messageParams = [
                        'sender' => $sender,
                        'sign' => $sms_setting->sign,
                        'tplId' => $tpl['tpl'],
                        'tplParams' => [],
                        'phoneNumber' => $mobile
                    ];

                    $message = new TemplateMessage($messageParams);
                    $res = $message->send();
                } catch (\Exception $r_e) {

                    return [
                        'code' => 1,
                        'msg' => $e->getMessage().$r_e->getMessage()
                    ];
                }
            }
        }

        $smsRecord = new SmsRecord();
        $smsRecord->mobile = $sms_setting->mobile;
        $smsRecord->tpl = $tpl['tpl'];
        $smsRecord->content = '';
        $smsRecord->ip = \Yii::$app->request->userIP;
        $smsRecord->addtime = time();
        $smsRecord->save();
        return [
            'code' => 0,
            'msg' => '成功'
        ];


    }






    public static function send_text($store_id, $content = null, $mobile)
    {
        $sms_setting = SmsSetting::findOne(['is_delete' => 0, 'store_id' => $store_id]);
        $mobile_cache = \Yii::$app->cache->get('mobile_cache' . $mobile);
        if ($mobile_cache) {
            return [
                'code' => 1,
                'msg' => '请勿频繁发送短信',
                'data' => $mobile,
            ];
        }
        \Yii::$app->cache->set('mobile_cache' . $mobile, true, 60);
        if ($sms_setting->status == 0) {
            return [
                'code' => 1,
                'msg' => '短信通知服务未开启'
            ];
        }
        if (!$mobile) {
            return [
                'code' => 1,
                'msg' => '请输入手机号'
            ];
        }
        $tpl = json_decode($sms_setting->tpl_code, true);
        if (!is_array($tpl) || !$tpl['tpl']) {
            return [
                'code' => 1,
                'msg' => '未设置验证码短信'
            ];
        }
        $content_sms[$tpl['msg']] = $content;
        $res = null;
        $resp = null;

        try {
            $sender = new AliyunSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
            $messageParams = [
                'sender' => $sender,
                'sign' => $sms_setting->sign,
                'tplId' => $tpl['tpl'],
                'tplParams' => $content_sms,
                'phoneNumber' => $mobile
            ];
            $message = new VerificationCodeMessage($messageParams);
            $message->codePointer = &$message->tplParams['code'];
            $res = $message->send();
            $content_sms[$tpl['msg']] = $message->codePointer;
            \Yii::$app->cache->set('code_cache' . $mobile, $message, 600);
        } catch (\Exception $e) {
            \Yii::warning("阿里云短信调用失败：" . $e->getMessage());
            try {
                \Yii::$app->cache->delete('mobile_cache' . $mobile);
                $sender = new AlidayuSender($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                $messageParams = [
                    'sender' => $sender,
                    'sign' => $sms_setting->sign,
                    'tplId' => $tpl['tpl'],
                    'tplParams' => $content_sms,
                    'phoneNumber' => $mobile
                ];
                $message = new VerificationCodeMessage($messageParams);
                $message->codePointer = &$message->tplParams['code'];
                $res = $message->send();
                $content_sms[$tpl['msg']] = $message->codePointer;
                \Yii::$app->cache->set('code_cache' . $mobile, $message, 600);
            } catch (\Exception $r_e) {
                \Yii::$app->cache->delete('mobile_cache' . $mobile);
                return [
                    'code' => 2,
                    'msg' => $r_e->getMessage().$e->getMessage()
                ];
            }
        }
        if (is_array($content_sms)) {
            foreach ($content_sms as $k => $v) {
                $content_sms[$k] = strval($v);
            }
            $content_sms = json_encode($content_sms, JSON_UNESCAPED_UNICODE);
        }
        $smsRecord = new SmsRecord();
        $smsRecord->mobile = $mobile;
        $smsRecord->tpl = $tpl['tpl'];
        $smsRecord->content = $content_sms;
        $smsRecord->ip = \Yii::$app->request->userIP;
        $smsRecord->addtime = time();
        $smsRecord->save();
        return [
            'code' => 0,
            'msg' => '成功'
        ];
    }
}
