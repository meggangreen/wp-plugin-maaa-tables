<?php

/*
Plugin Name:  MAAA Tables
Plugin URI:   http://meggangreen.com
Description:  update maaa tables
Version:      1.0
Author:       Meggan Green
Author URI:   http://meggangreen.com
License:      GNU GPL2
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You can receive a copy of the GNU General Public License by
writing to the Free Software Foundation, Inc.,
51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//Output td code for widget
function maaa_calc_stat_vals($maaa_funcamt, $maaa_funcdiv) {
  if ( $maaa_funcdiv <= 0 ) { $maaa_funcdiv = 1; } // can't divide by 0, empty
  $amtRaw = round( $maaa_funcamt / $maaa_funcdiv, 2 );
  $amtDL = floor($amtRaw);
  $amtDR = ($amtRaw - $amtDL) * 100;
  $amtDLs = number_format($amtDL);
  $amtDRs = sprintf("%02d", number_format($amtDR));

  $maaa_stat_vals = array($amtDLs, $amtDRs);
    //$maaa_stat_vals = '<td width="15%" style="text-align:right">$' . $amtDLs . '</td><td width="15%" style="text-align:left">.' . $amtDRs . '</td>';

  return $maaa_stat_vals;
}

//Make option tags for lists
function maaa_list_options($maaa_listtable, $maaa_listcol, $maaa_listsel) {
  global $wpdb;
  $maaa_listarr = $wpdb->get_col( "SELECT " . $maaa_listcol . " FROM " . $wpdb->prefix . "maaa_" . $maaa_listtable . " ORDER BY " . $maaa_listcol . " ASC" );
  foreach ($maaa_listarr as $maaa_listopt) {
    if ($maaa_listopt == $maaa_listsel) {
      $maaa_liststr = $maaa_liststr . '<option selected value="' . $maaa_listopt . '">' . $maaa_listopt . '</option>';
    } else {
      $maaa_liststr = $maaa_liststr . '<option value="' . $maaa_listopt . '">' . $maaa_listopt . '</option>';
    } //end if
  } //end for
  return $maaa_liststr;
} //end function

//Make table entry input form
function maaa_dataform($maaa_tablechoice, $maaa_valsarray, $maaa_valscount) {
  $maaa_delete = "enabled";
  if ($maaa_valsarray == "none") {
    unset($maaa_valsarray);
    for ($i=0; $i<=$maaa_valscount-1; $i++) {
      $maaa_valsarray[$i] = "";
    } //end for
    $maaa_delete = "disabled";
  } //end if
  $maaa_radio = "";

  switch ($maaa_tablechoice) {
    case "accomtrans":
      $maaa_dataform = '<form method="post" action="" name="f_accomtrans">' . wp_nonce_field('maaa_accomtrans_nonce') . '
        <input type="hidden" name="val_tchoice" value="' . $maaa_tablechoice . '">
        <input type="hidden" name="val_idedit" value="' . $maaa_valsarray[0] . '">
        Countries:<br><input type="text" name="val_country1" value="' . $maaa_valsarray[1] . '"> &nbsp; &nbsp; <input type="text" name="val_country2" value="' . $maaa_valsarray[2] . '"><br>
        Timestamps: &nbsp; &nbsp; <small>yyyy-mm-dd hh:mm:ss</small><br><input type="text" name="val_startin" value="' . $maaa_valsarray[3] . '"> &nbsp; &nbsp; <input type="text" name="val_endout" value="' . $maaa_valsarray[4] . '"><br>
        Company Information:<br>
           &nbsp; &nbsp; Name: &nbsp; &nbsp; <input type="text" name="val_coname" value="' . $maaa_valsarray[5] . '"><br>
           &nbsp; &nbsp; Address: &nbsp; &nbsp; <input type="text" name="val_coaddress" value="' . $maaa_valsarray[6] . '"><br>
           &nbsp; &nbsp; Phone: &nbsp; &nbsp; <input type="text" name="val_cophone" value="' . $maaa_valsarray[7] . '"><br>
           &nbsp; &nbsp; Contact: &nbsp; &nbsp; <input type="text" name="val_cocontact" value="' . $maaa_valsarray[8] . '"><br>
        Notes:<br><input type="text" name="val_notes" value="' . $maaa_valsarray[9] . '"><br>
        Confirmation Code:<br><input type="text" name="val_confcode" value="' . $maaa_valsarray[10] . '"><br>
        Confirmation Date:<br><small>yyyy-mm-dd hh:mm:ss</small><br><input type="text" name="val_confdate" value="' . $maaa_valsarray[11] . '"><br>
        Cancellation Date:<br><small>yyyy-mm-dd hh:mm:ss</small><br><input type="text" name="val_confcancelled" value="' . $maaa_valsarray[12] . '"><br>
        <input type="submit" value="Submit" name="submit_tupdate"> &nbsp; &nbsp; &nbsp;
        <input type="submit" value="Delete" name="delete_tupdate" ' . $maaa_delete . '></form>';
      $maaa_dfields = array ("id", "country1", "country2", "start_in", "end_out", "co_name", "notes", "conf_code");
      $maaa_tfields = "country1, country2, start_in, end_out, co_name, co_address, co_phone, co_contact, notes, conf_code, conf_date, conf_cancelled";
      $maaa_tftypes = "%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s";
      break;
    case "budget":
      if ($maaa_valsarray[1] == "bud") {
        $maaa_radio = 'Type:<br><input type="radio" name="val_type" value="bud" checked>Budgeted&nbsp;&nbsp;<input type="radio" name="val_type" value="act">Actual<br>';
      } else {
        $maaa_radio = 'Type:<br><input type="radio" name="val_type" value="bud">Budgeted&nbsp;&nbsp;<input type="radio" name="val_type" value="act" checked>Actual<br>';
      } //end if
      $maaa_dataform = '<form method="post" action="" name="f_budget">' . wp_nonce_field('maaa_budget_nonce') . '
        <input type="hidden" name="val_tchoice" value="' . $maaa_tablechoice . '">
        <input type="hidden" name="val_idedit" value="' . $maaa_valsarray[0] . '">
        ' . $maaa_radio . '
        Description:<br><input type="text" name="val_descrip" value="' . $maaa_valsarray[2] . '"><br>
        Price:<br><input type="text" name="val_price" value="' . $maaa_valsarray[3] . '"><br>
        Detail:<br><input type="text" name="val_detail" value="' . $maaa_valsarray[4] . '"><br>
        <input type="submit" value="Submit" name="submit_tupdate"> &nbsp; &nbsp; &nbsp;
        <input type="submit" value="Delete" name="delete_tupdate" ' . $maaa_delete . '></form>';
      $maaa_dfields = array ("id", "type", "descrip", "price", "detail");
      $maaa_tfields = "type, descrip, price, detail";
      $maaa_tftypes = "%s, %s, %f, %s";
      break;
    case "categories":
      $maaa_dataform = '<form method="post" action="" name="f_categories">' . wp_nonce_field('maaa_categories_nonce') . '
        <input type="hidden" name="val_tchoice" value="' . $maaa_tablechoice . '">
        <input type="hidden" name="val_idedit" value="' . $maaa_valsarray[0] . '">
        Category:<br><input type="text" name="val_cat" value="' . $maaa_valsarray[1] . '"><br>
        <input type="submit" value="Submit" name="submit_tupdate"> &nbsp; &nbsp; &nbsp;
        <input type="submit" value="Delete" name="delete_tupdate" ' . $maaa_delete . '>
        </form>';
      $maaa_dfields = array ("id", "category");
      $maaa_tfields = "category";
      $maaa_tftypes = "%s";
      break;
    case "countries":
      $maaa_dataform = '<form method="post" action="" name="f_countries">' . wp_nonce_field('maaa_countries_nonce') . '
        <input type="hidden" name="val_tchoice" value="' . $maaa_tablechoice . '">
        <input type="hidden" name="val_idedit" value="' . $maaa_valsarray[0] . '">
        Country:<br><input type="text" name="val_country" value="' . $maaa_valsarray[1] . '"><br>
        Visa Entry Fee:<br><input type="text" name="val_visaentry" value="' . $maaa_valsarray[2] . '"><br>
        Visa Exit Fee:<br><input type="text" name="val_visaexit" value="' . $maaa_valsarray[3] . '"><br>
        Visa Notes:<br><input type="text" name="val_visanotes" value="' . $maaa_valsarray[4] . '"><br>
        Visit Order:<br><input type="text" name="val_visitorder" value="' . $maaa_valsarray[5] . '"><br>
        Approximate Duration:<br><input type="text" name="val_duration" value="' . $maaa_valsarray[6] . '"><br>
        USD$1 Equals:<br><input type="text" name="val_currconvert" value="' . $maaa_valsarray[7] . '"><br>
        Foreign Amount Spent:<br><input type="text" name="val_currforeign" value="' . $maaa_valsarray[8] . '"><br>
        Map URL:<br>' .  $maaa_valsarray[9] .'<br>Use PHP MyAdmin to update the Map URL.<br>
        <input type="submit" value="Submit" name="submit_tupdate"> &nbsp; &nbsp; &nbsp;
        <input type="submit" value="Delete" name="delete_tupdate" ' . $maaa_delete . '>
        </form>';
      $maaa_dfields = array ("id", "country", "visa_entry", "visa_exit", "visit_order", "approx_duration", "map_url");
      $maaa_tfields = "country, visa_entry, visa_exit, visa_notes, visit_order, approx_duration, curr_convert, curr_foreign";
      $maaa_tftypes = "%s, %f, %f, %s, %d, %d, %f, %f";
      break;
    case "days":
      $maaa_countrystr = maaa_list_options("countries", "country", $maaa_valsarray[1]);
      $maaa_dataform = '<form method="post" action="" name="f_days">' . wp_nonce_field('maaa_days_nonce') . '
        <input type="hidden" name="val_tchoice" value="' . $maaa_tablechoice . '">
        <input type="hidden" name="val_idedit" value="' . $maaa_valsarray[0] . '">
        Country:<br><select name="val_country">' . $maaa_countrystr . '</select><br>
        Entry Timestamp:<br><small>yyyy-mm-dd hh:mm:ss</small><br><input type="text" name="val_entry" value="' . $maaa_valsarray[2] . '"><br>
        Exit Timestamp:<br><small>yyyy-mm-dd hh:mm:ss</small><br><input type="text" name="val_exit" value="' . $maaa_valsarray[3] . '"><br>
        <input type="submit" value="Submit" name="submit_tupdate"> &nbsp; &nbsp; &nbsp;
        <input type="submit" value="Delete" name="delete_tupdate" ' . $maaa_delete . '>
        </form>';
      $maaa_dfields = array ("id", "country", "entry_ts", "exit_ts", "days");
      $maaa_tfields = "country, entry_ts, exit_ts, days";
      $maaa_tftypes = "%s, %s, %s, %f";
      break;
    case "expenses":
      $maaa_countrystr = maaa_list_options("countries", "country", $maaa_valsarray[2]);
      $maaa_categorystr = maaa_list_options("categories", "category", $maaa_valsarray[3]);
      $maaa_dataform = '<form method="post" action="" name="f_expenses">' . wp_nonce_field('maaa_expenses_nonce') . '
        <input type="hidden" name="val_tchoice" value="' . $maaa_tablechoice . '">
        <input type="hidden" name="val_idedit" value="' . $maaa_valsarray[0] . '">
        Date:<br><small>yyyy-mm-dd</small><br><input type="text" name="val_date" value="' . $maaa_valsarray[1] . '"><br>
        Country:<br><select name="val_country">' . $maaa_countrystr . '</select><br>
        Category:<br><select name="val_category">' . $maaa_categorystr . '</select><br>
        Detail:<br><input type="text" name="val_detail" value="' . $maaa_valsarray[4] . '"><br>
        Price:<br><input type="text" name="val_price" value="' . $maaa_valsarray[5] . '"><br>
        Num of Units:<br><input type="text" name="val_units" value="' . $maaa_valsarray[6] . '"><br>
        <input type="submit" value="Submit" name="submit_tupdate"> &nbsp; &nbsp; &nbsp;
        <input type="submit" value="Delete" name="delete_tupdate" ' . $maaa_delete . '>
        </form>';
      $maaa_dfields = array ("id", "spenddate", "country", "category", "detail", "price");
      $maaa_tfields = "spenddate, country, category, detail, price, units, ppu";
      $maaa_tftypes = "%s, %s, %s, %s, %f, %f, %f";
      break;
    default:
      echo 'There was an error in "switch ($maaa_tablechoice)"'; //error message
  } //end switch

  return array ($maaa_dataform, $maaa_dfields, $maaa_tfields, $maaa_tftypes);
} //end function

//Make data table
function maaa_datatable($maaa_tablechoice, $maaa_tablefields) {
  global $wpdb;
  $maaa_tablepath = $wpdb->prefix . "maaa_" . $maaa_tablechoice;
  $i = count($maaa_tablefields) - 1;
  $maaa_tpct = floor(98 / $i);

  switch ($maaa_tablechoice) {
    case "none":
      break;
    case "accomtrans":
      $maaa_tablesql = "SELECT " . implode(", ",$maaa_tablefields) . " FROM $maaa_tablepath WHERE conf_cancelled = '0000-00-00 00:00:00' ORDER BY start_in ASC";
      $maaa_tabletitle = 'All active submissions to the ' . ucfirst($maaa_tablechoice) . ' table:';
      break;
    case "budget":
      $maaa_tablesql = "SELECT " . implode(", ",$maaa_tablefields) . " FROM $maaa_tablepath ORDER BY id DESC";
      $maaa_tabletitle = 'All submissions to the ' . ucfirst($maaa_tablechoice) . ' table:';
      break;
    case "countries":
      $maaa_tablesql = "SELECT " . implode(", ",$maaa_tablefields) . " FROM $maaa_tablepath ORDER BY country ASC";
      $maaa_tabletitle = 'All submissions to the ' . ucfirst($maaa_tablechoice) . ' table:';
      break;
    case "categories":
      $maaa_tablesql = "SELECT " . implode(", ",$maaa_tablefields) . " FROM $maaa_tablepath ORDER BY category ASC";
      $maaa_tabletitle = 'All submissions to the ' . ucfirst($maaa_tablechoice) . ' table:';
      break;
    default:
      $maaa_tablesql = "SELECT " . implode(", ",$maaa_tablefields) . " FROM $maaa_tablepath ORDER BY id DESC LIMIT 0 , 60";
      $maaa_tabletitle = 'Sixty most recent submissions to the ' . ucfirst($maaa_tablechoice) . ' table:';
  } //end switch

  $maaa_tabledata = $wpdb->get_results( $maaa_tablesql );
  if ($maaa_tabledata) {
    foreach ($maaa_tabledata as $maaa_tabledata_row) {
      foreach ($maaa_tabledata_row as $maaa_tabledata_field) {
        if ($maaa_tabledata_str) {
          $maaa_tabledata_str = $maaa_tabledata_str . '<td style="vertical-align:top; width="' . $maaa_tpct . '%"><center>' . $maaa_tabledata_field . '</center></td>';
        } else {
          $maaa_tabledata_str = '<td style="vertical-align:top; width="2%"><center><input type="submit" name="id_' . $maaa_tabledata_field . '" value="' . $maaa_tabledata_field . '"></center></td>';
        } //end if
      } //end foreach
      $maaa_tablerow_str = $maaa_tablerow_str . '<tr style="border-bottom:1px dotted #999;">' . $maaa_tabledata_str . '</tr>';
      unset($maaa_tabledata_str);
    } //end foreach
    foreach ($maaa_tablefields as $maaa_tablehead) {
      if ($maaa_tablehead_str) {
        $maaa_tablehead_str = $maaa_tablehead_str . '<th width="' . $maaa_tpct . '%">' . strtoupper($maaa_tablehead) . '</th>';
      } else {
        $maaa_tablehead_str = $maaa_tablehead_str . '<th width="2%">' . strtoupper($maaa_tablehead) . '</th>';
      } //endif
    } //end for
    //$maaa_tabledata_table =
    return '<hr><center><b>' . $maaa_tabletitle . '</b></center>
      <table width="100%" style="border-collapse:collapse;">
        <form method="post" action="" name="ids">' . wp_nonce_field('maaa_editid_nonce') . '<input type="hidden" name="val_edittable" value="' . $maaa_tablechoice . '">
        <tr>' . $maaa_tablehead_str . '</tr>
        ' . $maaa_tablerow_str . '
        </form>
      </table>';
  } //end if

} //end function

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
function maaa_forms_widget() {
  global $wpdb;
  $wpdb->show_errors();

  //Define input forms -- General
  if (isset($_POST['submit_tchoice'])) {
    $maaa_tchoice = $_POST['tablechoice'];
  } else if (isset($_POST['val_edittable'])) {
    $maaa_tchoice = $_POST['val_edittable'];
  } else if (isset($_POST['submit_tupdate']) || isset($_POST['delete_tupdate'])) {
    $maaa_tchoice = $_POST['val_tchoice'];
  }

  //Choose table info to display
  switch ($maaa_tchoice) {
    case "none":
      $maaa_form = 'Please select a table from the list.';
      break;
    case "accomtrans":
      $maaa_data = maaa_dataform($maaa_tchoice, "none", 13);
      break;
    case "budget":
      $maaa_data = maaa_dataform($maaa_tchoice, "none", 5);
      break;
    case "categories":
      $maaa_data = maaa_dataform($maaa_tchoice, "none", 2);
      break;
    case "countries":
      $maaa_data = maaa_dataform($maaa_tchoice, "none", 10);
      break;
    case "days":
      $maaa_data = maaa_dataform($maaa_tchoice, "none", 5);
      break;
    case "expenses":
      $maaa_data = maaa_dataform($maaa_tchoice, "none", 8);
      break;
    default:
      $maaa_form = "Use PHP MyAdmin to update the " . ucfirst($maaa_tchoice) . " table.";
      unset($maaa_sql);
  } //end switch

  //Display table selection form
  $maaa_tableoptions = array ("none","accomtrans","budget","categories","countries","days","expenses");
  foreach ($maaa_tableoptions as $maaa_toption) {
    if ($maaa_toption == "none") {
      $maaa_toptionstr = $maaa_toptionstr . '<option value="' . $maaa_toption . '">(select table)</option>';
    } else if ($maaa_toption == $maaa_tchoice) {
      $maaa_toptionstr = $maaa_toptionstr . '<option selected value="' . $maaa_toption . '">' . ucfirst($maaa_toption) . '</option>';
    } else {
      $maaa_toptionstr = $maaa_toptionstr . '<option value="' . $maaa_toption . '">' . ucfirst($maaa_toption) . '</option>';
    } //end if
  } //end for
  echo '
  <table width="100%">
    <tr>
      <td width="50%">
        <form method="post" action="">';
        wp_nonce_field('maaa_choosetable_nonce');
        echo '
        <select name="tablechoice" class="postform">
    ' . $maaa_toptionstr . '
        </select>&nbsp;<input type="submit" value="Go" name="submit_tchoice">
        &nbsp; &nbsp; <a href="http://www.meggangreen.com/maaa/stats" target="_new">View Stats</a>
        </form>
      </td>';

  //Load subforms to update table
  if (isset($_POST['submit_tchoice'])) { //Table choice is submitted
    check_admin_referer('maaa_choosetable_nonce'); //check nonces
    //$maaa_tchoice = $_POST['tablechoice'];
    $maaa_table = $wpdb->prefix . "maaa_" . $maaa_tchoice;
    echo '<td width="50%"><b>' . ucfirst($maaa_tchoice) . '</b></td></tr>
          <tr>
          <td></td>
          <td>' . $maaa_data[0] . '</td>
          </tr>
          <tr>
          <td colspan="2">
            ' . maaa_datatable($maaa_tchoice, $maaa_data[1]) . '
          </td>
          </tr>
          </table>';

  } else if (isset($_POST['val_edittable'])) { //Table row to update is submitted
    check_admin_referer('maaa_editid_nonce'); //check nonces
    $maaa_table = $wpdb->prefix . "maaa_" . $maaa_tchoice;
    $maaa_updateid_post = implode(",,", $_POST);
    $maaa_updateid_loc = strrpos($maaa_updateid_post,",,");
    $maaa_updateid = substr($maaa_updateid_post,$maaa_updateid_loc+2);
    $maaa_idvals = $wpdb->get_row( "SELECT * FROM $maaa_table WHERE id = $maaa_updateid", ARRAY_N ); //$maaa_idvals[0]
    $maaa_data = maaa_dataform($maaa_tchoice, $maaa_idvals, count($maaa_idvals));
    echo '<td width="50%"><b>' . ucfirst($maaa_tchoice) . $maaa_updateid . '</b></td></tr>
          <tr>
          <td></td>
          <td>' . $maaa_data[0] . '</td>
          </tr>
          <tr>
          <td colspan="2">
            ' . maaa_datatable($maaa_tchoice, $maaa_data[1]) . '
          </td>
          </tr>
          </table>';

  } else if (isset($_POST['submit_tupdate']) || isset($_POST['delete_tupdate'])) { //Table row entries are submitted
    $maaa_table = $wpdb->prefix . "maaa_" . $maaa_tchoice;
    if (isset($_POST[val_idedit])) {
      $maaa_val_id = $_POST[val_idedit];
    } //end if
    //$maaa_tchoice = $_POST['val_tchoice'];
    //$maaa_table = $wpdb->prefix . "maaa_" . $maaa_tchoice;
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
        $maaa_valstr = array ($maaa_val_country1, $maaa_val_country2, $maaa_val_startin, $maaa_val_endout, $maaa_val_coname, $maaa_val_coaddress, $maaa_val_cophone, $maaa_val_cocontact, $maaa_val_notes, $maaa_val_confcode, $maaa_val_confdate, $maaa_val_confcancelled);
        break;
      case "budget":
        check_admin_referer('maaa_budget_nonce');
        $maaa_val_type = filter_var($_POST['val_type'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_descrip = filter_var($_POST['val_descrip'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_price = filter_var($_POST['val_price'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_detail = filter_var($_POST['val_detail'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_valstr = array ($maaa_val_type, $maaa_val_descrip, $maaa_val_price, $maaa_val_detail);
        break;
      case "categories":
        check_admin_referer('maaa_categories_nonce');
        $maaa_val_category = filter_var($_POST['val_cat'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_valstr = array ($maaa_val_category);
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
        $maaa_valstr = array ($maaa_val_country, $maaa_val_visaentry, $maaa_val_visaexit, $maaa_val_visanotes, $maaa_val_visitorder, $maaa_val_duration, $maaa_val_currconvert, $maaa_val_currforeign );
        break;
      case "days":
        check_admin_referer('maaa_days_nonce');
        $maaa_val_country = filter_var($_POST['val_country'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_entry = filter_var($_POST['val_entry'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_exit = filter_var($_POST['val_exit'], FILTER_CALLBACK, array("options"=>"maaa_filterinput"));
        $maaa_val_days = maaa_date_diff($maaa_val_entry,$maaa_val_exit,0)[1];
        $maaa_valstr = array ($maaa_val_country,$maaa_val_entry,$maaa_val_exit,$maaa_val_days);
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
        $maaa_valstr = array ($maaa_val_date,$maaa_val_country,$maaa_val_category,$maaa_val_detail,$maaa_val_price,$maaa_val_units,$maaa_val_ppu);
        break;
      default:
        $maaa_form = '<center>Not so much.</center>';
    } // end switch

    //Execute query and display result
    if (isset($maaa_val_id) && $maaa_val_id !="" && isset($_POST['submit_tupdate'])) {
      $maaa_data[2] = explode(", ",$maaa_data[2]);
      $maaa_data[3] = explode(", ",$maaa_data[3]);
      for ($i=0; $i<=count($maaa_data[2])-1; $i++) {
        $maaa_setstr[$i] = $maaa_data[2][$i] . ' = ' . $maaa_data[3][$i];
      } //end for
      $maaa_setstr = implode(", ",$maaa_setstr);
      $maaa_sql = $wpdb->prepare( "UPDATE $maaa_table SET $maaa_setstr WHERE id = $maaa_val_id", $maaa_valstr );
    } else if (isset($maaa_val_id) && $maaa_val_id !="" && isset($_POST['delete_tupdate'])) {
      $maaa_sql = $wpdb->prepare( "DELETE FROM $maaa_table WHERE id = $maaa_val_id LIMIT 1", $maaa_valstr );
    } else {
      $maaa_sql = $wpdb->prepare( "INSERT INTO $maaa_table ( $maaa_data[2] ) VALUES ( $maaa_data[3] )", $maaa_valstr );
    } //end if
    echo '<td width="50%"><b>' . ucfirst($maaa_tchoice) . '</b></td></tr>
          <tr><td valign="top">';
    if ($wpdb->query($maaa_sql) === FALSE) {
      echo '<b>Bummer!</b><br><br>' . $wpdb->print_error();
    } else {
      echo '<b>Success!</b><br><br>';
    } //end if
    echo implode('<br>',$maaa_valstr);
    echo '</td>
          <td valign="top">
            ' . $maaa_data[0] . '
          </td>
        </tr>
        <tr>
          <td colspan="2">
            ' . maaa_datatable($maaa_tchoice, $maaa_data[1]) . '
          </td>
        </tr>
      </table>';
  } else {
  echo '
    </tr>
  </table>';
  } //end if
} //end function

// Create the widget action hook function which activates the admin dashboard widget
function maaa_add_table_widgets() {
  wp_add_dashboard_widget('maaa_admin_widget', 'MAAA Tables', 'maaa_forms_widget');
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
    $maaa_countrystr = maaa_list_options("countries", "country", $maaa_cchoice);

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
    $maaa_catfins = explode(",,",$maaa_catfins);

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
    <select name="val_cchoice" class="postform"><?php echo $maaa_countrystr; ?></select>&nbsp;<input type="submit" value="Show" name="submit_country" class="postform">
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
              <td width="100%"><?php echo $maaa_daytotal; ?></td>
            </tr>
            <tr style="height:15px"></tr>
            <tr>
              <th width="100%"><b>First Entry</b></td>
            </tr>
            <tr>
              <td width="100%"><?php echo $maaa_day1; ?></td>
            </tr>
            <tr style="height:15px"></tr>
            <tr>
              <th width="100%"><b>Last Exit</b></td>
            </tr>
            <tr>
              <td width="100%"><?php echo $maaa_day2; ?></td>
            </tr>
            <tr style="height:15px"></tr>
            <tr>
              <th width="100%"><b>USD$1 Equals</b></td>
            </tr>
            <tr>
              <td width="100%"><?php echo $maaa_currency; ?></td>
            </tr>
          </table>
        </td>
        <td width="75%" style="border-left: double #1188ee">
          <table id="statsfin" style="padding-left: 35px">
            <tr>
              <th width="40%"><b><?php echo $maaa_cchoice; ?></b></td>
              <th width="30%" colspan="2" style="text-align:center"><b>Total USD$</b></td>
              <th width="30%" colspan="2" style="text-align:center"><b>Avg / Day</b></td>
            </tr>
            <tr>
              <td width="40%">All Categories</td>
              <td width="15%" style="text-align:right">$<?php echo esc_html( maaa_calc_stat_vals( $maaa_cattotal, 1 )[0] ); ?></td>
              <td width="15%" style="text-align:left">.<?php echo esc_html( maaa_calc_stat_vals( $maaa_cattotal, 1 )[1] ); ?></td>
              <td width="15%" style="text-align:right">$<?php echo esc_html( maaa_calc_stat_vals( $maaa_cattotal, $maaa_daysum )[0] ); ?></td>
              <td width="15%" style="text-align:left">.<?php echo esc_html( maaa_calc_stat_vals( $maaa_cattotal, $maaa_daysum )[1] ); ?></td>
              <?php // echo maaa_calc_stat_vals( $maaa_cattotal,1 ); // td 10%R 20%L code for total ?>
              <?php // echo maaa_calc_stat_vals( $maaa_cattotal,$maaa_daysum ); // td 10%R 20%L code for per diem ?>
            </tr>
            <?php
            foreach ($maaa_catfins as $maaa_catfin) {
              $maaa_catfin_vals = explode("%",$maaa_catfin);
              $maaa_catfin_c = $maaa_catfin_vals[0];
              $maaa_catfin_f = $maaa_catfin_vals[1];
              if ($maaa_catfin_c != "") { ?>
                <tr>
                  <td width="40%"><?php echo esc_html( $maaa_catfin_c ); ?></td>
                  <td width="15%" style="text-align:right">$<?php echo esc_html( maaa_calc_stat_vals( $maaa_catfin_f, 1 )[0] ); ?></td>
                  <td width="15%" style="text-align:left">.<?php echo esc_html( maaa_calc_stat_vals( $maaa_catfin_f, 1 )[1] ); ?></td>
                  <td width="15%" style="text-align:right">$<?php echo esc_html( maaa_calc_stat_vals( $maaa_catfin_f, $maaa_daysum )[0] ); ?></td>
                  <td width="15%" style="text-align:left">.<?php echo esc_html( maaa_calc_stat_vals( $maaa_catfin_f, $maaa_daysum )[1] ); ?></td>
                  <?php // echo maaa_calc_stat_vals( $maaa_catfin_f,1 ); // td 10%R 20%L code for total ?>
                  <?php // echo maaa_calc_stat_vals( $maaa_catfin_f,$maaa_daysum ); // td 10%R 20%L code for per diem ?>
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


////////////////////////////////OLD INSTALL
//MySQL syntax updated through manual table creation 28-Jul-2013 (countries through expenses)
/*
  global $wpdb;
  global $log_db_version;

  //Accom/Trans
  CREATE TABLE  `wp_maaa_accomtrans` (
 `id` MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 `country1` VARCHAR( 30 ) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL ,
 `country2` VARCHAR( 30 ) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL ,
 `start_in` DATETIME NOT NULL ,
 `end_out` DATETIME NOT NULL ,
 `co_name` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL ,
 `co_address` VARCHAR( 5000 ) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL ,
 `co_phone` VARCHAR( 15 ) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL ,
 `co_contact` VARCHAR( 200 ) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL ,
 `notes` VARCHAR( 10000 ) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL ,
 `conf_code` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL ,
 `conf_date` DATE NOT NULL ,
 `conf_cancelled` DATE NOT NULL ,
FULLTEXT (
`country1` ,
`country2` ,
`co_name` ,
`co_address` ,
`co_contact` ,
`notes` ,
`conf_code`
)
) ENGINE = MYISAM CHARACTER SET latin1 COLLATE latin1_general_cs;

  //Budget
  $maaa_table = $wpdb->prefix . "maaa_budget";
  if($wpdb->get_var("show tables like '$maaa_table'") != $maaa_table) {
    $sql = "CREATE TABLE " . $maaa_table . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      descrip varchar(30) NOT NULL,
      price decimal(7,2) NOT NULL,
      detail tinytext() NOT NULL,
      PRIMARY KEY id (id),
      UNIQUE KEY descrip (descrip)
    );";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
         dbDelta($sql);
    add_option("log_db_version", $log_db_version);
  }

  //Categories
  $maaa_table = $wpdb->prefix . "maaa_categories";
  if($wpdb->get_var("show tables like '$maaa_table'") != $maaa_table) {
    $sql = "CREATE TABLE " . $maaa_table . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      category varchar(30) NOT NULL,
      PRIMARY KEY id (id),
      UNIQUE KEY category (category)
    );";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
         dbDelta($sql);
    add_option("log_db_version", $log_db_version);
  }

    //Countries
  CREATE TABLE wp_maaa_countries(

id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT ,
country VARCHAR( 30 ) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL ,
visa_entry DECIMAL( 6, 2 ) DEFAULT 0,
visa_exit DECIMAL( 6, 2 ) DEFAULT 0,
visa_notes TEXT CHARACTER SET latin1 COLLATE latin1_general_cs,
visit_order SMALLINT( 2 ) DEFAULT 0,
approx_duration SMALLINT( 2 ) DEFAULT  0,
curr_convert DECIMAL( 8, 5 ) DEFAULT  0,
curr_foreign DECIMAL( 7, 2 ) DEFAULT  0,
map_url TEXT( 255 ) CHARACTER SET latin1 COLLATE latin1_general_cs,
PRIMARY KEY id( id ) ,
UNIQUE KEY country( country )
);

  //Days
  $maaa_table = $wpdb->prefix . "maaa_days";
  if ($wpdb->get_var("show tables like '$maaa_table'") != $maaa_table) {
    $maaa_tcreate_sql = "CREATE TABLE wp_maaa_days (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      country varchar(30) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
      entry_ts datetime NOT NULL,
      exit_ts datetime NOT NULL,
      days decimal(7,3) NOT NULL,
        PRIMARY KEY  id (id)
    );";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
         dbDelta( $maaa_tcreate_sql );
    add_option("log_db_version", $log_db_version);
  //}

  //Expenses
  $maaa_table = $wpdb->prefix . "maaa_expenses";
  if($wpdb->get_var("show tables like '$maaa_table'") != $maaa_table) {
    $sql = "CREATE TABLE " . $maaa_table . " (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      spenddate date NOT NULL,
        country varchar(30) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
      category varchar(30) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
      detail text(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
        price decimal(7,2) NOT NULL,
        units decimal(7,3) NOT NULL,
        ppu decimal(7,3) NOT NULL,
        PRIMARY KEY id (id)
    );";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
         dbDelta($sql);
    add_option("log_db_version", $log_db_version);
  }
*/

?>
