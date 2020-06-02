<?php

/**
* Plugin Name:  MAAA Tables
* Plugin URI:   http://meggangreen.com
* Description:  update maaa tables
* Version:      2.0.0
* Updated:      2020-05-31
* Author:       Meggan Green
* Author URI:   http://meggangreen.com
* License:      GNU GPL2
*/


/**
 * Returns the left-of- and right-of-decimal values.
 * 
 * Given the total amount and the divisor (1 if total, num of days if average),
 * calculates the values for the left and right sides of the decimal. These are
 * divided so as to permit easy decimal alignment.
 * 
 * @since 0.0.0
 * @since 2.0.0 Cleaned up as part of major refactor
 * 
 * @param float $dollars Total dollar amount
 * @param float $days Number of days to divide by (1 for total)
 * 
 * @return array {
 *     @type string $dollars_left String of left-of-decimal value
 *     @type string $dollars_right String of right-of-decimal value
 * }
 */
function get_left_right_values($dollars, $days) {
  if ( $days <= 0 ) { $days = 1; }

  $dollars_average = $dollars / $days;
  $dollars_left = floor($dollars_average);
  $dollars_right = ($dollars_average - $dollars_left) * 100;

  return array(number_format($dollars_left), number_format($dollars_right));
}


/**
 * Makes option tags for dropdown selectors.
 * 
 * Makes an option tag for each option in a given table and column. If the
 * option matches the user selection, the option tag includes the "selected"
 * attribute. Returns all the option tags as one string.
 * 
 * @since 0.0.0
 * @since 2.0.0 Cleaned up as part of major refactor
 * 
 * @param string $table Name of table holding the data
 * @param string $column Name of column holding the data
 * @param string $selection Selected value
 * 
 * @return string $options String of option tags
 */
function make_options($table, $column, $selection) {
  global $wpdb;

  $all_values = $wpdb->get_col( "SELECT " . $column . " FROM " . $wpdb->prefix . "maaa_" . $table . " ORDER BY " . $column . " ASC" );
  
  $options = '';
  foreach ($all_values as $value) {
    if ($value == $selection) {
      $options = $options .
                 '<option selected value="' . esc_attr( $value ) . '">' .
                 esc_html( $value ) .
                 '</option>';
    } else {
      $options = $options .
                 '<option value="' . esc_attr( $value ) . '">' .
                 esc_html( $value ) .
                 '</option>';
    }
  }

  return $options;
}


/**
 * The Dashboard widget data entry form.
 * 
 * The HTML form, blank (default) for entering new data or populated for editing
 * or deleting a table row from a selected table. Only sub-classes should be used.
 * 
 * @since 2.0.0
 * 
 * @var string $table Table name  #TODO is necessary to keep?
 * @var string $name Form name
 * @var string $nonce String passed to wp_nonce_field()
 * @var mutable string OR array $values Array of table row values or "none"  #TODO pass empty array instead of string "none"
 * @var integer $count Number of values in array $dfields  #TODO deprecate
 * @var string $delete Sets "Delete" button to property to "enabled" or "disabled"  #TODO change to bool
 * @var string $content Entire HTML form
 * @var array $dfields Array of required table columns
 * @var string $tfields Stringified list of all table columns, except ID
 * @var string $tftypes Stringified list of data types corresponding to $tfields
 */
abstract class EntryForm {
  protected $table;
  protected $name;
  protected $nonce;
  protected $values;
  protected $count;
  protected $delete;
  public $content;
  public $dfields;
  public $tfields;
  public $tftypes;
  
  function __construct($table, $values, $count) {
    $this->table = esc_attr($table);
    $this->name = "f_" . $this->table;
    $this->nonce = "maaa_" . $this->table . "_nonce";
    $this->count = $count;
    $this->values = $this->_clean_values($values);
    $this -> _set_delete();
  }

  /**
   * Creates $values array OR sanitizes $values using esc_attr().
   * 
   * #TODO accept empty array instead of string
   * 
   * @since 2.0.0 Moved into separate function as part of major refactor
   * @param mutable string OR array
   * @return array
   */
  protected function _clean_values($values) {
    if ( $values == "none" ) {  // Starting with a blank form
      unset($values);
      return array_map(function($v) {return "";}, range(0, $this->count));
    } else {
      return array_map(function($v) {return esc_attr($v);}, $values);
    }
  }
  
  /**
   * Builds HTML form header.
   * 
   * @since 2.0.0 Moved into separate function as part of major refactor
   * @return string
   */
  protected function _get_header() {
    return '<form method="post" action="" name="' . $this->name . '">'
           . wp_nonce_field($this->nonce) . 
           '  <input type="hidden" name="val_tchoice" value="' . $this->table . '">
              <input type="hidden" name="val_idedit" value="' . $this->values[0] . '">';
  }
  
  /**
   * Sets the dfields, tfields, and tftypes properties.
   * 
   * Sets the field properties to values hard-coded in each sub-class.
   * 
   * @since 2.0.0
   */
  abstract protected function _set_fields();
  
  /**
   * Builds HTML form body.
   * 
   * Uses hard-coded HTML tags and the values property to build the form body.
   * 
   * @since 2.0.0
   * @return string
   */
  abstract protected function _get_body();
  
  /**
   * Sets delete property.
   * 
   * Called by __construct(); sets the delete property.
   * #TODO deprecate
   * 
   * @since 2.0.0 Moved into separate function as part of major refactor
   */
  protected function _set_delete() {
    $this->delete = ($this->values[0] == "" ? "disabled" : "enabled");
  }
  
  /**
   * Builds HTML form footer.
   * 
   * @since 2.0.0 Moved into separate function as part of major refactor
   * @return string
   */
  protected function _get_footer() {
    return '  <input type="submit" value="Submit" name="submit_tupdate"> 
              &nbsp; &nbsp; &nbsp;
              <input type="submit" value="Delete" name="delete_tupdate" ' . $this->delete . '>
            </form>';
  }

  /**
   * Builds entire HTML form and sets content property.
   * 
   * @since 2.0.0
   */
  protected function _set_content() {
    $this->content = $this->_get_header() . $this->_get_body() . $this->_get_footer();
  }

}

/**
 * The Dashboard widget AccomTrans data entry form.
 * 
 * @since 2.0.0
 * 
 * @see EntryForm
 */
class AccomTrans extends EntryForm {
  function __construct($table, $values, $count) {
    // PHP docs indicate I shouldn't need to pass in the params above, but I do
    // https://www.php.net/manual/en/classobj.examples.php
    parent::__construct($table, $values, $count);
    $this -> _set_fields();
    $this -> _set_content();
  }
  
  protected function _set_fields() {
    $this->dfields = array("id", "country1", "country2", "start_in", "end_out","co_name", "notes", "conf_code");
    $this->tfields = "country1, country2, start_in, end_out, co_name, co_address, co_phone, co_contact, notes, conf_code, conf_date, conf_cancelled";
    $this->tftypes = "%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s";
  }

  protected function _get_body() {
    return 'Countries:<br>
            <input type="text" name="val_country1" value="' . $this->values[1] . '"> &nbsp; &nbsp; <input type="text" name="val_country2" value="' . $this->values[2] . '"><br>
            Timestamps: &nbsp; &nbsp; <small>yyyy-mm-dd hh:mm:ss</small><br>
            <input type="text" name="val_startin" value="' . $this->values[3] . '"> &nbsp; &nbsp; <input type="text" name="val_endout" value="' . $this->values[4] . '"><br>
            Company Information:<br>
              &nbsp; &nbsp; Name: &nbsp; &nbsp; <input type="text" name="val_coname" value="' . $this->values[5] . '"><br>
              &nbsp; &nbsp; Address: &nbsp; &nbsp; <input type="text" name="val_coaddress" value="' . $this->values[6] . '"><br>
              &nbsp; &nbsp; Phone: &nbsp; &nbsp; <input type="text" name="val_cophone" value="' . $this->values[7] . '"><br>
              &nbsp; &nbsp; Contact: &nbsp; &nbsp; <input type="text" name="val_cocontact" value="' . $this->values[8] . '"><br>
            Notes:<br><input type="text" name="val_notes" value="' . $this->values[9] . '"><br>
            Confirmation Code:<br>
            <input type="text" name="val_confcode" value="' . $this->values[10] . '"><br>
            Confirmation Date:<br>
            <small>yyyy-mm-dd hh:mm:ss</small><br>
            <input type="text" name="val_confdate" value="' . $this->values[11] . '"><br>
            Cancellation Date:<br>
            <small>yyyy-mm-dd hh:mm:ss</small><br>
            <input type="text" name="val_confcancelled" value="' . $this->values[12] . '"><br>';
  }
}

/**
 * The Dashboard widget Budget data entry form.
 * 
 * The Budget form has one unique property and one function, _set_radio_field().
 * 
 * @since 2.0.0
 * @var string $maaa_radio Section of the form body
 * 
 * @see EntryForm
 */
class Budget extends EntryForm {
  protected $maaa_radio;

  function __construct($table, $values, $count) {
    parent::__construct($table, $values, $count);
    $this -> _set_fields();
    $this -> _set_content();
  }
  
  protected function _set_fields() {
    $this->dfields = array("id", "type", "descrip", "price", "detail");
    $this->tfields = "type, descrip, price, detail";
    $this->tftypes = "%s, %s, %f, %s";
  }
  
  /**
   * Builds the first section of the form body.
   * 
   * The first input field of the Budget form body is a set of radio buttons
   * describing whether the data is the budgeted or actual data. Called by
   * _get_body().
   * 
   * @since 2.0.0
   */
  protected function _set_radio_field() {
    if ($this->this->values[1] == "bud") {
      $this->maaa_radio = 'Type:<br><input type="radio" name="val_type" value="bud" checked>Budgeted&nbsp;&nbsp;<input type="radio" name="val_type" value="act">Actual<br>';
    } else {
      $this->maaa_radio = 'Type:<br><input type="radio" name="val_type" value="bud">Budgeted&nbsp;&nbsp;<input type="radio" name="val_type" value="act" checked>Actual<br>';
    }
  }

  protected function _get_body() {
    $this -> _set_radio_field();
    return $this->maaa_radio . '
           Description:<br><input type="text" name="val_descrip" value="' . $this->values[2] . '"><br>
           Price:<br><input type="text" name="val_price" value="' . $this->values[3] . '"><br>
           Detail:<br><input type="text" name="val_detail" value="' . $this->values[4] . '"><br>';
  }
}

/**
 * The Dashboard widget Category data entry form.
 * 
 * @since 2.0.0
 * 
 * @see EntryForm
 */
class Category extends EntryForm {
  function __construct($table, $values, $count) {
    parent::__construct($table, $values, $count);
    $this -> _set_fields();
    $this -> _set_content();
  }
  
  protected function _set_fields() {
    $this->dfields = array("id", "category");
    $this->tfields = "category";
    $this->tftypes = "%s";
  }
  
  protected function _get_body() {
    return 'Category:<br>
            <input type="text" name="val_cat" value="' . $this->values[1] . '"><br>';
  }
}

/**
 * The Dashboard widget Country data entry form.
 * 
 * @since 2.0.0
 * 
 * @see EntryForm
 */
class Country extends EntryForm {
  function __construct($table, $values, $count) {
    parent::__construct($table, $values, $count);
    $this -> _set_fields();
    $this -> _set_content();
  }
  
  protected function _set_fields() {
    $this->dfields = array("id", "country", "visa_entry", "visa_exit", "visit_order", "approx_duration", "map_url");
    $this->tfields = "country, visa_entry, visa_exit, visa_notes, visit_order, approx_duration, curr_convert, curr_foreign";
    $this->tftypes = "%s, %f, %f, %s, %d, %d, %f, %f";
  }
  
  protected function _get_body() {
    return 'Country:<br>
            <input type="text" name="val_country" value="' . $this->values[1] . '"><br>
            Visa Entry Fee:<br><input type="text" name="val_visaentry" value="' . $this->values[2] . '"><br>
            Visa Exit Fee:<br><input type="text" name="val_visaexit" value="' . $this->values[3] . '"><br>
            Visa Notes:<br><input type="text" name="val_visanotes" value="' . $this->values[4] . '"><br>
            Visit Order:<br><input type="text" name="val_visitorder" value="' . $this->values[5] . '"><br>
            Approximate Duration:<br><input type="text" name="val_duration" value="' . $this->values[6] . '"><br>
            USD$1 Equals:<br><input type="text" name="val_currconvert" value="' . $this->values[7] . '"><br>
            Foreign Amount Spent:<br><input type="text" name="val_currforeign" value="' . $this->values[8] . '"><br>
            Map URL:<br>' .  esc_url( $this->values[9] ) .'<br>Use PHP MyAdmin to update the Map URL.<br>';
  }
}

/**
 * The Dashboard widget Day data entry form.
 * 
 * @since 2.0.0
 * 
 * @see EntryForm
 */
class Day extends EntryForm {
  function __construct($table, $values, $count) {
    parent::__construct($table, $values, $count);
    $this -> _set_fields();
    $this -> _set_content();
  }
  
  protected function _set_fields() {
    $this->dfields = array("id", "country", "entry_ts", "exit_ts", "days");
    $this->tfields = "country, entry_ts, exit_ts, days";
    $this->tftypes = "%s, %s, %s, %f";
  }

  protected function _get_body() {
    $dropdown = make_options("countries", "country", $this->values[1]);
    return 'Country:<br>
            <select name="val_country">' . $dropdown . '</select><br>
            Entry Timestamp:<br><small>yyyy-mm-dd hh:mm:ss</small><br>
            <input type="text" name="val_entry" value="' . $this->values[2] . '"><br>
            Exit Timestamp:<br><small>yyyy-mm-dd hh:mm:ss</small><br>
            <input type="text" name="val_exit" value="' . $this->values[3] . '"><br>';
  }
}

/**
 * The Dashboard widget Expense data entry form.
 * 
 * @since 2.0.0
 * 
 * @see EntryForm
 */
class Expense extends EntryForm {
  function __construct($table, $values, $count) {
    parent::__construct($table, $values, $count);
    $this -> _set_fields();
    $this -> _set_content();
  }
  
  protected function _set_fields() {
    $this->dfields = array("id", "spenddate", "country", "category", "detail", "price");
    $this->tfields = "spenddate, country, category, detail, price, units, ppu";
    $this->tftypes = "%s, %s, %s, %s, %f, %f, %f";
  }

  protected function _get_body() {
    $countries = make_options("countries", "country", $this->values[2]);
    $categories = make_options("categories", "category", $this->values[3]);
    return 'Date:<br><small>yyyy-mm-dd</small><br>
            <input type="text" name="val_date" value="' . $this->values[1] . '"><br>
            Country:<br><select name="val_country">' . $countries . '</select><br>
            Category:<br><select name="val_category">' . $categories . '</select><br>
            Detail:<br><input type="text" name="val_detail" value="' . $this->values[4] . '"><br>
            Price:<br><input type="text" name="val_price" value="' . $this->values[5] . '"><br>
            Num of Units:<br><input type="text" name="val_units" value="' . $this->values[6] . '"><br>';
  }
}


/**
 * Makes entry input form for Dashboard widget.
 * 
 * Builds the appropriate form based on the selected table.
 * 
 * @since 0.0.0
 * @since 2.0.0 Cleaned up as part of major refactor
 * 
 * @param string $table Table name
 * @param mutable string OR array $values Array of values or "none"  #TODO change to array only
 * 
 * @return array {  #TODO make return object instead
 *     @type string $form->content HTML form
 *     @type array $form->dfields Array of strings of required column names
 *     @type string $form->tfields Stringified list of all column names, except ID
 *     @type string $form->tftypes Stringified list of data types corresponding to tfields
 * }
 */ 
function maaa_make_dataform_safe($table, $values, $count) {
  
  switch ($table) {
    case "accomtrans":
      $form = new AccomTrans($table, $values, $count);
      break;
    case "budget":
      $form = new Budget($table, $values, $count);
      break;
    case "categories":
      $form = new Category($table, $values, $count);
      break;
    case "countries":
      $form = new Country($table, $values, $count);
      break;
    case "days":
      $form = new Day($table, $values, $count);
      break;
    case "expenses":
      $form = new Expense($table, $values, $count);
      break;
    default:
      echo 'There was an error in "maaa_make_dataform_safe()"';
  }

  return array($form->content, $form->dfields, $form->tfields, $form->tftypes);
}


/**
 * Makes the SQL query for the Dashboard widget's table.
 * 
 * @since 0.0.0
 * @since 2.0.0 Broken out as separate function
 * 
 * @param string $table Selected table name
 * @param array $fields Array of selected column names  # TODO can get all cols and not print some instead?
 * @return string
 */
function make_query($table, $fields) {
  global $wpdb;
  $db_table = $wpdb->prefix . "maaa_" . $table;

  $select = "SELECT " . implode(", ", $fields);
  $from = " FROM $db_table";
  $where = "";
  $orderby = " ORDER BY id DESC";
  $limit = "";
  
  switch ($table) {
    case "accomtrans":
      $where = " WHERE conf_cancelled = '0000-00-00 00:00:00'";
      $orderby = " ORDER BY start_in ASC";
      break;
    case "countries":
      $orderby = " ORDER BY country ASC";
      break;
    case "categories":
      $orderby = " ORDER BY category ASC";
      break;
    case "days" || "expenses":
      $limit = " LIMIT 0 , 60";
      break;
    default:
      $select = "";
      $from = "";
      $where = "";
      $orderby = "";
      $limit = "";
  }

  return $select . $from . $where . $orderby . $limit;
}


/**
 * Gets DB rows.
 * 
 * @since 0.0.0
 * @since 2.0.0 Broken out and refactored
 * 
 * @param string $table Table name
 * @param array $fields Array of selected column names  # TODO can get all cols and not print some instead?
 * @return array Array of row objects
 */
function get_data($table, $fields) {
  global $wpdb;
  
  if ( $table == "none" ) {
    return NULL;
  }
  
  $query = make_query($table, $fields);
  
  return $wpdb->get_results($query);
}


/**
 * Makes the HTML header for the Dashboard widget's table.
 * 
 * @since 0.0.0
 * @since 2.0.0 Moved into separate function during refactor
 * 
 * @param string $table Table name
 * @return string 
 */
function make_html_header($table) {
  $table = ucfirst($table);
  
  switch ($table) {
    case "Accomtrans":
      $header = "All active submissions to the $table table:";
      break;
    case "Days" || "Expenses":
      $header = "Sixty most recent submissions to the $table table:";
      break;
    default:
      $header = "All submissions to the $table table:";
  }

  return '<hr><center><b>' . esc_html($header) . '</b></center>';
}


/**
 * Makes the HTML header row for the Dashboard widget's table.
 * 
 * @since 0.0.0
 * @since 2.0.0 Moved to separate function
 * 
 * @param array $cols Array of selected column names
 * @param integer $width Percentage of table for all but first cell in row
 * @return string
 */
function make_html_row_heads($cols, $width) {
  $width = esc_attr($width);
  
  $pre = '<tr>';
  $post = '</tr>';

  $cols[0] = '<th width="2%">' . esc_html(strtoupper($cols[0])) . '</th>';
  for ($i=1; $i < count($cols); $i++) { 
    $cols[$i] = '<th width="' . $width . '%">' 
               . esc_html(strtoupper($cols[$i])) .
               '</th>';
  }

  return $pre . implode($cols) . $post;
}


/**
 * Makes one HTML row for the Dashboard widget's table.
 * 
 * @since 0.0.0
 * @since 2.0.0 Moved into separate function
 * 
 * @param array $fields An array of DB row object properties (ie column names)
 * @param array $row A DB row object
 * @param integer $width Percentage of table for all but first cell in row
 * @return string
 */
function make_one_html_row($fields, $row, $width) {
  $width = esc_attr($width);
  
  $pre = '<tr>';
  $post = '</tr>';

  $cells = array();

  $row->id = esc_attr($row->id);
  $html = '<td style="vertical-align:top; width="2%"><center>' .
          '<input type="submit" 
                  name="id_' . $row->id . '" 
                  value="' . $row->id . '">' .
          '</center></td>';
  array_push($cells, $html);

  for ($i=1; $i < count($fields); $i++) {
    $f = $fields[$i];
    $html = '<td style="vertical-align:top; width="' . $width . '%"><center>'
            . esc_html($row->$f) .
            '</center></td>';
    array_push($cells, $html);
  }

  return $pre . implode($cells) . $post;
}


/**
 * Makes the HTML rows for the Dashboard widget's table.
 * 
 * @since 0.0.0
 * @since 2.0.0 Moved into separate function
 * 
 * @param array $fields Array of column names
 * @param array $rows Table row objects from SQL query
 * @param integer $width Percentage of table for all but first cell in row
 * @return string
 */ 
function make_html_rows($fields, $rows, $width) {
  for ($i=0; $i < count($rows); $i++) { 
    $rows[$i] = make_one_html_row($fields, $rows[$i], $width);
  }  

  return implode($rows);
}


/**
 * Makes the Dashboard widget's table.
 * 
 * Driver function to make the HTML table in the Dashboard widget.
 * 
 * @since 0.0.0
 * @since 2.0.0 Underwent significant refactor
 * 
 * @param string $table Table name
 * @param array $fields Array of selected column names  # TODO can get all cols and not print some instead?
 * @return string
 */
function maaa_make_output_table_safe($table, $fields) {
  $width = floor(98 / (count($fields) - 1));  // width of the table cells
  
  $rows = get_data($table, $fields);

  $html_header = make_html_header($table);
  $html_row_heads = make_html_row_heads($fields, $width);
  $html_rows = make_html_rows($fields, $rows, $width);
  
  $table = esc_attr($table);

  return $html_header .
         '<table width="100%" style="border-collapse:collapse;">
           <form method="post" action="" name="ids">'
           . wp_nonce_field('maaa_editid_nonce') . 
           '<input type="hidden" name="val_edittable" value="' . $table . '">'
           . $html_row_heads
           . $html_rows .
           '</form>
         </table>';
}


//////////////////////////////////////////////////////INSTALL
//Create the tables in the WP database // this s*** dont work -- moved to bottom for ease of reading
global $log_db_version;
$log_db_version = "0.1";
function maaa_tables_install () {

}

//Registration hook to create the tables when the plugin is activated
register_activation_hook(__FILE__, 'maaa_tables_install');

//////////////////////////////////////////////////////ADMIN
// Create the function to output the contents of the admin dashboard widget
function maaa_display_forms_widget() {
  global $wpdb;
  $wpdb->show_errors();

  //Define input forms -- General
  if (isset($_POST['submit_tchoice'])) {
    $maaa_tchoice = $_POST['tablechoice'];
  } else if (isset($_POST['val_edittable'])) {
    $maaa_tchoice = $_POST['val_edittable'];
  } else if (isset($_POST['submit_tupdate']) || isset($_POST['delete_tupdate'])) {
    $maaa_tchoice = $_POST['val_tchoice'];
  } // end if

  //Choose table info to display
  switch ($maaa_tchoice) {
    case "none":
      $maaa_output_data_safe = array('Please select a table from the list.');
      break;
    case "accomtrans":
      $maaa_output_data_safe = maaa_make_dataform_safe($maaa_tchoice, "none", 13);
      break;
    case "budget":
      $maaa_output_data_safe = maaa_make_dataform_safe($maaa_tchoice, "none", 5);
      break;
    case "categories":
      $maaa_output_data_safe = maaa_make_dataform_safe($maaa_tchoice, "none", 2);
      break;
    case "countries":
      $maaa_output_data_safe = maaa_make_dataform_safe($maaa_tchoice, "none", 10);
      break;
    case "days":
      $maaa_output_data_safe = maaa_make_dataform_safe($maaa_tchoice, "none", 5);
      break;
    case "expenses":
      $maaa_output_data_safe = maaa_make_dataform_safe($maaa_tchoice, "none", 8);
      break;
  } //end switch

  //Display table selection form
  $maaa_tableoptions = array(
    "none",
    "accomtrans",
    "budget",
    "categories",
    "countries",
    "days",
    "expenses"
  );
  foreach ($maaa_tableoptions as $maaa_toption) {
    if ($maaa_toption == "none") {
      $maaa_toptionstr_safe = $maaa_toptionstr_safe .
                         '<option value="' .
                         esc_attr( $maaa_toption ) .
                         '">(select table)</option>';
    } else if ($maaa_toption == $maaa_tchoice) {
      $maaa_toptionstr_safe = $maaa_toptionstr_safe .
                         '<option selected value="' .
                         esc_attr( $maaa_toption ) .
                         '">' .
                         esc_html( ucfirst($maaa_toption) ) .
                         '</option>';
    } else {
      $maaa_toptionstr_safe = $maaa_toptionstr_safe .
                         '<option value="' .
                         esc_attr( $maaa_toption ) . '">' .
                         esc_html( ucfirst($maaa_toption) ) .
                         '</option>';
    } //end if
  } //end for

  //Load subforms to update table
  $maaa_admin_table_title_safe = esc_html( ucfirst($maaa_tchoice) );
  $maaa_admin_table_qresult_safe = '';

  if (isset($_POST['submit_tchoice'])) { //Table choice is submitted
    check_admin_referer('maaa_choosetable_nonce'); //check nonces
    $maaa_table = $wpdb->prefix . "maaa_" . $maaa_tchoice;

  } else if (isset($_POST['val_edittable'])) { //Table row to update is submitted
    check_admin_referer('maaa_editid_nonce'); //check nonces
    $maaa_table = $wpdb->prefix . "maaa_" . $maaa_tchoice;
    $maaa_updateid_post = implode(",,", $_POST);
    $maaa_updateid_loc = strrpos($maaa_updateid_post,",,");
    $maaa_updateid = substr($maaa_updateid_post,$maaa_updateid_loc+2);
    $maaa_idvals = $wpdb->get_row( "SELECT * FROM $maaa_table WHERE id = $maaa_updateid", ARRAY_N ); //$maaa_idvals[0]
    $maaa_output_data_safe = maaa_make_dataform_safe($maaa_tchoice, $maaa_idvals, count($maaa_idvals));
    $maaa_admin_table_title_safe = $maaa_admin_table_title_safe . '&nbsp;' . esc_html( $maaa_updateid );

  } else if (isset($_POST['submit_tupdate']) || isset($_POST['delete_tupdate'])) { //Table row entries are submitted
    $maaa_table = $wpdb->prefix . "maaa_" . $maaa_tchoice;
    if (isset($_POST[val_idedit])) {
      $maaa_val_id = $_POST[val_idedit];
    } //end if
    switch ($maaa_tchoice) {
      case "accomtrans":
        check_admin_referer('maaa_accomtrans_nonce');
        $maaa_val_country1 = filter_var($_POST['val_country1'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_country2 = filter_var($_POST['val_country2'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_startin = filter_var($_POST['val_startin'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_endout = filter_var($_POST['val_endout'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_coname = filter_var($_POST['val_coname'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_coaddress = filter_var($_POST['val_coaddress'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_cophone = filter_var($_POST['val_cophone'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_cocontact = filter_var($_POST['val_cocontact'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_notes = filter_var($_POST['val_notes'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_confcode = filter_var($_POST['val_confcode'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_confdate = filter_var($_POST['val_confdate'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_confcancelled = filter_var($_POST['val_confcancelled'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_valstr = array($maaa_val_country1,
                             $maaa_val_country2,
                             $maaa_val_startin,
                             $maaa_val_endout,
                             $maaa_val_coname,
                             $maaa_val_coaddress,
                             $maaa_val_cophone,
                             $maaa_val_cocontact,
                             $maaa_val_notes,
                             $maaa_val_confcode,
                             $maaa_val_confdate,
                             $maaa_val_confcancelled);
        break;
      case "budget":
        check_admin_referer('maaa_budget_nonce');
        $maaa_val_type = filter_var($_POST['val_type'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_descrip = filter_var($_POST['val_descrip'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_price = filter_var($_POST['val_price'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_detail = filter_var($_POST['val_detail'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_valstr = array($maaa_val_type,
                             $maaa_val_descrip,
                             $maaa_val_price,
                             $maaa_val_detail);
        break;
      case "categories":
        check_admin_referer('maaa_categories_nonce');
        $maaa_val_category = filter_var($_POST['val_cat'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_valstr = array($maaa_val_category);
        break;
      case "countries":
        check_admin_referer('maaa_countries_nonce');
        $maaa_val_country = filter_var($_POST['val_country'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_visaentry = filter_var($_POST['val_visaentry'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_visaexit = filter_var($_POST['val_visaexit'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_visanotes = filter_var($_POST['val_visanotes'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_visitorder = filter_var($_POST['val_visitorder'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_duration = filter_var($_POST['val_duration'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_currconvert = filter_var($_POST['val_currconvert'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_currforeign = filter_var($_POST['val_currforeign'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_valstr = array($maaa_val_country,
                             $maaa_val_visaentry,
                             $maaa_val_visaexit,
                             $maaa_val_visanotes,
                             $maaa_val_visitorder,
                             $maaa_val_duration,
                             $maaa_val_currconvert,
                             $maaa_val_currforeign);
        break;
      case "days":
        check_admin_referer('maaa_days_nonce');
        $maaa_val_country = filter_var($_POST['val_country'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_entry = filter_var($_POST['val_entry'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_exit = filter_var($_POST['val_exit'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_days = maaa_date_diff($maaa_val_entry,$maaa_val_exit,0)[1];
        $maaa_valstr = array($maaa_val_country,
                             $maaa_val_entry,
                             $maaa_val_exit,
                             $maaa_val_days);
        break;
      case "expenses":
        check_admin_referer('maaa_expenses_nonce');
        $maaa_val_date = filter_var($_POST['val_date'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_country = filter_var($_POST['val_country'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_category = filter_var($_POST['val_category'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_detail = filter_var($_POST['val_detail'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_price = filter_var($_POST['val_price'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_units = filter_var($_POST['val_units'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_ppu = $maaa_val_price / $maaa_val_units;
        $maaa_valstr = array($maaa_val_date,
                             $maaa_val_country,
                             $maaa_val_category,
                             $maaa_val_detail,
                             $maaa_val_price,
                             $maaa_val_units,
                             $maaa_val_ppu);
        break;
      default:
        $maaa_output_data_safe = array('<center>Not so much.</center>');
    } // end switch
    //Execute query and display result
    if (isset($maaa_val_id) && $maaa_val_id !="" && isset($_POST['submit_tupdate'])) {
      $maaa_output_data_safe[2] = explode(", ", $maaa_output_data_safe[2]);
      $maaa_output_data_safe[3] = explode(", ", $maaa_output_data_safe[3]);
      for ($i=0; $i<count($maaa_output_data_safe[2]); $i++) {
        $maaa_setstr[$i] = $maaa_output_data_safe[2][$i] . ' = ' . $maaa_output_data_safe[3][$i];
      } //end for
      $maaa_setstr = implode(", ", $maaa_setstr);
      $maaa_sql = $wpdb->prepare( "UPDATE $maaa_table SET $maaa_setstr WHERE id = $maaa_val_id", $maaa_valstr );
    } else if (isset($maaa_val_id) && $maaa_val_id !="" && isset($_POST['delete_tupdate'])) {
      $maaa_sql = $wpdb->prepare( "DELETE FROM $maaa_table WHERE id = $maaa_val_id LIMIT 1", $maaa_valstr );
    } else {
      $maaa_sql = $wpdb->prepare( "INSERT INTO $maaa_table ( $maaa_output_data_safe[2] ) VALUES ( $maaa_output_data_safe[3] )", $maaa_valstr );
    } //end if
    if ($wpdb->query($maaa_sql) === FALSE) {
      $maaa_admin_table_qresult_safe = '<b>Bummer!</b><br><br>' . esc_html( $wpdb->print_error() );
    } else {
      $maaa_admin_table_qresult_safe = '<b>Success!</b><br><br>';
    } //end if
    $maaa_admin_table_qresult_safe = $maaa_admin_table_qresult_safe . implode('<br>', $maaa_valstr);

  } else {
    echo '
    <table width="100%">
      <tr>
        <td width="50%">
          <form method="post" action="">';
          wp_nonce_field('maaa_choosetable_nonce');
          echo '
          <select name="tablechoice" class="postform">' . $maaa_toptionstr_safe . '</select>
          <input type="submit" value="Go" name="submit_tchoice">
          &nbsp; &nbsp; <a href="' . esc_url("http://www.meggangreen.com/maaa/stats") . '" target="_new">View Stats</a>
          </form>
        </td>
      </tr>
    </table>';
    return;
  } //end if

  $maaa_admin_table_safe = '
  <table width="100%">
    <tr>
      <td width="50%">
        <form method="post" action="">' .
        wp_nonce_field('maaa_choosetable_nonce') .
        '<select name="tablechoice" class="postform">' . $maaa_toptionstr_safe . '</select>
        <input type="submit" value="Go" name="submit_tchoice">
        &nbsp; &nbsp; <a href="' . esc_url("http://www.meggangreen.com/maaa/stats") . '" target="_new">View Stats</a>
        </form>
      </td>
      <td width="50%"><b>' . $maaa_admin_table_title_safe . '</b></td>
    </tr>
    <tr>
      <td valign="top">' . $maaa_admin_table_qresult_safe . '</td>
      <td valign="top">' . $maaa_output_data_safe[0] . '</td>
    </tr>
    <tr>
      <td colspan="2">
        ' . maaa_make_output_table_safe($maaa_tchoice, $maaa_output_data_safe[1]) . '
      </td>
    </tr>
  </table>';

  echo $maaa_admin_table_safe;

} //end function


// Create the widget action hook function which activates the admin dashboard widget
function maaa_add_table_widgets() {
  wp_add_dashboard_widget('maaa_admin_widget', 'MAAA Tables', 'maaa_display_forms_widget');
}


// Hook into the 'wp_dashboard_setup' action to register our other functions
add_action('wp_dashboard_setup', 'maaa_add_table_widgets');


//////////////////////////////////////////////////////SIDEBAR

//Create Sidebar Widgets
function maaa_sidebar_widget_init() {

  function maaa_allstats( $maaa_statargs_all ){
    extract($maaa_statargs_all);
    global $wpdb;
    $wpdb->show_errors();

    //Retrieve list options
    if (isset($_POST['submit_country'])) {
      check_admin_referer('maaa_choosecountry_nonce'); //check nonces
      $maaa_cchoice = $_POST['val_cchoice'];
    } else {
      $maaa_cchoice = "All Countries";
    }
    $maaa_countrystr = make_options("countries", "country", $maaa_cchoice);

    //Retrieve category sums
    $maaa_category = $wpdb->get_col( "SELECT category FROM " . $wpdb->prefix . "maaa_categories ORDER BY category ASC" );
    $maaa_table = $wpdb->prefix . "maaa_expenses";
    foreach ($maaa_category as $maaa_value) {
      if ($maaa_cchoice == "All Countries") {
        $maaa_catsum = $wpdb->get_var( "SELECT SUM(price) FROM $maaa_table WHERE category = '$maaa_value'" );
      } else {
        $maaa_catsum = $wpdb->get_var( "SELECT SUM(price) FROM $maaa_table WHERE category = '$maaa_value' AND country = '$maaa_cchoice'" );
      } //end if
      if (empty($maaa_catsum)) : $maaa_catsum = 0; endif;
      $maaa_catfins = $maaa_catfins . $maaa_value . "%" . $maaa_catsum . ",,";
      $maaa_cattotal = $maaa_cattotal + $maaa_catsum;
    } //end for
    $maaa_catfins = explode(",,", $maaa_catfins);

    //Retrieve number of days
    $maaa_table = $wpdb->prefix . "maaa_days";
    if ($maaa_cchoice == "All Countries") {
      $maaa_day1 = $wpdb->get_var( "SELECT entry_ts FROM $maaa_table ORDER BY entry_ts ASC LIMIT 0 , 1" );
      $maaa_day2 = $wpdb->get_var( "SELECT exit_ts FROM $maaa_table ORDER BY entry_ts DESC LIMIT 0 , 1" );
      if ($maaa_day2 == "0000-00-00 00:00:00") {
        $maaa_day2 = date('Y-m-d H:i:s', time()-21600);
        $maaa_currcountry = TRUE;
      } //endif
      $maaa_daytotal = maaa_date_diff($maaa_day1,$maaa_day2,0)[0];
      $maaa_daysum = maaa_date_diff($maaa_day1,$maaa_day2,0)[1];
    } else {
      $maaa_day1 = $wpdb->get_var( "SELECT entry_ts FROM $maaa_table WHERE country = '$maaa_cchoice' ORDER BY entry_ts ASC LIMIT 0 , 1" );
      $maaa_day2 = $wpdb->get_var( "SELECT exit_ts FROM $maaa_table WHERE country = '$maaa_cchoice' ORDER BY entry_ts DESC LIMIT 0 , 1" );
      $maaa_day3 = $wpdb->get_var( "SELECT entry_ts FROM $maaa_table WHERE country = '$maaa_cchoice' ORDER BY entry_ts DESC LIMIT 0 , 1" );
      $maaa_daysum = $wpdb->get_var( "SELECT SUM(days) FROM $maaa_table WHERE country = '$maaa_cchoice'" );
      if ($maaa_day2 == "0000-00-00 00:00:00") {
        $maaa_day2 = date('Y-m-d H:i:s', time()-21600);
        $maaa_currcountry = TRUE;
        $maaa_daysum = $maaa_daysum + 9999.999;
        $maaa_daytotal = maaa_date_diff($maaa_day3,$maaa_day2,$maaa_daysum)[0];
        $maaa_daysum = maaa_date_diff($maaa_day3,$maaa_day2,$maaa_daysum)[1];
      } else {
        $maaa_daytotal = maaa_date_diff(0,0,$maaa_daysum)[0];
        $maaa_daysum = maaa_date_diff(0,0,$maaa_daysum)[1];
      } //endif
    } //end if
    $maaa_day1 = date('Y-m-d H:i', strtotime($maaa_day1));
    $maaa_day2 = date('Y-m-d H:i', strtotime($maaa_day2));
    if ($maaa_day1 == "1970-01-01 00:00" || $maaa_day2 == "1970-01-01 00:00") {
      $maaa_daytotal = "Never Been";
      $maaa_day1 = "";
      $maaa_day2 = "";
      $maaa_daysum = 0;
    } //endif
    if ($maaa_currcountry == TRUE) { $maaa_day2 = "Currently Here"; }
    if ($maaa_daysum == 0) { $maaa_daysum = 1; }

    //Retrieve currency conversion
    $maaa_table =$wpdb->prefix . "maaa_countries";
    if ($maaa_cchoice == "All Countries") {
      $maaa_currency = "(N/A)";
    } else {
      $maaa_currency = $wpdb->get_var( "SELECT curr_convert FROM $maaa_table WHERE country = '$maaa_cchoice'" );
    } //end if

    //Display widget
    //$title = 'All Financial Stats'; //Set sidebar widget title
    //echo $before_widget; //Display "Before Widget" (defined by theme)
    //echo $before_title . $title . $after_title; //Display "Before" and "After Title" (defined by theme)

    //Widget body
    //end php for html
    ?>
    <form method="post" action="">
    <?php wp_nonce_field('maaa_choosecountry_nonce'); ?>
    <select name="val_cchoice" class="postform"><?php echo $maaa_countrystr; ?></select>
    <input type="submit" value="Show" name="submit_country" class="postform">
    </form>
    <hr>
    <table width="100%">
      <tr>
        <td width="25%">
          <table id="statsgen">
            <tr>
              <th width="100%"><b>Total Time</b></td>
            </tr>
            <tr>
              <td width="100%"><?php echo esc_html( $maaa_daytotal ); ?></td>
            </tr>
            <tr style="height:15px"></tr>
            <tr>
              <th width="100%"><b>First Entry</b></td>
            </tr>
            <tr>
              <td width="100%"><?php echo esc_html( $maaa_day1 ); ?></td>
            </tr>
            <tr style="height:15px"></tr>
            <tr>
              <th width="100%"><b>Last Exit</b></td>
            </tr>
            <tr>
              <td width="100%"><?php echo esc_html( $maaa_day2 ); ?></td>
            </tr>
            <tr style="height:15px"></tr>
            <tr>
              <th width="100%"><b>USD$1 Equals</b></td>
            </tr>
            <tr>
              <td width="100%"><?php echo esc_html( $maaa_currency ); ?></td>
            </tr>
          </table>
        </td>
        <td width="75%" style="border-left: double #1188ee">
          <table id="statsfin" style="padding-left: 35px">
            <tr>
              <th width="40%"><b><?php echo esc_html( $maaa_cchoice ); ?></b></td>
              <th width="30%" colspan="2" style="text-align:center"><b>Total USD$</b></td>
              <th width="30%" colspan="2" style="text-align:center"><b>Avg / Day</b></td>
            </tr>
            <tr>
              <td width="40%">All Categories</td>
              <td width="15%" style="text-align:right">$<?php echo esc_html( get_left_right_values( $maaa_cattotal, 1 )[0] ); ?></td>
              <td width="15%" style="text-align:left">.<?php echo esc_html( get_left_right_values( $maaa_cattotal, 1 )[1] ); ?></td>
              <td width="15%" style="text-align:right">$<?php echo esc_html( get_left_right_values( $maaa_cattotal, $maaa_daysum )[0] ); ?></td>
              <td width="15%" style="text-align:left">.<?php echo esc_html( get_left_right_values( $maaa_cattotal, $maaa_daysum )[1] ); ?></td>
            </tr>
            <?php
            foreach ($maaa_catfins as $maaa_catfin) {
              $maaa_catfin_vals = explode("%",$maaa_catfin);
              $maaa_catfin_c = $maaa_catfin_vals[0];
              $maaa_catfin_f = $maaa_catfin_vals[1];
              if ($maaa_catfin_c != "") { ?>
                <tr>
                  <td width="40%"><?php echo esc_html( $maaa_catfin_c ); ?></td>
                  <td width="15%" style="text-align:right">$<?php echo esc_html( get_left_right_values( $maaa_catfin_f, 1 )[0] ); ?></td>
                  <td width="15%" style="text-align:left">.<?php echo esc_html( get_left_right_values( $maaa_catfin_f, 1 )[1] ); ?></td>
                  <td width="15%" style="text-align:right">$<?php echo esc_html( get_left_right_values( $maaa_catfin_f, $maaa_daysum )[0] ); ?></td>
                  <td width="15%" style="text-align:left">.<?php echo esc_html( get_left_right_values( $maaa_catfin_f, $maaa_daysum )[1] ); ?></td>
                </tr>
              <?php } //end if
            } //end foreach
            ?>
          </table>
        </td>
      </tr>
    </table>

    <?php
    //echo $after_widget; //Display "After Widget" (defined by theme)
  } //end function


  // Hook into the 'register_sidebar_widget' action
  wp_register_sidebar_widget('maaa_allstats','Stats - All', 'maaa_allstats');

} //end function


// Hook into the 'plugins_loaded' action
add_action('plugins_loaded', 'maaa_sidebar_widget_init');

?>
