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

  use ClicShopping\OM\HTML;

  class payment_server extends \ClicShopping\Apps\Payment\Moneris\Module\ClicShoppingAdmin\Config\ConfigParamAbstract {
    public $default = 'Test';
    public $sort_order = 60;

    protected function init() {
      $this->title = $this->app->getDef('cfg_moneris_payment_server_title');
      $this->description = $this->app->getDef('cfg_moneris_payment_server_desc');
    }

    public function getInputField()  {
      $value = $this->getInputValue();

      $input =  HTML::radioField($this->key, 'Production', $value, 'id="' . $this->key . 'Production" autocomplete="off"') . $this->app->getDef('cfg_moneris_payment_server_production') . '<br /> ';
      $input .=  HTML::radioField($this->key, 'Test', $value, 'id="' . $this->key . 'Test" autocomplete="off"') . $this->app->getDef('cfg_moneris_payment_server_test') . '<br />';

      return $input;
    }
  }
