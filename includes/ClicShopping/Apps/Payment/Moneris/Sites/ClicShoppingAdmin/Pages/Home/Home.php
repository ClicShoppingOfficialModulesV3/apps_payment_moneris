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

  namespace ClicShopping\Apps\Payment\Moneris\Sites\ClicShoppingAdmin\Pages\Home;

  use ClicShopping\OM\Registry;

  use ClicShopping\Apps\Payment\Moneris\Moneris;

  class Home extends \ClicShopping\OM\PagesAbstract {
    public mixed $app;

    protected function init() {
      $CLICSHOPPING_Moneris = new Moneris();
      Registry::set('Moneris', $CLICSHOPPING_Moneris);

      $this->app = $CLICSHOPPING_Moneris;

      $this->app->loadDefinitions('Sites/ClicShoppingAdmin/main');
    }
  }
