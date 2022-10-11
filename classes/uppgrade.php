<?php

/**
 *
 */

class SIGA4W_upgrade {

    public $options;

    public $opt_version;

    public function __construct($options){

        $this->options = $options;

        $this->opt_version = (!empty($options['version'])) ? $options['version'] : '1.0';
    }

    /**
     *
     */
    public  function run() {

        if( version_compare( $this->opt_version, '1.0', '==' ) ) {

            $new_widgets = $widgets = get_option('widget_'.SIGA4W_WIDGET_ID);

            if( is_array($widgets) && count($widgets) > 0 ){
                foreach( $widgets as $id => $widget ){
                    if(is_int($id)){
                        foreach( $widget as $key => $val ){
                            if( strpos($key,'_label') !== false &&  strpos($val,'%d') !== false ){
                                $new_widgets[$id][$key] = str_replace('%d', '%s', $val);
                            }
                        }
                    }
                }
            }

            if( $widgets != $new_widgets ) {
                update_option( 'widget_'.SIGA4W_WIDGET_ID, $new_widgets );
            }
        }


        // update version number
        if( version_compare( $this->opt_version, SIGA4W_VERSION, '<' ) ) {
            $this->options['version'] = SIGA4W_VERSION;
            update_option( SIGA4W_OPTION, $this->options );
        }

    }

}