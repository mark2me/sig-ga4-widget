<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6" style="margin-bottom: 30px;">

            <?php
                $theMonth = siga4w_get_data( [
                    'dateRange' => [ date("Y-m-01"), date("Y-m-t") ]
                    ], 'cache_theMonth' );
            ?>
            <h3><?php _e( 'Current month', 'sig-ga4-widget' ) ?></h3>
            <div id="ga4_daily_chart" style="width:100%;height: 250px;background-color: #fff;"><?php
                if( isset($theMonth['message']) ) echo $theMonth['message'];
            ?></div>
            <i><?php
                /* translators: %1$s is start date, %2$s is end date. */
                echo sprintf( __( 'Date range: %1$s ~ %2$s', 'sig-ga4-widget' ), date("Y-m-01"), date("Y-m-t") );
            ?></i>

        </div>

        <div class="col-md-6" style="margin-bottom: 30px;">

            <?php
                $theYear = siga4w_get_data( [
                    'dateRange' => [ date("Y-01-01"), date("Y-m-t") ],
                    'dimensions' => ['month'],
                    ], 'cache_theYear' );
            ?>
            <h3><?php _e( 'Current year' , 'sig-ga4-widget' ) ?></h3>
            <div id="ga4_month_chart" style="width:100%;height: 250px;background-color: #fff;"><?php
                if( isset($theYear['message']) ) echo $theYear['message'];
            ?></div>
            <i><?php
                /* translators: %1$s is start date, %2$s is end date. */
                echo sprintf( __( 'Date range: %1$s ~ %2$s', 'sig-ga4-widget' ), date("Y-01-01"), date("Y-12-31") );
            ?></i>

        </div>


        <div class="col-md-6" style="margin-bottom: 30px;">
            <?php
                $theHot = siga4w_get_data( [
                    'dateRange' => [ '7daysAgo', 'today' ],
                    'dimensions' => ['pageTitle', 'fullPageUrl'],
                    'metrics' => ['screenPageViews'],
                    'limit' => 10
                    ], 'cache_hot' );
            ?>
            <h3><?php _e( 'Popular Posts (last 7 days)', 'sig-ga4-widget' ) ?></h3>
            <table class="wp-list-table widefat striped table-view-list">
            <thead>
                <tr>
                    <th align="center">No.</th>
                    <th><?php _e( 'Page title', 'sig-ga4-widget' )?></th>
                    <th><?php _e( 'Pageviews', 'sig-ga4-widget' )?></th>
                </tr>
            </thead>
            <tbody>
            <?php
                if( is_array($theHot) && count($theHot)>0 ){
                    foreach( $theHot as $k => $rs) {
                        echo '<tr><td align="center">'.($k+1).'</td><td><a href="https://'.$rs['fullPageUrl'].'" target="_blank">'.$rs['pageTitle'].'</a></td><td align="center">'.$rs['screenPageViews'].'</td></tr>';
                    }
                }
            ?>
            </tbody>
            </table>
        </div>

    </div>
</div>


<script type="text/javascript">
<?php if( !isset($theMonth['message']) ):

    $day_loop = siga4w_day_loop('1 day', [date("Y-m-01"),date("Y-m-t")]);
    $days = [];
    foreach( $day_loop as $d){
        $day = str_replace("-","",$d);
        $days[] = [
            'x' => $d,
            'a' => ( !empty($theMonth[$day]['screenPageViews']) ) ? $theMonth[$day]['screenPageViews'] : 0,
            'b' => ( !empty($theMonth[$day]['totalUsers']) ) ? $theMonth[$day]['totalUsers'] : 0,
        ];
    }
?>
    new Morris.Line({
        element: 'ga4_daily_chart',
        data: <?php echo json_encode($days); ?>,
        xkey: 'x',
        xLabels: 'day',
        xLabelFormat: function(x) {
            return (x.getMonth()+1) + '/' + x.getDate();
        },
        ykeys: ['a','b'],
        labels: ['Pageviews','Visits'],
        fillOpacity: 1.0,
        resize: true
    });
<?php endif; ?>

<?php if( !isset($theYear['message']) ):

    $month_loop = siga4w_day_loop('1 month', [date("Y-01-01"),date("Y-12-31")], 'm');
    $months = [];
    foreach( $month_loop as $m){

        $months[] = [
            'x' => date('Y')."-".$m,
            'a' => ( !empty($theYear[$m]['screenPageViews']) ) ? $theYear[$m]['screenPageViews'] : 0,
            'b' => ( !empty($theYear[$m]['totalUsers']) ) ? $theYear[$m]['totalUsers'] : 0,
        ];
    }

?>
    new Morris.Line({
        element: 'ga4_month_chart',
        data: <?php echo json_encode($months); ?>,
        xkey: 'x',
        xLabels: 'month',
        xLabelFormat: function(x) {
            return x.getFullYear() + '/' + (x.getMonth()+1);
        },
        ykeys: ['a','b'],
        labels: ['Pageview','Visits'],
        fillOpacity: 1.0,
        resize: true
    });
<?php endif; ?>
</script>