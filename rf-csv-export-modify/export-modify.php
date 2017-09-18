<?php
/**
 * Plugin Name: WooCommerce Modify CSV output
 * Plugin URI: http://rayflores.com/plugins/csv-modifier/
 * Description: modifies output from csv exporter Scottees
 * Version: 1.0.1
 * Author: Ray Flores
 * Author URI: http://rayflores.com
 */
 
 // set order columns
function rf_wc_csv_export_custom_order_columns( $column_headers ){

			$column_headers = array(
					'order_id'				=> 'ORDER ID', // order number
					'order_date'               		=> 'DATE',// order date
					'status'             		=> 'STATUS',// order status
					'shipping_total' 		=> 'SHIPPING',
					'order_total' 			=> 'ORDER_TOTAL',
					'payment_method'  => 'PMT_TYPE',// payment method
					'shipping_method'	=> 'SHP_TYPE', // shipping type
					'customer_id'         => 'CUST_ID', // customer id
					'billing_first_name' => 'FIRST',// billing_first_name
					'billing_last_name'  => 'LAST',// billing_last_name
					'billing_company'   => 'COMPANY', // billing_company
					'billing_email'         => 'EMAIL',// billing_email
					'billing_phone'       	=> 'PHONE', // billing_phone
					'billing_address_1'  => 'ADDR1', // billing_address_1
					'billing_address_2'  => 'ADDR2', // billing_address_2
					'billing_postcode'    => 'ZIP', //billing_postcode
					'billing_city'        	=> 'CITY', // billing_city
					'billing_state'       	=> 'ST', // billing_state
					'customer_note'		=> 'CUST_NOTE', // customer notes
					'item_name'			=> 'ITEM_NAME', // product name
					'item_sku'				=> 'ITEM_SKU', // product sku
					'item_quantity'		=> 'ITEM_QTY', // item quantity
					'item_total' 			=> 	'ITEM_TOTAL', // item total
					//'item_meta'			=>	'ITEM_META', //meta ?? 
					'pa_color' 				=> 'COLOR',
					'pa_color2'			=> 'COLOR2',
					'Size'					=> 'SIZE',
					'YOG' 					=> 'YOG',
					'Name - Name'		=> 'NAME',
					'pa_name2'			=> 'NAME2',
					'pa_logo'				=> 'LOGO',
					'pa_background'	=> 'BACKGROUND',
					'pa_skinlogo'			=> 'SKINLOGO',
					'pa_misc1'				=> 'P10',
					'pa_misc2'				=> 'P11',
					
					'order_notes'			=> 'ORDER_NOTES', // order_notes
					'vendors'				=> 'VENDORS', // vendors list ??
					'_cost_field'			=> 'COST',
					'_sell_field'				=> 'SELL',
					'_source_field'		=> 'SOURCE',
					'_mfgr_field'			=> 'MFGR',
					'_itemno_field'		=> 'ITEMNO',
					'_decocost_field'	=> 'DECOCOST',
					'_prodnote_field'	=> 'PRODNOTE',
					'_c08_field'			=> 'C08',
					'_c09_field'			=> 'C09',
					'_c10_field'			=> 'C10',
				
				);
			

			return $column_headers;

}
add_filter( 'wc_customer_order_csv_export_order_headers', 'rf_wc_csv_export_custom_order_columns' );
 
 
 
 /**
 * Alter the column headers for the orders CSV to split item_meta into separate columns
 *
 * Note that this change is only applied to the Default - One Row per Item format
 *
 * @param array $column_headers {
 *     column headers in key => name format
 *     to modify the column headers, ensure the keys match these and set your own values
 * }
 * @param WC_Customer_Order_CSV_Export_Generator $csv_generator, generator instance
 * @return array column headers in column_key => column_name format
 */
function rf_wc_csv_export_order_headers_split_item_meta( $column_headers, $csv_generator ) {


	if ( 'default_one_row_per_item' === $csv_generator->order_format ) {

		// remove item_meta column
		unset( $column_headers['item_meta'] );

		// get all item meta
		$all_item_meta = rf_wc_get_item_meta_for_orders( $csv_generator->ids );

		$item_meta_headers = array();

		foreach ( $all_item_meta as $meta_key ) {
			$item_meta_headers[ $meta_key ] = $meta_key;
		}

		$column_headers = rf_wc_array_insert_after( $column_headers, 'item_total', $item_meta_headers );
	}

	return $column_headers;
}
add_filter( 'wc_customer_order_csv_export_order_headers', 'rf_wc_csv_export_order_headers_split_item_meta', 10, 2 );


/**
 * CSV Order Export Line Item.
 *
 * Filter the individual line item entry to add the raw item for use in rf_wc_csv_export_order_row_one_row_per_item_split_item_meta()
 *
 * @param array $line_item {
 *     line item data in key => value format
 *     the keys are for convenience and not used for exporting. Make
 *     sure to prefix the values with the desired line item entry name
 * }
 *
 * @param array $item WC order item data
 * @return array $line_item
 */
function rf_wc_csv_export_order_line_item_add_raw_item( $line_item, $item, $product, $order ) {
	
	
		$line_item = array_merge( $line_item, array( 'raw_item' => $item ) );
		$line_item['_cost_field'] = get_post_meta($product->id, '_cost_field', true);
		$line_item['_sell_field'] = get_post_meta($product->id, '_sell_field', true);
		$line_item['_source_field'] = get_post_meta($product->id, '_source_field', true);
		$line_item['_mfgr_field'] = get_post_meta($product->id, '_mfgr_field', true);
		$line_item['_itemno_field'] = get_post_meta($product->id, '_itemno_field', true);
		$line_item['_decocost_field'] = get_post_meta($product->id, '_decocost_field', true);
		$line_item['_prodnote_field'] = get_post_meta($product->id, '_prodnote_field', true);
		$line_item['_c08_field'] = get_post_meta($product->id, '_c08_field', true);
		$line_item['_c09_field'] = get_post_meta($product->id, '_c09_field', true);
		$line_item['_c10_field'] = get_post_meta($product->id, '_c10_field', true);

	return $line_item;
}
add_filter( 'wc_customer_order_csv_export_order_line_item', 'rf_wc_csv_export_order_line_item_add_raw_item', 10,4 );


/**
 * CSV Order Export Row for One Row per Item.
 *
 * Filter the individual row data for the order export to add data for each item meta key
 *
 * @param array $order_data {
 *     order data in key => value format
 *     to modify the row data, ensure the key matches any of the header keys and set your own value
 * }
 * @param array $item
 * @param \WC_Order $order WC Order object
 * @param \WC_Customer_Order_CSV_Export_Generator $csv_generator, generator instance
 */
function rf_wc_csv_export_order_row_one_row_per_item_split_item_meta( $order_data, $item ) {

	$item_meta = new WC_Order_Item_Meta( $item['raw_item']['item_meta'] );

	foreach ( $item_meta->get_formatted() as $meta_key => $values ) {
		$order_data[ $meta_key ] = $values['value'];
	}
	$order_data['_cost_field'] = $item['_cost_field'];
	$order_data['_sell_field'] = $item['_sell_field'];
	$order_data['_source_field'] = $item['_source_field'];
	$order_data['_mfgr_field'] = $item['_mfgr_field'];
	$order_data['_itemno_field'] = $item['_itemno_field'];
	$order_data['_decocost_field'] = $item['_decocost_field'];
	$order_data['_prodnote_field'] = $item['_prodnote_field'];
	$order_data['_c08_field'] = $item['_c08_field'];
	$order_data['_c09_field'] = $item['_c09_field'];
	$order_data['_c10_field'] = $item['_c10_field'];

	return $order_data;
}
add_filter( 'wc_customer_order_csv_export_order_row_one_row_per_item', 'rf_wc_csv_export_order_row_one_row_per_item_split_item_meta', 10, 2 );


/** Helper Functions **********************************************************/

/**
 * Get item meta for orders
 *
 * @param array $order_ids array of order ids
 * @return array $all_item_meta array of all item meta keys for $order_ids
 */
function rf_wc_get_item_meta_for_orders( $order_ids ) {

	$all_item_meta = array();

	foreach ( $order_ids as $order_id ) {

		$order = wc_get_order( $order_id );

		// get line items
		foreach ( $order->get_items() as $item ) {
			$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );
			$all_item_meta = array_merge( $all_item_meta, array_keys( $item_meta->get_formatted() ) );
		}
	}

	return $all_item_meta;
}

/**
 * Insert the given element after the given key in the array
 *
 * @param array $array array to insert the given element into
 * @param string $insert_key key to insert given element after
 * @param array $element element to insert into array
 * @return array
 */
function rf_wc_array_insert_after( Array $array, $insert_key, Array $element ) {

	$new_array = array();

	foreach ( $array as $key => $value ) {

		$new_array[ $key ] = $value;

		if ( $insert_key == $key ) {

			foreach ( $element as $k => $v ) {
				$new_array[ $k ] = $v;
			}
		}
	}

	return $new_array;
}