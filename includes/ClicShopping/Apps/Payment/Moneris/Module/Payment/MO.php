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

  namespace ClicShopping\Apps\Payment\Moneris\Module\Payment;

  use ClicShopping\OM\HTML;
  use ClicShopping\OM\Registry;
  use ClicShopping\OM\CLICSHOPPING;
  use ClicShopping\OM\HTTP;

  use ClicShopping\Apps\Payment\Moneris\Moneris as MonerisApp;
  use ClicShopping\Sites\Common\B2BCommon;


  class MO implements \ClicShopping\OM\Modules\PaymentInterface  {

    public $code;
    public $title;
    public $description;
    public $enabled;
    public mixed $app;
    public $title_selection;

    public function __construct() {
      $CLICSHOPPING_Customer = Registry::get('Customer');

      if (Registry::exists('Order')) {
        $CLICSHOPPING_Order = Registry::get('Order');
      }

      if (!Registry::exists('Moneris')) {
        Registry::set('Moneris', new MonerisApp());
      }

      $this->app = Registry::get('Moneris');
      $this->app->loadDefinitions('Module/Shop/MO/MO');


      $this->signature = 'Moneris|' . $this->app->getVersion() . '|1.0';
      $this->api_version = $this->app->getApiVersion();

      $this->code = 'MO';
      $this->title = $this->app->getDef('module_moneris_title');
      $this->public_title = $this->app->getDef('module_moneris_public_title');

// Activation module du paiement selon les groupes B2Bs
      if (defined('CLICSHOPPING_APP_MONERIS_MO_STATUS')) {
        if ($CLICSHOPPING_Customer->getCustomersGroupID() != 0) {
          if ( B2BCommon::getPaymentUnallowed($this->code)) {
            if (CLICSHOPPING_APP_MONERIS_MO_STATUS == 'True') {
              $this->enabled = true;
            }  else {
              $this->enabled = false;
            }
          }
        } else {
          if (CLICSHOPPING_APP_MONERIS_MO_NO_AUTHORIZE == 'True' && $CLICSHOPPING_Customer->getCustomersGroupID() == 0) {
            if ($CLICSHOPPING_Customer->getCustomersGroupID() == 0) {
              if (CLICSHOPPING_APP_MONERIS_MO_STATUS == 'True') {
                $this->enabled = true;
              }  else {
                $this->enabled = false;
              }
            }
          }
        }

        if ((int)CLICSHOPPING_APP_MONERIS_MO_PREPARE_ORDER_STATUS_ID > 0) {
          $this->order_status = CLICSHOPPING_APP_MONERIS_MO_PREPARE_ORDER_STATUS_ID;
        }

  // server connexion
        if (CLICSHOPPING_APP_MONERIS_MO_PAYMENT_SERVER == 'Production') {
          $this->form_action_url = 'https://www3.moneris.com/HPPDP/index.php';
        } else {
          $this->form_action_url = 'https://esqa.moneris.com/HPPDP/index.php';
        }

        if ( $this->enabled === true ) {
          if ( isset($CLICSHOPPING_Order) && is_object($CLICSHOPPING_Order)) {
            $this->update_status();
          }
        }

        $this->sort_order = defined('CLICSHOPPING_APP_MONERIS_MO_SORT_ORDER') ? CLICSHOPPING_APP_MONERIS_MO_SORT_ORDER : 0;
      }

      if (!defined('CLICSHOPPING_APP_MONERIS_MO_STORE_ID') || !defined('CLICSHOPPING_APP_MONERIS_MO_API_TOkEN')) {
        $this->enabled = false;
      }
    }

    public function update_status() {
      $CLICSHOPPING_Order = Registry::get('Order');

      if ( ($this->enabled === true) && ((int)CLICSHOPPING_APP_MONERIS_MO_ZONE > 0)) {
        $check_flag = false;

        $Qcheck = $this->app->db->get('zones_to_geo_zones', 'zone_id', ['geo_zone_id' => CLICSHOPPING_APP_MONERIS_MO_ZONE,
                                                                        'zone_country_id' => $CLICSHOPPING_Order->delivery['country']['id']
                                                                        ],
                                                                        'zone_id'
                                      );

        while ($Qcheck->fetch()) {
          if (($Qcheck->valueInt('zone_id') < 1) || ($Qcheck->valueInt('zone_id') == $CLICSHOPPING_Order->delivery['zone_id'])) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag === false) {
          $this->enabled = false;
        }
      }
    }

    public function javascript_validation() {
      return false;
    }

    public function selection() {
      $CLICSHOPPING_Template = Registry::get('Template');

      if (defined('CLICSHOPPING_APP_MONERIS_MO_LOGO') && !empty(CLICSHOPPING_APP_MONERIS_MO_LOGO)) {
       $this->public_title = $this->title . '&nbsp;&nbsp;&nbsp;' . HTML::image($CLICSHOPPING_Template->getDirectoryTemplateImages() . 'logos/payment/' . CLICSHOPPING_APP_MONERIS_MO_LOGO);
      } else {
        $this->public_title = $this->title;
      }

      return ['id' => $this->app->vendor . '\\' . $this->app->code . '\\' . $this->code,
              'module' => $this->public_title
             ];
    }

    public function pre_confirmation_check() {
      $error = false;

      if (empty(CLICSHOPPING_APP_MONERIS_MO_STORE_ID) && empty(CLICSHOPPING_APP_MONERIS_MO_API_TOkEN)) {
        $error = true;
        $message = 'Store_id or API error';
      }

      if ($error !== false) {
        CLICSHOPPING::redirect(null, 'Checkout&Billing&payment_error=' . $this->code . '&ErrMsg=' . $message, true, false);
      }

      return false;
    }

    public function confirmation() {
      $CLICSHOPPING_Template = Registry::get('Template');
      $this->title_selection = '';

      if (CLICSHOPPING_APP_MONERIS_MO_LOGO) {
        $this->title_selection = HTML::image($CLICSHOPPING_Template->getDirectoryTemplateImages() . 'logos/payment/' . CLICSHOPPING_APP_MONERIS_MO_LOGO);
      } else {
        $this->title_selection = $this->title;
      }

      return array('title' => $this->title_selection);
    }

    public function process_button() {
      $CLICSHOPPING_Customer = Registry::get('Customer');
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_Currencies = Registry::get('Currencies');
      $CLICSHOPPING_Order = Registry::get('Order');

// customer email
      $Qcheck = $CLICSHOPPING_Db->prepare('select customers_email_address
                                           from :table_customers
                                           where customers_id = :customers_id
                                           limit 1
                                           ');
      $Qcheck->bindInt(':customers_id', $CLICSHOPPING_Customer->getID());
      $Qcheck->execute();

      $email = $Qcheck->value('customers_email_address');

      $total_amount = number_format($CLICSHOPPING_Order->info['total'] * $CLICSHOPPING_Currencies->getValue($CLICSHOPPING_Order->info['currency']), $CLICSHOPPING_Currencies->currencies[$CLICSHOPPING_Order->info['currency']]['decimal_places'], '.', '');

// free texte : a bigger reference, session context for the return on the merchant website
      $session_clicshopping = session_id();

      $process_button_string = HTML::hiddenField('ps_store_id', CLICSHOPPING_APP_MONERIS_MO_STORE_ID) .
                              HTML::hiddenField('hpp_key', CLICSHOPPING_APP_MONERIS_MO_API_TOKEN) .
                              HTML::hiddenField('charge_total', $total_amount) .
                              HTML::hiddenField('note', STORE_NAME) .
                              HTML::hiddenField('rvar1', $session_clicshopping) .
                              HTML::hiddenField('rvar2', HTTP::GetIpAddress()) .
                              HTML::hiddenField('lang', 'en_ca') .
                              HTML::hiddenField('eci','1' ) .
                              HTML::hiddenField(session_name(), session_id());

//  shipping
      if (is_numeric($_SESSION['sendto']) && ($_SESSION['sendto'] > 0)) {
        $process_button_string .= HTML::hiddenField('cust_id',(int)$CLICSHOPPING_Customer->getID() ) .
                                  HTML::hiddenField('ship_first_name', $CLICSHOPPING_Order->delivery['firstname']) .
                                  HTML::hiddenField('ship_last_name', $CLICSHOPPING_Order->delivery['lastname']) .
                                  HTML::hiddenField('ship_company_name', $CLICSHOPPING_Order->delivery['company']) .
                                  HTML::hiddenField('ship_address_one', $CLICSHOPPING_Order->delivery['street_address'] . ' ' . $CLICSHOPPING_Order->delivery['suburb'] ) .
                                  HTML::hiddenField('ship_city', $CLICSHOPPING_Order->delivery['city']) .
                                  HTML::hiddenField('ship_addr_state_or_province' ,$CLICSHOPPING_Order->delivery['state']) .
                                  HTML::hiddenField('ship_postal_code', $CLICSHOPPING_Order->delivery['postcode']) .
                                  HTML::hiddenField('ship_country', $CLICSHOPPING_Order->delivery['country']['title']) .
                                  HTML::hiddenField('ship_phone', $CLICSHOPPING_Order->delivery['telephone']) .
                                  HTML::hiddenField('email', $CLICSHOPPING_Order->shipping['email_address']);
      }
//  billing
      $process_button_string .= HTML::hiddenField('bill_first_name', $CLICSHOPPING_Order->billing['firstname']) .
                                HTML::hiddenField('bill_last_name', $CLICSHOPPING_Order->billing['lastname']) .
                                HTML::hiddenField('bill_company_name', $CLICSHOPPING_Order->billing['company']) .
                                HTML::hiddenField('bill_address_one', $CLICSHOPPING_Order->customer['street_address']. ' ' . $CLICSHOPPING_Order->billing['suburb'] ) .
                                HTML::hiddenField('bill_city', $CLICSHOPPING_Order->billing['city']) .
                                HTML::hiddenField('bill_addr_state_or_province' ,$CLICSHOPPING_Order->billing['state']) .
                                HTML::hiddenField('bill_postal_code', $CLICSHOPPING_Order->billing['postcode']) .
                                HTML::hiddenField('bill_country', $CLICSHOPPING_Order->customer['country']['title']) .
                                HTML::hiddenField('bill_phone', $CLICSHOPPING_Order->customer['telephone']) .
                                HTML::hiddenField('email', $email);


//---------------------------------------------------------------
// ----------------------- Test Payment -------------------------
//---------------------------------------------------------------

      if (CLICSHOPPING_APP_MONERIS_MO_PAYMENT_SERVER_SIMULATION == 'True' && CLICSHOPPING_APP_MONERIS_MO_PAYMENT_SERVER == 'Test') {
?>
<b>TEST MODE - DATA BANK FORM:</b>
<p><span class="name">API TOKEN Moneris</span> : <span class="value"><?php echo CLICSHOPPING_APP_MONERIS_MO_API_TOKEN;?></span></p>
<pre>
&lt;form <span class="name">action</span>="<span class="value"><?php echo $this->form_action_url ;?>"</span> method="post" id="PaymentRequest"&gt;

&lt;input type="hidden" name="<span class="name">Cust_id</span>"                  value="<span class="value"><?php echo (int)$CLICSHOPPING_Customer->getID();?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">ps_store_id</span>"              value="<span class="value"><?php echo CLICSHOPPING_APP_MONERIS_MO_STORE_ID;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">hpp_key Moneris</span>"          value="<span class="value"><?php echo CLICSHOPPING_APP_MONERIS_MO_API_TOKEN;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">Date stamp</span>"                     value="<span class="value"><?php echo date("Y/m/d");?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">Description</span>"               value="<span class="value"><?php echo STORE_NAME;?></span>" /&gt;

&lt;input type="hidden" name="<span class="name">lang</span>"                     value="<span class="value">ca-fr</span>" /&gt;
&lt;input type="hidden" name="<span class="name">eci</span>"                      value="<span class="value">1</span>" /&gt;
&lt;input type="hidden" name="<span class="name">session_clicshopping (rvar1) </span>"    value="<span class="value"><?php echo $session_clicshopping;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">session_clicshopping (rvar2) </span>"    value="<span class="value"><?php echo HTTP::GetIpAddress();?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">redirect_url</span>"             value="<span class="value"><?php echo CLICSHOPPING::link(null, 'Checkout&Process', true);?></span>" /&gt;

<span>Billing</span>
&lt;input type="hidden" name="<span class="name">charge_total</span>"             value="<span class="value"><?php echo $total_amount;?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">customer_id</span>"              value="<span class="value"><?php echo (int)$CLICSHOPPING_Customer->getID();?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">bill_first_name</span>"          value="<span class="value"><?php echo $CLICSHOPPING_Order->billing['lastname'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">bill_company</span>"             value="<span class="value"><?php echo $CLICSHOPPING_Order->billing['company'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">bill_state</span>"               value="<span class="value"><?php echo $CLICSHOPPING_Order->billing['state'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">bill_address_one</span>"         value="<span class="value"><?php echo $CLICSHOPPING_Order->customer['street_address']. ' ' . $CLICSHOPPING_Order->billing['suburb'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">bill_postcode</span>"            value="<span class="value"><?php echo $CLICSHOPPING_Order->billing['postcode'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">bill_company</span>"             value="<span class="value"><?php echo $CLICSHOPPING_Order->customer['country']['title'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">bill_telephone</span>"           value="<span class="value"><?php echo $CLICSHOPPING_Order->customer['telephone'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">bill_email</span>"               value="<span class="value"><?php echo $email;?></span>" /&gt;


<?php
  if (is_numeric($_SESSION['sendto']) && ($_SESSION['sendto'] > 0)) {
?>

<span>Shipping</span>
&lt;input type="hidden" name="<span class="name">ship_first_name</span>"          value="<span class="value"><?php echo $CLICSHOPPING_Order->delivery['lastname'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">ship_company</span>"             value="<span class="value"><?php echo $CLICSHOPPING_Order->delivery['company'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">ship_state</span>"               value="<span class="value"><?php echo $CLICSHOPPING_Order->delivery['state'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">ship_address_one</span>"         value="<span class="value"><?php echo $CLICSHOPPING_Order->delivery['street_address']. ' ' . $CLICSHOPPING_Order->delivery['suburb'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">ship_postcode</span>"            value="<span class="value"><?php echo $CLICSHOPPING_Order->delivery['postcode'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">ship_company</span>"             value="<span class="value"><?php echo $CLICSHOPPING_Order->delivery['country']['title'];?></span>" /&gt;
&lt;input type="hidden" name="<span class="name">ship_telephone</span>"           value="<span class="value"><?php echo $CLICSHOPPING_Order->delivery['telephone'];?></span>" /&gt;
<?php
  }
?>
&lt;/form&gt;
</pre>

<?php
      }

      return $process_button_string;
    }

    public function before_process() {
      global $_SERVER;

      $error = false;

      if ($error !== false) {
        CLICSHOPPING::redirect(null, 'Checkout&Billing&payment_error=' . $this->code . '&error=' . $error, true, false);
      }

       return false;
    }

    public function after_process() {
      return false;
    }

    public function get_error() {

      if (isset($_GET['ErrMsg']) && (strlen($_GET['ErrMsg']) > 0)) {
        $error = stripslashes(urldecode($_GET['ErrMsg']));
      } elseif (isset($_GET['error']) && (strlen($_GET['error']) > 0)) {
        $error = stripslashes(urldecode($_GET['error']));
      } else {
        $error = CLICSHOPPING_APP_MONERIS_MO_TEXT_ERROR;
      }

      $error = array('title' => CLICSHOPPING_APP_MONERIS_MO_TEXT_ERROR,
                     'error' => $error);
      return false;
    }

    public function check() {
      return defined('CLICSHOPPING_APP_MONERIS_MO_STATUS') && (trim(CLICSHOPPING_APP_MONERIS_MO_STATUS) != '');
    }

    public function install() {
      $this->app->redirect('Configure&Install&module=Moneris');
    }

    public function remove() {
      $this->app->redirect('Configure&Uninstall&module=Moneris');
    }

    public function keys() {
      return array('CLICSHOPPING_APP_MONERIS_MO_SORT_ORDER');
    }

// ***************************************************************************
//
//      Moneris eSELECTplus Additional Functions
//
// ***************************************************************************

// format prices without currency formatting
    public function format_raw($number, $currency_code = '', $currency_value = '') {
      $CLICSHOPPING_Currencies = Registry::get('Currencies');

      if (empty($currency_code) || !$this->is_set($currency_code)) {
        $currency_code = $_SESSION['currency'];
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $CLICSHOPPING_Currencies->currencies[$currency_code]['value'];
      }

      return number_format(round($number * $currency_value, $CLICSHOPPING_Currencies->currencies[$currency_code]['decimal_places']), $CLICSHOPPING_Currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }
  }