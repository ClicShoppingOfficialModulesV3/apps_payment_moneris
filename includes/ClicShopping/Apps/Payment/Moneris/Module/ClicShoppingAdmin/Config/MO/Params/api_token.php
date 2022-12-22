<?php
  /**
 *
 *  @copyright 2008 - https://www.clicshopping.org
 *  @Brand : ClicShopping(Tm) at Inpi all right Reserved
 *  @Licence GPL 2 & MIT
 *  @licence MIT - Portion of osCommerce 2.4
 *  @Info : https://www.clicshopping.org/forum/trademark/
 *
 */

  namespace ClicShopping\Apps\Payment\Moneris\Module\ClicShoppingAdmin\Config\MO\Params;

  class api_token extends \ClicShopping\Apps\Payment\Moneris\Module\ClicShoppingAdmin\Config\ConfigParamAbstract {
    public $default = '';
    public $sort_order = 50;

    protected function init() {
      $this->title = $this->app->getDef('cfg_moneris_api_token_title');
      $this->description = $this->app->getDef('cfg_moneris_api_token_desc');
    }
  }
