<?php
/**********************************************************************
Copyright (C) FrontAccounting, LLC.
Released under the terms of the GNU General Public License, GPL,
as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/
$page_security = 'SA_GRN';
$path_to_root = "..";
include_once($path_to_root . "/purchasing/includes/po_class.inc");

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_db.inc");
include_once($path_to_root . "/purchasing/includes/purchasing_ui.inc");

$js = "";
if ($SysPrefs->use_popup_windows)
    $js .= get_js_open_window(900, 500);
if (user_use_date_picker())
    $js .= get_js_date_picker();
page(_($help_context = "Receive Purchase Order Items"), false, false, "", $js);

//---------------------------------------------------------------------------------------------------------------
if (isset($_GET['POItem'])){
    $_SESSION['ORDER'] =  get_sql_for_order_detail($_GET['POItem']);
}



//--------------------------------------------------------------------------------------------------

if ((!isset($_GET['PONumber']) || $_GET['PONumber'] == 0) && !isset($_SESSION['PO']))
{
    die (_("This page can only be opened if a purchase order has been selected. Please select a purchase order first."));
}

//--------------------------------------------------------------------------------------------------

function display_lot_receive_item()
{
    div_start('grn_items');
    start_table(TABLESTYLE, "colspan=7 width='80%'");
    $th = array(_("Item Code"), _("Description"), _("GRV Quantity"),_("Quantity"), _("Lot Number"), _("Expiry Date"));
    table_header($th);

    /*show the line items on the order with the quantity being received for modification */

    $total = 0;
    $k = 0; //row colour counter

    if (isset($_SESSION['ORDER'])){
        $ln_itm = $_SESSION['ORDER'];
    }

    hidden('po_detail_item',$ln_itm['po_detail_item']);
    hidden('qty_received',$ln_itm['quantity_received']);
    hidden('doc_no',$ln_itm['reference']);
    hidden('item_code',$ln_itm['item_code']);

    alt_table_row_color($k);
    label_cell($ln_itm['item_code']);
    label_cell($ln_itm['description']);
    label_cell($ln_itm['quantity_received']);
    qty_cells(null, 'quantity_in', $_POST['quantity_in'], null, null, '');
    text_cells(null,'lot_number',$_POST['lot_number'],20,200);
    date_cells(null, 'expiry_date', '', true, 0, 0, 0, '', true);
    end_row();

    end_table();
    div_end();
}
//--------------------------------------------------------------------------------------------------

function check_po_changed()
{
    /*Now need to check that the order details are the same as they were when they were read
    into the Items array. If they've changed then someone else must have altered them */
    // Compare against COMPLETED items only !!
    // Otherwise if you try to fullfill item quantities separately will give error.
    $result = get_po_items($_SESSION['PO']->order_no);

    $line_no = 0;
    while ($myrow = db_fetch($result))
    {
        $ln_item = $_SESSION['PO']->line_items[$line_no];
        // only compare against items that are outstanding
        $qty_outstanding = $ln_item->quantity - $ln_item->qty_received;
        if ($qty_outstanding > 0)
        {
            if ($ln_item->qty_inv != $myrow["qty_invoiced"]	||
                $ln_item->stock_id != $myrow["item_code"] ||
                $ln_item->quantity != $myrow["quantity_ordered"] ||
                $ln_item->qty_received != $myrow["quantity_received"])
            {
                return true;
            }
        }
        $line_no++;
    } /*loop through all line items of the order to ensure none have been invoiced */

    return false;
}

//--------------------------------------------------------------------------------------------------

function can_process()
{
    global $SysPrefs;

    if (!is_date($_POST['expiry_date'])){
        display_error(_("Invalid Expiry Date Input."));
        set_focus('expiry_date');
        return false;
    }
    $date1 = date('Y-m-d',strtotime(now()));
    $date2 = date('Y-m-d',strtotime(get_post('expiry_date')));
    $d1=new DateTime($date2);
    $d2=new DateTime($date1);
    $Months = $d1->diff($d2)->m + ($d1->diff($d2)->y*12);
    if ($Months < 6){
        display_error(_("Expiry Date cannot be less than 6 Months."));
        set_focus('expiry_date');
        return false;
    }

    if (!get_post('quantity_in')){
        display_error(_("Quantity cannot be empty."));
        set_focus('quantity_in');
        return false;
    }
    if (!is_numeric(get_post('quantity_in')) || get_post('quantity_in')<=0){
        display_error(_("Invalid Quantity field"));
        set_focus('quantity_in');
        return false;
    }

    if (get_post('quantity_in') != get_post('qty_received') ){
        display_error(_("Quantity must be equal to GRV Quantity."));
        set_focus('quantity_in');
        return false;
    }

    if (!get_post('lot_number')){
        display_error(_("Lot Number cannot be empty."));
        set_focus('lot_number');
        return false;
    }

    if (checkLotItem(get_post('lot_number'))){
        display_error(_("Lot Number must be unique"));
        set_focus('lot_number');
        return false;
    }

    return true;
}

function process_receive_lot()
{
    global $path_to_root, $Ajax;
    if (!can_process())
        return;

     addLotItem( $_POST['quantity_in'],$_POST['lot_number'],$_POST['po_detail_item'],$_POST['item_code'],$_POST['doc_no'],$_POST['expiry_date']);
    meta_forward('/purchasing/inquiry/grn_search.php');
}

//--------------------------------------------------------------------------------------------------

if (isset($_GET['PONumber']) && $_GET['PONumber'] > 0 && !isset($_POST['Update']))
{
    create_new_po(ST_PURCHORDER, $_GET['PONumber']);
    $_SESSION['PO']->trans_type = ST_SUPPRECEIVE;
    $_SESSION['PO']->reference = $Refs->get_next(ST_SUPPRECEIVE,
        array('date' => Today(), 'supplier' => $_SESSION['PO']->supplier_id));
    copy_from_cart();
}

//--------------------------------------------------------------------------------------------------

if (isset($_POST['ProcessLotItem']))
{

    process_receive_lot();
}


//--------------------------------------------------------------------------------------------------
start_form();

edit_grn_lot_summary($_SESSION['ORDER'], true);
display_heading(_("Item to Generate Lot#"));
display_lot_receive_item();


echo '<br>';
submit_center_first('ProcessLotItem', _("Process Lot Item"), _("Clear all fields"), 'default');

end_form();

//--------------------------------------------------------------------------------------------------
end_page();
