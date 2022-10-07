<?php

/**
 *  https://github.com/googleapis/php-analytics-data
 *
 *  https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema
 */

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Filter;
use Google\Analytics\Data\V1beta\FilterExpression;

class SIGA4W_ga4 {

    private $property;

    private $dateRanges = [];

    private $dimensions = [];

    private $dimensions_list = [];

    private $metrics = [];

    private $metrics_list = [ 'totalUsers', 'screenPageViews' ];

    private $limit = 0;

    private $dimensionFilter;

    function __construct($property_id='0') {

        $this->property = "properties/{$property_id}";
    }

    /**
     *  @return array
     */
    public function getData(){

        if(empty($this->dimensions)) $this->setDimensionsName(['date']);
        if(empty($this->dateRanges)) $this->setDateRanges('today','today');
        if(empty($this->metrics)) $this->setMetricsName($this->metrics_list);

        $args = [
            'property'      => $this->property,
            'dateRanges'    => $this->dateRanges,
            'dimensions'    => $this->dimensions,
            'metrics'       => $this->metrics,
        ];

        if( !empty($this->dimensionFilter) )  $args['dimensionFilter'] = $this->dimensionFilter;

        if( $this->limit > 0 ) $args['limit'] = $this->limit;


        try {
            $client = new BetaAnalyticsDataClient();

            $response = $client->runReport($args);

            $data = [];

            $dimensionVars = [];
            foreach($response->getDimensionHeaders() as $i){
                $dimensionVars[] = $i->getName();
            }

            $metricVars = [];
            foreach($response->getMetricHeaders() as $i){
                $metricVars[] = $i->getName();
            }

            foreach ($response->getRows() as $row) {

                if( count($row->getDimensionValues()) > 1 ){

                    $item = [];

                    foreach( $row->getDimensionValues() as $index=>$d ){
                        $item[$dimensionVars[$index]] = esc_attr( $d->getValue() );
                    }

                    foreach( $metricVars as $index=>$name ){
                        $item[$name] = esc_attr( $row->getMetricValues()[$index]->getValue() );
                    }

                    $data[] = $item;
                }else{

                    $key = esc_attr( $row->getDimensionValues()[0]->getValue() );
                    $data[$key] = [];

                    foreach( $metricVars as $index=>$name ){
                        $data[$key][$name] = esc_attr( $row->getMetricValues()[$index]->getValue() );
                    }

                }
            }

            return $data;

        }catch (Exception $e) {

            // [message] => xxxxxxxx. [code] => 3 [status] => INVALID_ARGUMENT [details] => ''
            siga4w_log('########## sig-ga4-widget ##########');
            siga4w_log($e->getMessage());
            siga4w_log('####################################');

            if( siga4w_isJson($e->getMessage()) ){
                return json_decode( $e->getMessage(), true );
            }else{
                return [ 'message'=> $e->getMessage() ];
            }
        }
    }

    /**
     *  Must be YYYY-MM-DD, NdaysAgo, yesterday, or today.
     *  @param string $start_date
     *  @param string $end_date
     */
    public function setDateRanges($start_date,$end_date){

        $dateRange = new DateRange();
        $this->dateRanges[] = $dateRange->setStartDate($start_date)->setEndDate($end_date);
        return $this;
    }

    /**
     *  date, month, year, city, pageTitle, pagePath, fullPageUrl...
     *  @param array
     */
    public function setDimensionsName($array=array()){

        $this->dimensions_list = $array;

        foreach($array as $name){
            $dimension = new Dimension();
            $this->dimensions[] = $dimension->setName($name);
        }
        return $this;
    }

    /**
     *  totalUsers, screenPageViews, newUsers
     *  @param array
     */
    public function setMetricsName($array=array()){

        $this->metrics_list = $array;

        foreach($array as $name){
            $metric = new Metric();
            $this->metrics[] = $metric->setName($name);
        }
        return $this;
    }

    /**
     *  @param int
     */
    public function setLimit($numbers=0){

        $this->limit = $numbers;

        return $this;
    }

    /**
     *  @param string
     */
    public function setDimensionFilter($page_path=''){

        $stringFilter = new Filter\StringFilter();
        $stringFilter->setMatchType(Filter\StringFilter\MatchType::BEGINS_WITH);
        $stringFilter->setValue($page_path);

        $filter = new Filter();
        $filter->setFieldName('pagePath');
        $filter->setStringFilter($stringFilter);

        $this->dimensionFilter = new FilterExpression();
        $this->dimensionFilter->setFilter($filter);

        return $this;
    }


}