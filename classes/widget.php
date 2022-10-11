<?php

/**
 *  Widget
 */

class SIGA4W_widget extends WP_Widget {

    private $defaults;

    private $def_val;

    function __construct() {

        parent::__construct(
            SIGA4W_WIDGET_ID,
            __( 'Show GA4 stats', 'sig-ga4-widget' ),
            [ 'description' => __( 'Display statistics for GA4 pageviews.', 'sig-ga4-widget' ) ]
        );

        $this->defaults =  [
    		'title' => __( 'Browsing Statistics', 'sig-ga4-widget' ),
            'pageview' => [
                'today' => [    /* translators: %s is today pageviews  */
                    'label' => __( 'Today pageviews: %s<br>', 'sig-ga4-widget' ),
                    'show' => 'yes',
                    'adjust' => 0
                ],
                'all' => [      /* translators: %s is tatal pageviews  */
                    'label' => __( 'Total pageviews: %s<br>', 'sig-ga4-widget' ),
                    'show' => 'yes',
                    'adjust' => 0
                ]
            ],
            'visit' => [
                'today' => [    /* translators: %s is today visits  */
                    'label' => __( 'Today visits: %s<br>', 'sig-ga4-widget' ),
                    'show' => '',
                    'adjust' => 0
                ],
                'all' => [
                                /* translators: %s is total visits  */
                    'label' => __( 'Total visits: %s<br>', 'sig-ga4-widget' ),
                    'show' => '',
                    'adjust' => 0
                ]
            ],
    	];

        $this->_mixKey( '', $this->defaults );
    }


    public function widget( $args, $instance ) {

        extract($instance);

        $widget_id = ( !empty($args['widget_id']) ) ? $args['widget_id'] : '';

        // output
        $content = '';
        $content .= $args['before_widget'];

        if( isset($title) && $title !== '' ){
            $content .= "{$args['before_title']}{$title}{$args['after_title']}";
        }

        $content .= '<div id="'.$widget_id.'_inner"><img src="'. SIGA4W_PLUGIN_URL .'assets/img/loading.gif"><script type="text/javascript">jQuery(document).ready(function($) {$.get(\''.admin_url('admin-ajax.php?action=siga4w-widget&id='.$widget_id.'&_wpnonce='.wp_create_nonce('siga4w-widget-'.$widget_id)).'\', function(data) {$(\'#'.$widget_id.'_inner\').html(data); });});</script></div>';

        $content .= $args['after_widget'];

        echo $content;

    }

    public function form( $instance ) {

        if( empty($instance) ){
            $instance = wp_parse_args( (array) $instance, $this->def_val );
        }
    ?>
        <p>
            <label><?php _e( 'Custom title:' , 'sig-ga4-widget' )?></label>
            <input class="widefat" type="text" name="<?php echo $this->get_field_name('title');?>" value="<?php echo esc_attr($instance['title']); ?>">
        </p>
        <p>
            <style type="text/css">
            table.siga4w{ width:100%; max-width:600px; border-collapse: collapse;border-spacing: 0;}
            table.siga4w td{ border:1px solid #eee;padding: 2px 10px;}
            </style>
            <label><?php _e( 'Custom items:' , 'sig-ga4-widget' )?></label>
            <table class="siga4w">
                <tr align="center">
                    <td width="50"><label><?php _e( 'Show', 'sig-ga4-widget' ); ?></label></td>
                    <td width="140"><label><?php _e( 'Default', 'sig-ga4-widget' ); ?></label></td>
                    <td width="*"><label><?php _e( 'Custom label text', 'sig-ga4-widget' ); ?></label></td>
                    <td width="100"><label><?php _e( 'Adjust', 'sig-ga4-widget' ); ?></label></td>
                </tr>
                <tr align="center">
                    <td>
                        <input class="" type="checkbox" value="yes" name="<?php echo $this->get_field_name('pageview_today_show');?>" <?php checked('yes',$instance['pageview_today_show'])?>>
                    </td>
                    <td align="left">
                        <label><?php echo $this->def_val['pageview_today_label'];?></label>
                    </td>
                    <td>
                        <input class="widefat" type="text" name="<?php echo $this->get_field_name('pageview_today_label');?>" value="<?php echo esc_attr($instance['pageview_today_label']); ?>">
                    </td>
                    <td>
                        <input class="widefat" type="text" name="<?php echo $this->get_field_name('pageview_today_adjust');?>" value="<?php echo esc_attr($instance['pageview_today_adjust']); ?>" onkeyup="value=value.replace(/[^\d]/g,'')" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))">
                    </td>
                </tr>
                <tr align="center">
                    <td>
                        <input class="" type="checkbox" value="yes" name="<?php echo $this->get_field_name('pageview_all_show');?>" <?php checked('yes',$instance['pageview_all_show'])?>>
                    </td>
                    <td align="left">
                        <label><?php echo $this->def_val['pageview_all_label']?></label>
                    </td>
                    <td>
                        <input class="widefat" type="text" name="<?php echo $this->get_field_name('pageview_all_label');?>" value="<?php echo esc_attr($instance['pageview_all_label']); ?>">
                    </td>
                    <td width="*">
                        <input class="widefat" type="text" name="<?php echo $this->get_field_name('pageview_all_adjust');?>" value="<?php echo esc_attr($instance['pageview_all_adjust']); ?>" onkeyup="value=value.replace(/[^\d]/g,'')" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))">
                    </td>
                </tr>
                <tr align="center">
                    <td>
                        <input class="" type="checkbox" value="yes" name="<?php echo $this->get_field_name('visit_today_show');?>" <?php checked('yes',$instance['visit_today_show'])?>>
                    </td>
                    <td align="left">
                        <label><?php echo $this->def_val['visit_today_label']?></label>
                    </td>
                    <td>
                        <input class="widefat" type="text" name="<?php echo $this->get_field_name('visit_today_label');?>" value="<?php echo esc_attr($instance['visit_today_label']); ?>">
                    </td>
                    <td width="*">
                        <input class="widefat" type="text" name="<?php echo $this->get_field_name('visit_today_adjust');?>" value="<?php echo esc_attr($instance['visit_today_adjust']); ?>" onkeyup="value=value.replace(/[^\d]/g,'')" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))">
                    </td>
                </tr>
                <tr align="center">
                    <td>
                        <input class="" type="checkbox" value="yes" name="<?php echo $this->get_field_name('visit_all_show');?>" <?php checked('yes',$instance['visit_all_show'])?>>
                    </td>
                    <td align="left">
                        <label><?php echo $this->def_val['visit_all_label']?></label>
                    </td>
                    <td>
                        <input class="widefat" type="text" name="<?php echo $this->get_field_name('visit_all_label');?>" value="<?php echo esc_attr($instance['visit_all_label']); ?>">
                    </td>
                    <td width="*">
                        <input class="widefat" type="text" name="<?php echo $this->get_field_name('visit_all_adjust');?>" value="<?php echo esc_attr($instance['visit_all_adjust']); ?>" onkeyup="value=value.replace(/[^\d]/g,'')" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))">
                    </td>
                </tr>
            </table>
        </p>
    <?php
    }

    public function update( $new_instance, $old_instance ) {

        $instance = $old_instance;

        $instance['title'] = siga4w_strip_tags( $new_instance['title'] );

        $instance['pageview_today_label']   = siga4w_strip_tags( $new_instance['pageview_today_label'] );
        $instance['pageview_today_show']    = strip_tags( $new_instance['pageview_today_show'] );
        $instance['pageview_today_adjust']  = (!empty($new_instance['pageview_today_adjust'])) ? preg_replace( '/[^0-9]/', '', $new_instance['pageview_today_adjust'] ) : 0;

        $instance['pageview_all_label']     = siga4w_strip_tags( $new_instance['pageview_all_label'] );
        $instance['pageview_all_show']      = strip_tags( $new_instance['pageview_all_show'] );
        $instance['pageview_all_adjust']    = (!empty($new_instance['pageview_all_adjust'])) ? preg_replace( '/[^0-9]/', '', $new_instance['pageview_all_adjust']) : 0;

        $instance['visit_today_label']  = siga4w_strip_tags( $new_instance['visit_today_label'] );
        $instance['visit_today_show']   = strip_tags( $new_instance['visit_today_show'] );
        $instance['visit_today_adjust'] = (!empty($new_instance['visit_today_adjust'])) ? preg_replace( '/[^0-9]/', '', $new_instance['visit_today_adjust']) : 0;

        $instance['visit_all_label']    = siga4w_strip_tags( $new_instance['visit_all_label'] );
        $instance['visit_all_show']     = strip_tags( $new_instance['visit_all_show'] );
        $instance['visit_all_adjust']   = (!empty($new_instance['visit_all_adjust'])) ? preg_replace( '/[^0-9]/', '', $new_instance['visit_all_adjust']) : 0;

        //clear transient
        siga4w_del_cache();

        return $instance;
    }

    /**
     *  @param int
     *  @return string
     */
    public function show_ajax_content($widget_id=0){

        if( empty($widget_id) ){
            return '--';
        }else{
            $widgets = get_option('widget_'.SIGA4W_WIDGET_ID);
            if( isset($widgets[$widget_id]) ){
                extract($widgets[$widget_id]);
            }else{
                return __( 'No matching record found.', 'sig-ga4-widget' );
            }
        }

        $_today = $this->get_today();
        $_all = $this->get_all();

        $nums = [
            'pageview_today'=> ( isset($_today['result']['screenPageViews']) ) ? $_today['result']['screenPageViews'] : 0,
            'pageview_all'  => ( isset($_all['result']['screenPageViews']) ) ? $_all['result']['screenPageViews'] : 0 ,
            'visit_today'   => ( isset($_today['result']['totalUsers']) ) ? $_today['result']['totalUsers'] : 0 ,
            'visit_all'     => ( isset($_all['result']['totalUsers']) ) ? $_all['result']['totalUsers'] : 0 ,
        ];

        $content = '<span data-today="'.( !empty($_today['time']) ? $_today['time']:'' ).'" data-all="'.( !empty($_all['time']) ? $_all['time']:'' ).'"></span>';

        //今日瀏覽
        if( isset($pageview_today_show) && $pageview_today_show == 'yes' ){

            if( empty($pageview_today_label) && !empty($this->def_val['pageview_today_label']) ) {
                $pageview_today_label = $this->def_val['pageview_today_label'];
            }

            $content .= sprintf( $pageview_today_label, number_format($nums['pageview_today'] += (int)$pageview_today_adjust) );
        }

        //累計瀏覽
        if( isset($pageview_all_show) && $pageview_all_show == 'yes' ){

            if( empty($pageview_all_label) && !empty($this->def_val['pageview_all_label']) ) {
                $pageview_all_label = $this->def_val['pageview_all_label'];
            }

            $content .= sprintf( $pageview_all_label, number_format($nums['pageview_all'] += (int)$pageview_all_adjust) );
        }

        //今日人氣
        if( isset($visit_today_show) && $visit_today_show == 'yes' ){

            if( empty($visit_today_label) && !empty($this->def_val['visit_today_label']) ) {
                $visit_today_label = $this->def_val['visit_today_label'];
            }

            $content .= sprintf( $visit_today_label, number_format($nums['visit_today'] += (int)$visit_today_adjust) );
        }

        //累計人氣
        if( isset($visit_all_show) && $visit_all_show == 'yes' ){

            if( empty($visit_all_label) && !empty($this->def_val['visit_all_label']) ) {
                $visit_all_label = $this->def_val['visit_all_label'];
            }

            $content .= sprintf( $visit_all_label, number_format($nums['visit_all'] += (int)$visit_all_adjust) );
        }

        return $content;
    }

    private function _mixKey($top_name,$array){
        foreach( $array as $key => $value ){
            if( is_array($value) ){
                $this->_mixKey($top_name.$key.'_',$value);
            }else{
                $this->def_val[$top_name.$key] = $value;
            }
        }
    }


    /**
     *  array(
     *       [result] => array(
     *           [totalUsers] => 123
     *           [screenPageViews] => 155
     *       )
     *       [time] => 12345678
     *   )
     */
    private function get_today(){

        if( !$data = get_transient('siga4w_get_today_cache') ){

            $options = get_option(SIGA4W_OPTION);

            $cache_time = ( isset($options['cache_time']) && $options['cache_time'] !== '' ) ? $options['cache_time'] : SIGA4W_CACHE_TIME;

            $ga4 = new SIGA4W_ga4($options['property_id']);
            $data = $ga4->getData();

            if( !isset($data['message']) ){
                $data = [
                    'result' => reset($data),
                    'time' => time()
                ];
                set_transient( 'siga4w_get_today_cache', $data, $cache_time );
            }
        }

        return $data;
    }

    /**
     *  array(
     *       [result] => array(
     *           [totalUsers] => 123
     *           [screenPageViews] => 155
     *       )
     *       [time] => 12345678
     *   )
     */
    private function get_all(){

        if( ! $data = get_transient('siga4w_get_all_cache') ){

            $options = get_option(SIGA4W_OPTION);

            $cache_time = ( isset($options['cache_time']) && $options['cache_time'] !== '' ) ? $options['cache_time'] : SIGA4W_CACHE_TIME;

            $begin_date = ( !empty($options['begin_date']) ) ? $options['begin_date'] : SIGA4W_BEGIN_DATE;

            $ga4 = new SIGA4W_ga4($options['property_id']);
            $data = $ga4->setDateRanges( $begin_date, 'today' )->setDimensionsName(['year'])->getData();

            if( !isset($data['message']) ){

                $dy = [];
                foreach($data as $year){
                    foreach($year as $item=>$nums){
                        $dy[$item] = ( isset($dy[$item]) ) ? ($dy[$item]+(int)$nums) : (int)$nums;
                    }
                }

                $data = [
                    'result' => $dy,
                    'time' => time()
                ];

                set_transient( 'siga4w_get_all_cache', $data, $cache_time );
            }

        }

        return $data;

    }



}

function siga4w_load_ga4_widgets(){
    register_widget( 'SIGA4W_widget' );
}

add_action( 'widgets_init', 'siga4w_load_ga4_widgets' );
