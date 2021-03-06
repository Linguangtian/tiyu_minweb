<?php
/**
 * ALIPAY API: koubei.marketing.data.customreport.detail.query request
 *
 * @author auto create
 *
 * @since  1.0, 2018-04-25 17:35:28
 */

namespace Alipay\Request;

class KoubeiMarketingDataCustomreportDetailQueryRequest extends AbstractAlipayRequest
{
    /**
     * 自定义数据报表规则详情查询接口
     **/
    private $bizContent;

    public function setBizContent($bizContent)
    {
        $this->bizContent = $bizContent;
        $this->apiParams['biz_content'] = $bizContent;
    }

    public function getBizContent()
    {
        return $this->bizContent;
    }
}
