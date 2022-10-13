<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap">
    <h2><?php _e( 'Setup', 'sig-ga4-widget' )?></h2>
    <hr>
    <div class="container-fluid">
        <div class="row">
            <!-- left -->
            <div class="col-12 col-sm-8">
                <form method="post" action="options.php" enctype="multipart/form-data">

                    <?php settings_fields(SIGA4W_OPTION_GROUP); ?>

                    <table class="form-table">

                        <tr valign="top">
                            <th scope="row"><?php _e( 'Upload key file', 'sig-ga4-widget' )?></th>
                            <td>
                                <?php if( !empty($this->options['json_key']) && is_file($this->options['json_key']) ): ?>
                                    <input type="hidden" name="<?php echo SIGA4W_OPTION.'[json_key]'?>" value="<?php echo esc_attr($this->options['json_key'])?>">
                                    <?php
                                        $file = pathinfo($this->options['json_key']);
                                        /* translators: %s is filename  */
                                        echo sprintf( __( 'Your key file: %s', 'sig-ga4-widget' ) , $file['basename'] );
                                    ?><p><button id="btn-del-file" class="button" type="button" name=""><?php _e( 'Delete file', 'sig-ga4-widget' )?></button></p>
                                <?php else: ?>
                                    <input type="file" class="regular-text" name="json_key" value="" />
                                    <p class="description"><?php
                                        _e( 'Could not find the key file.', 'sig-ga4-widget' );
                                    ?></p>
                                <?php endif; ?>

                                </p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e( 'Website property id', 'sig-ga4-widget' )?></th>
                            <td><input type="text" class="" name="<?php echo SIGA4W_OPTION?>[property_id]" value="<?php if(!empty($this->options['property_id'])) echo esc_attr($this->options['property_id']); ?>" />
                            <p class="description"><?php _e( 'You can find property id numbers at Google Analytics property like  <b>webname(12345678)</b>.', 'sig-ga4-widget' )?></p></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e( 'GA data start date', 'sig-ga4-widget' )?></th>
                            <td><input type="date" class="" name="<?php echo SIGA4W_OPTION?>[begin_date]" value="<?php echo (!empty($this->options['begin_date'])) ? esc_attr($this->options['begin_date']):SIGA4W_BEGIN_DATE; ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e( 'Cache time (second)', 'sig-ga4-widget' )?></th>
                            <td>
                                <input type="number" class="" name="<?php echo SIGA4W_OPTION?>[cache_time]" min="0" value="<?php
                                echo isset($this->options['cache_time']) ? esc_attr($this->options['cache_time']) : SIGA4W_CACHE_TIME ; ?>" onkeyup="value=value.replace(/[^\d]/g,'')" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))"/>
                                <p><?php
                                    /* translators: %d is seconds */
                                    echo sprintf( __( 'Set the cache time to avoid repeated API calls and speed up page loading. (Default time: %d seconds)', 'sig-ga4-widget' ), SIGA4W_CACHE_TIME );
                                ?></p>
                            </td>
                        </tr>

                    </table>

                    <h2 class="title"><?php _e( 'Post pageviews', 'sig-ga4-widget' )?>&nbsp;<span style="font-size: 13px;"><?php _e( '( Notice! Each post will call the api once. )', 'sig-ga4-widget' )?></span></h2>

                    <table class="form-table">

                        <tr valign="top">
                            <th scope="row"><?php _e( 'Pageview label text', 'sig-ga4-widget' )?></th>
                            <td><input type="text" class="regular-text" name="<?php echo SIGA4W_OPTION?>[post_pv_label]" value="<?php if ( !empty($this->options['post_pv_label']) ) echo esc_attr($this->options['post_pv_label']); ?>" />
                                <p><?
                                    _e( 'Default label', 'sig-ga4-widget' );
                                    echo '<code>' . esc_attr($this->def_pv_label) . '</code>';
                                ?></p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e( 'Show on', 'sig-ga4-widget' )?></th>
                            <td>
                                <?php
                                $post_types = siga4w_get_post_types();
                                foreach( $post_types as $type => $name ){
                                ?>
                                <label style="margin-right: 20px;"><input type="checkbox" name="<?php echo SIGA4W_OPTION."[post_pv][{$type}]"?>" value="yes" <?php checked( 'yes', (isset($this->options['post_pv'][$type])) ? $this->options['post_pv'][$type]:'' )?> /> <?php echo $name; ?></label>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e( 'Position', 'sig-ga4-widget' )?></th>
                            <td>
                                <?php $post_pv_pos = ( !empty($this->options['post_pv_pos']) ) ? $this->options['post_pv_pos']:''; ?>
                                <select size="1" name="<?php echo SIGA4W_OPTION."[post_pv_pos]"?>">
                                    <option value="top-left" <?php selected( 'top-left', $post_pv_pos );?>><?php _e( 'Top Left', 'sig-ga4-widget' ); ?>&nbsp;(↖)</option>
                                    <option value="top-center" <?php selected( 'top-center', $post_pv_pos );?>><?php _e( 'Top Center', 'sig-ga4-widget' ); ?>&nbsp;(↑)</option>
                                    <option value="top-right" <?php selected( 'top-right', $post_pv_pos );?>><?php _e( 'Top Right', 'sig-ga4-widget' ); ?>&nbsp;(↗)</option>
                                    <option value="bottom-left" <?php selected( 'bottom-left', $post_pv_pos );?>><?php _e( 'Bottom Left', 'sig-ga4-widget' ); ?>&nbsp;(↙)</option>
                                    <option value="bottom-center" <?php selected( 'bottom-center', $post_pv_pos );?>><?php _e( 'Bottom Center', 'sig-ga4-widget' ); ?>&nbsp;(↓)</option>
                                    <option value="bottom-right" <?php selected( 'bottom-right', $post_pv_pos );?>><?php _e( 'Bottom Right', 'sig-ga4-widget' ); ?>&nbsp;(↘)</option>
                                </select>
                            </td>
                        </tr>


                    </table>

                    <?php submit_button(); ?>

                </form>

            </div>
            <!-- //left -->

            <!-- right -->
            <div class="col-12 col-sm-4">
                <div style="background-color: #fff;padding:5px 15px; line-height: 1.5;">
                    <h2><?php _e( 'Prompt:', 'sig-ga4-widget' ); ?></h2>
                    <p><?php _e( 'Caution! This plug-in uses the Google Analytics Data API, and there is a daily limit on the number of calls (50,000). If the number of requests exceeds, you may need to pay for it. (You can set the cache time in the settings to avoid exceeding the number of calls)', 'sig-ga4-widget' )?></p>

                    <h2><?php _e( 'How to setup:', 'sig-ga4-widget' ); ?></h2>
                    <ol>
                        <li>登入 https://console.cloud.google.com/apis/ 。點左側選單<b>「已啟用的API和服務」</b>：啟用「Google Analytics Data API」服務（已啟用過的就不需此步驟）。</li>
                        <li>點左側選單<b>「憑證」</b>：點<b>「+建立憑證」</b> -> 選<b>「服務帳戶」</b>（輸入服務帳戶名稱、服務帳戶ID，並記住該員電子郵件地址，點<b>「建立並繼續」</b>到下一步）。</li>
                        <li>選擇角色：選<b>「App Engine 管理員」</b>-> 點<b>「繼續」</b>，再點<b>「完成」</b>。在<b>「服務帳戶」</b>清單，點該員電子郵件進入。點<b>「金鑰」</b> -> 點<b>「新增金鑰」</b> -> 選<b>「建立新的金鑰」</b>，金鑰類型選<b>「json」</b>並保存下載檔案。</li>
                        <li>登入 https://analytics.google.com/ 。點左下方<b>「管理」</b>，然後前往所需的帳戶/資源。點入<b>「帳戶存取管理」</b>中，按一下 [+] ，然後點選<b>「新增使用者」</b>，將剛剛建立的服務帳戶電子郵件新增進去（角色選檢視者即可）。</li>
                        <li>如果您資料建立正確，將會在選單「<a href="<?php echo admin_url('admin.php?page=siga4w_page')?>"><?php _e('GA4 widget config', 'sig-ga4-widget')?></a>」頁面中看到 Google Analytics 的過去統計資料。
                    </ol>
                </div>
            </div>
            <!-- //right -->

        </div>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function($) {
        $('#btn-del-file').on('click',function(){
            if(confirm("<?php _e( 'Are you sure you want to delete this file?', 'sig-ga4-widget' )?>")){
                $.post( '<?php echo admin_url('admin-ajax.php?action=siga4w-delete')?>' , { _ajax_nonce: '<?php echo wp_create_nonce('delete_json_file');?>' } ).done( function( rs ) {
                    alert( rs.message );
                    if( rs.success ) location.reload();
                });

            }else{
            	return false;
            }
        });
    });

</script>