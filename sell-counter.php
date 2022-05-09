<?php
/**
* Plugin Name: WooCommerce Sell Counter
* Description: Automatic Counter of Product Sellings
* Version: 1.0
* Author: Danila Kovalenko
* Author URI: https://www.upwork.com/freelancers/danilakovalenko1
*/

add_shortcode('sellcounter','sellcounter_func');
function sellcounter_func($atts){
    global $post;
    $id = (isset($atts['product']) ? $atts['product'] : $post->ID);
        $product = get_post_meta($id,'_sellcounter_field',true);
    if(!is_null($product)&&!is_bool($product)&&$product!==""){
        return $product;
    } else {
        update_post_meta($id,'_sellcounter_field',0);
        return 0;
    }
}
add_action('woocommerce_order_status_completed', function ($order_id) {
   $order = wc_get_order($order_id);
   foreach ($order->get_items() as $id => $item){
       $pid = $item->get_product_id();
       $quantity = $item->get_quantity();
       $sell_count2 = intval(get_post_meta($pid,'_sellcounter_field',true));
       $sell_count = $sell_count3 = $sell_count2 + intval($quantity);
       $lottery = [];
       if($sell_count>=intval(get_option('sellcounter_max'))) {
           update_post_meta($pid,'_sellcounter_random_number', mt_rand(1,intval(get_option('sellcounter_random_max'))));
           $sell_count = $sell_count - 500;
       }
       update_post_meta($pid,'_sellcounter_field',$sell_count);;
   }
}, 10, 1);
function sellcounter_add_settings_page() {
    add_menu_page( 'Sell Counter Settings', 'Sell Counter Settings', 'manage_options', 'sell-counter', 'sellcounter_settings_page' );
}
add_action( 'admin_menu', 'sellcounter_add_settings_page' );

function sellcounter_settings_page() {
    $done = false;
    if(isset($_POST['pid'])){
        update_post_meta($_POST['pid'], '_sellcounter_field', 0);
        $done = true;
    }
    if(isset($_POST['all'])){
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
        );
        $pages_array = get_posts($args);
        foreach ($pages_array as $p){
            update_post_meta($p->ID,'_sellcounter_field', 0);
        }
        $done = true;
    }
    if(isset($_POST['max_number'])){
        update_option('sellcounter_max',$_POST['max_number']);
        $done = true;
    }
    if(isset($_POST['max_random_number'])){
        update_option('sellcounter_random_max',$_POST['max_random_number']);
        $done = true;
    }
    if($done){
        echo '<div class="notice notice-success is-dismissible">
        <p>Done!</p>
</div>';
    }
    ?>
    <h2>WooCommerce Sell Counter Settings</h2>
    <form method="post">
        <label>Max No. of Sellings</label>
        <input type="number" name="max_number" value="<?php echo get_option('sellcounter_max');?>"/><br/>
        <br/><br/>
        <label>Max Random Number</label>
        <input type="number" name="max_random_number" value="<?php echo get_option('sellcounter_random_max');?>"/><br/>
        <input  class="button button-primary" type="submit" value="Save" />
    </form><br/>
    <form method="post">
        <label>Product ID to reset</label>
        <input type="text" placeholder="e.g. 23" name="pid"/>
        <input  class="button button-primary" type="submit" value="Reset product counter" />
    </form>
    <br/>
    <form method="post">
        <input type="hidden" name="all" value="1"/>
        <input class="button button-primary" type="submit" value="Reset all counters" />
    </form>
    <?php
}
