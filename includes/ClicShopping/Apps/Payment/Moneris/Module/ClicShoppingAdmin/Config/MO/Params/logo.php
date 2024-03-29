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

  class logo extends \ClicShopping\Apps\Payment\Moneris\Module\ClicShoppingAdmin\Config\ConfigParamAbstract {
    public $default = 'moneris.jpg';
    public $sort_order = 30;

    protected function init() {
      $this->title = $this->app->getDef('cfg_moneris_title');
      $this->description = $this->app->getDef('cfg_moneris_desc');
    }
  }
