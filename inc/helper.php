<?php

/**
 *  Get cache time
 *  @return int
 */
function siga4w_get_cache_time(){
    $options = get_option(SIGA4W_OPTION);
    return ( isset($options['cache_time']) && $options['cache_time'] !== '' ) ? $options['cache_time'] : SIGA4W_CACHE_TIME;
}

/**
 *  Get GA4 data
 *  @return array
 */
function siga4w_get_data($args=[],$cache_name=''){

    $data = false;

    if( $cache_name !== '' ){
        $data = get_transient($cache_name);
    }

    if( !$data && class_exists('SIGA4W_ga4') ){

        $options = get_option(SIGA4W_OPTION);

        $ga4 = new SIGA4W_ga4($options['property_id']);

        if( !empty($args['metrics']) )  $ga4->setMetricsName($args['metrics']);
        if( !empty($args['dimensions']) )  $ga4->setDimensionsName( $args['dimensions'] );
        if( !empty($args['dateRange']) )  $ga4->setDateRanges($args['dateRange'][0],$args['dateRange'][1]);
        if( !empty($args['dimensionFilter']) )  $ga4->setDimensionFilter($args['dimensionFilter']);
        if( !empty($args['limit']) )  $ga4->setLimit($args['limit']);

        $data = $ga4->getData();

        if( $cache_name !== '' &&  !isset($data['message']) ) {
            set_transient( $cache_name, $data, siga4w_get_cache_time() );
        }

    }

    return $data;
}



/**
 *  Covert time to local time & format
 *  @param string
 *  @param string
 *  @return string
 */
function siga4w_local_time($time='',$format='Y-m-d H:i:s'){

    if( empty($time) ) return;

    $dt = new DateTime();
    $dt->setTimezone(new DateTimeZone( get_option('timezone_string') ));
    $dt->setTimestamp($time);
    return $dt->format($format);
}

/**
 *  custom strip_tags, allow <b><p><div><ul><li><br>
 *  @param string
 */
function siga4w_strip_tags($string='') {

    if( !empty($string) ){
        $string= strip_tags($string,'<i><b><p><div><ul><li><br>');
    }
    return $string;
}


/**
 *  only post: page & custom post type
 */
function siga4w_get_post_types(){

    $post_types = get_post_types([
        'public'   => true,
        ], 'objects');

    $posts = [];
    $exclude = [ 'attachment', 'elementor_library', 'e-landing-page' ];

    foreach ($post_types as $post_type) {
        if( !in_array( $post_type->name, $exclude )) {
            $posts[$post_type->name] = $post_type->labels->singular_name;
        }
    }

    return $posts;
}

/**
 *
 */
function siga4w_day_loop($dateString='1 day',$day=array(),$format='Y-m-d'){

    $dt = new DateTime();

    if(empty($day[0])) $day[0] = $dt->format("Y-m-1");
    if(empty($day[1])) $day[1] = $dt->format("Y-m-t");

    $begin = new DateTime($day[0]);
    $end = new DateTime($day[1]);

    $interval = DateInterval::createFromDateString($dateString);
    $period = new DatePeriod($begin, $interval, $end);

    $days = [];
    foreach ($period as $dt) {
        $days[] =$dt->format($format);
    }

    return $days;
}

/**
 *  check is json
 */

function siga4w_isJson($string) {
   json_decode($string);
   return json_last_error() === JSON_ERROR_NONE;
}

/**
 *  @since 1.0.1
 */
function siga4w_del_cache(){
    delete_transient('siga4w_get_today_cache');
    delete_transient('siga4w_get_all_cache');
}

/**
 *  Add log
 */
if (!function_exists('write_log')) {

    function siga4w_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }

}