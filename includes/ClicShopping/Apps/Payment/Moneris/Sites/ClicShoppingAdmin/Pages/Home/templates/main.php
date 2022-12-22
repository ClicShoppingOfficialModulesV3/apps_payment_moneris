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

use ClicShopping\OM\HTML;
  use ClicShopping\OM\CLICSHOPPING;
  use ClicShopping\OM\Registry;
  use ClicShopping\OM\HTTP;

  $CLICSHOPPING_MessageStack = Registry::get('MessageStack');
  $CLICSHOPPING_Moneris = Registry::get('Moneris');

  if ($CLICSHOPPING_MessageStack->exists('Moneris')) {
    echo $CLICSHOPPING_MessageStack->get('Moneris');
  }
?>
  <div class="contentBody">
    <div class="row">
      <div class="col-md-12">
        <div class="card card-block headerCard">
          <div class="row">
            <span class="col-md-1 logoHeading"><?php echo HTML::image($CLICSHOPPING_Template->getImageDirectory() . 'categories/modules_modules_checkout_payment.gif', $CLICSHOPPING_Moneris->getDef('Moneris'), '40', '40'); ?></span>
            <span class="col-md-4 pageHeading"><?php echo '&nbsp;' . $CLICSHOPPING_Moneris->getDef('heading_title'); ?></span>
          </div>
        </div>
      </div>
    </div>
    <div class="separator"></div>
    <div class="col-md-12 mainTitle"><strong><?php echo $CLICSHOPPING_Moneris->getDef('text_moneris') ; ?></strong></div>
    <div class="adminformTitle">
      <div class="row">
        <div class="separator"></div>

        <div class="col-md-12">
          <div class="form-group">
            <div class="col-md-12">
              <?php echo $CLICSHOPPING_Moneris->getDef('text_intro');  ?>
              <?php echo $CLICSHOPPING_Moneris->getDef('approved_url', ['approve_url' => HTTP::typeUrlDomain() . 'index.php?Checkout&Process']);  ?>
              <?php echo $CLICSHOPPING_Moneris->getDef('declined_url', ['decline_url' => HTTP::typeUrlDomain() . 'index.php?Checkout&Billing']);  ?>

            </div>
          </div>
        </div>

        <div class="col-md-12 text-center">
          <div class="form-group">
            <div class="col-md-12">
<?php
  echo HTML::form('configure', CLICSHOPPING::link(null, 'A&Payment\Moneris&Configure'));
  echo HTML::button($CLICSHOPPING_Moneris->getDef('button_configure'), null, null, 'primary');
  echo '</form>';
?>
            </div>
          </div>
        </div>
      </div>
      <div class="separator"></div>
    </div>
  </div>
