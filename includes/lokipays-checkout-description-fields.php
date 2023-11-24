<?php

add_filter( 'woocommerce_gateway_description', 'lokipays_lokipays_description_fields', 20, 2 );
add_action( 'woocommerce_checkout_process', 'lokipays_lokipays_description_fields_validation' );
add_action( 'woocommerce_checkout_update_order_meta', 'lokipays_checkout_update_order_meta', 10, 1 );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'lokipays_order_data_after_billing_address', 10, 1 );
add_action( 'woocommerce_order_item_meta_end', 'lokipays_order_item_meta_end', 10, 3 );

function lokipays_lokipays_description_fields( $description, $payment_id ) {

    if ( 'lokipays' !== $payment_id ) {
        return $description;
    }
    
    ob_start();

    echo '<div style="display: block; width:300px; height:auto;">';
    // echo '<img src="' . plugins_url('../assets/icon.png', __FILE__ ) . '">';

    woocommerce_form_field(
        'name_on_card',
        array(
            'type' => 'text',
            'label' =>__( 'Name on card', 'lokipays-payments-woo' ),
            'class' => array( 'form-row', 'form-row-wide' ),
            'required' => true,
        )
    );
    woocommerce_form_field(
        'number_on_card',
        array(
            'type' => 'text',
            'label' =>__( 'Number on card', 'lokipays-payments-woo' ),
            'class' => array( 'form-row', 'form-row-wide' ),
            'required' => true,
        )
    );
    echo '<table border="0"><tr>';
    echo '<td style="border:0px;padding:0px;">';
    woocommerce_form_field(
        'expiry_month_on_card',
        array(
            'type' => 'text',
            'label' =>__( 'Month', 'lokipays-payments-woo' ),
             'class' => array( 'form-row', 'form-row-wide' ),
            'required' => true,
        )
    );
    echo '</td>';
    echo '<td style="border:0px;padding:0px;">';
    woocommerce_form_field(
        'expiry_year_on_card',
        array(
            'type' => 'text',
            'label' =>__( 'Year', 'lokipays-payments-woo' ),
            'class' => array( 'form-row', 'form-row-wide' ),
            'required' => true,
        )
    );
    echo '</td>';
    echo '<td style="border:0px;padding:0px;">';
    woocommerce_form_field(
        'cvv_on_card',
        array(
            'type' => 'text',
            'label' =>__( 'CVV', 'lokipays-payments-woo' ),
            'class' => array( 'form-row', 'form-row-wide' ),
            'required' => true,
        )
    );
    echo '</tr></table>';

    echo '</div>';

    $description .= ob_get_clean();

    return $description;
}

function lokipays_lokipays_description_fields_validation() {
    if( 'lokipays' === $_POST['payment_method'] && ! isset( $_POST['name_on_card'] )  || empty( $_POST['name_on_card'] ) ) {
        wc_add_notice( 'Please enter a name on card that is to be billed', 'error' );
    }
    if( 'lokipays' === $_POST['payment_method'] && ! isset( $_POST['number_on_card'] )  || empty( $_POST['number_on_card'] ) ) {
        wc_add_notice( 'Please enter a number on card that is to be billed', 'error' );
    }
    if( 'lokipays' === $_POST['payment_method'] && ! isset( $_POST['expiry_month_on_card'] )  || empty( $_POST['expiry_month_on_card'] ) ) {
        wc_add_notice( 'Please enter a expiry month of card', 'error' );
    }
    if( 'lokipays' === $_POST['payment_method'] && ! isset( $_POST['expiry_year_on_card'] )  || empty( $_POST['expiry_year_on_card'] ) ) {
        wc_add_notice( 'Please enter a expiry year of card', 'error' );
    }
    if( 'lokipays' === $_POST['payment_method'] && ! isset( $_POST['cvv_on_card'] )  || empty( $_POST['cvv_on_card'] ) ) {
        wc_add_notice( 'Please enter a cvv of card', 'error' );
    }
}

function lokipays_checkout_update_order_meta( $order_id ) {

    if( isset( $_POST['name_on_card'] ) || ! empty( $_POST['name_on_card'] ) ) {
        update_post_meta( $order_id, 'name_on_card', $_POST['name_on_card'] );
     }
     if( isset( $_POST['number_on_card'] ) || ! empty( $_POST['number_on_card'] ) ) {
        update_post_meta( $order_id, 'number_on_card', $_POST['number_on_card'] );
     }
     if( isset( $_POST['expiry_month_on_card'] ) || ! empty( $_POST['expiry_month_on_card'] ) ) {
        update_post_meta( $order_id, 'expiry_month_on_card', $_POST['expiry_month_on_card'] );
     }
     if( isset( $_POST['expiry_year_on_card'] ) || ! empty( $_POST['expiry_year_on_card'] ) ) {
        update_post_meta( $order_id, 'expiry_year_on_card', $_POST['expiry_year_on_card'] );
     }
     if( isset( $_POST['cvv_on_card'] ) || ! empty( $_POST['cvv_on_card'] ) ) {
        update_post_meta( $order_id, 'cvv_on_card', $_POST['cvv_on_card'] );
     }
}

function lokipays_order_data_after_billing_address( $order ) {
    echo '<p><strong>' . __( 'Name On Card:', 'lokipays-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'name_on_card', true ) . '</p>';
}

function lokipays_order_item_meta_end( $item_id, $item, $order ) {
    echo '<p><strong>' . __( 'Name On Card:', 'lokipays-payments-woo' ) . '</strong><br>' . get_post_meta( $order->get_id(), 'name_on_card', true ) . '</p>';
}
