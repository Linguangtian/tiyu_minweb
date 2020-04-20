<?php
/**
 * @link http://www.zjhejiang.com/
 * @copyright Copyright (c) 2018 浙江禾匠信息科技有限公司
 * @author Lu Wei
 *
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2018/5/30
 * Time: 11:25
 */


namespace app\modules\mch\models\mch;

use app\models\Option;
use app\modules\mch\models\MchModel;
use yii\helpers\Html;

class MchSettingForm extends MchModel
{
    public $store_id;
    public $entry_rules;
    public $bond_detail;
    public $bond_free;
    public $bond_open;
    public $bond_good_id;
    public $type;
    public $cs_icon;
    public $bond_stable;
    public $bond_install;
    public $bond_service;
    public $bond_original;
    public $bond_security;

    public function rules()
    {
        return [
            [['entry_rules'], 'string', 'max' => 10000,],
            [['bond_detail'], 'string', 'max' => 10000,],
            [['bond_free'], 'integer'],
            [['bond_open'], 'integer'],
            [['type'], 'safe'],
            [['cs_icon'], 'integer'],
            [['bond_stable'], 'integer'],
            [['bond_install'], 'integer'],
            [['bond_service'], 'integer'],
            [['bond_original'], 'integer'],
            [['bond_security'], 'integer']
        
        ];
    }

    public function attributeLabels()
    {
        return [
            'entry_rules' => '入驻协议',
        ];
    }

    public function search()
    {
        $default = [
            'entry_rules' => '',
            'type' => [],
            'cs_icon' => 0,
            'bond_detail' => '',
            'bond_free' => '',
            'bond_open' => 0,
            'bond_good_id' => 1,
            'bond_stable' => 0,
            'bond_install' => 0,
            'bond_service' => 0,
            'bond_original' => 0,
            'bond_security' => 0,

        ];
        $data = Option::get('mch_setting', $this->store_id, 'mch', $default);
        if (!isset($data['type']) || !$data['type']) {
            $data['type'] = [];
        }
        return $data;
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->errorResponse;
        }
        $data = [
            'entry_rules' => Html::encode($this->entry_rules),
            'bond_detail' => Html::encode($this->bond_detail),
            'type' => $this->type ? $this->type : [],
            'cs_icon' => $this->cs_icon,
            'bond_open' => $this->bond_open,
            'bond_free' => $this->bond_free,
            'bond_good_id' => $this->bond_good_id,
            'bond_install' => $this->bond_install,
            'bond_stable' => $this->bond_stable,
            'bond_service' => $this->bond_service,
            'bond_original' => $this->bond_original,
            'bond_security' => $this->bond_security,
        ];
        $res = Option::set('mch_setting', $data, $this->store_id, 'mch');
        if ($res) {
            return [
                'code' => 0,
                'msg' => '保存成功。',
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '保存失败。',
            ];
        }
    }
}
