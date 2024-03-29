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

  class payment_server_simulation extends \ClicShopping\Apps\Payment\Moneris\Module\ClicShoppingAdmin\Config\ConfigParamAbstract {
    public $default = 'False';
    public $sort_order = 60;

    protected function init() {
      $this->title = $this->app->getDef('cfg_moneris_payment_server_simulation_title');
      $this->description = $this->app->getDef('cfg_moneris_payment_server_simulation_desc');
    }

    public function getInputField()  {
      $value = $this->getInputValue();

      $input =  HTML::radioField($this->key, 'True', $value, 'id="' . $this->key . '1" autocomplete="off"') . $this->app->getDef('cfg_moneris_payment_server_simulation') . '<br /> ';
      $input .=  HTML::radioField($this->key, 'False', $value, 'id="' . $this->key . '0" autocomplete="off"') . $this->app->getDef('cfg_moneris_payment_server_no_simulation') . '<br />';

      return $input;
    }
  }
