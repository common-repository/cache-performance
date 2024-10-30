<?php require_once('header.php'); ?>
<div class="wrapped">
    <div id="poststuff" class="metabox-holder ppw-settings">
        <div class="left-side">
            <div class="metabox-holder content-container">
                <div class="postbox-container wps-postbox-container">
                    <div class="meta-box-sortables ui-sortable">
                        <div class="postbox">
                            <div class="handlediv" title="Click to toggle">
                                <br/>
                            </div>
                          <div class="inside">
                                <!--            <div class="postbox" id="ppw_global_postbox">               -->
                                <div class="inside">

                                    <form id="companyID" method="post" action="<?php echo admin_url( 'admin.php?page=optimisationio-cache-settings' ); ?>">

                                            <div>
                                                <table cellpadding="0" cellspacing="0" border="0" width="600" class="form-table">
                                                    <tbody>
                                                        <tr height="50">
                                                            <td width="150" style="vertical-align: top;">
                                                                Cache Expiry :
                                                            </td>
                                                            <td>
                                                               <fieldset>
                                                                       <input type="text" name="cache_expires" id="cache_expires" value="<?php echo esc_attr($settings['cache_expires']) ?>" />
                                                                       <p class="description"> 0 = never expires. (Time in hours)</p>

                                                               </fieldset>
                                                            </td>
                                                        </tr>
                                                        <tr height="50">
                                                            <td  style="vertical-align: top;">
                                                                Compress cache:
                                                            </td>
                                                            <td>
                                                            <fieldset>
                                                               <input type="checkbox" name="cache_compress" id="cache_compress" value="1" <?php checked('1', $settings['cache_compress']); ?> />
                                                               <p class="description">Disable if the you notice any weird issues in browser.</p>
                                                               </fieldset>
                                                            </td>
                                                        </tr>
                                                        <tr height="50">
                                                            <td style="vertical-align: top;">
                                                                New Posts :
                                                            </td>
                                                            <td>
                                                            <fieldset>
                                                                <input type="checkbox" name="cache_new_post" id="cache_new_post" value="1" <?php checked('1', $settings['cache_new_post']); ?> />
                                                                <p class="description"> Wipe cache when you publish a new post</p>
                                                             </fieldset>
                                                            </td>
                                                        </tr>
                                                        <tr height="50" >
                                                            <td style="vertical-align: top;">
                                                                New comments :
                                                            </td>
                                                            <td>
                                                            <fieldset>
                                                               <input type="checkbox" name="cache_new_comment" id="cache_new_comment" value="1" <?php checked('1', $settings['cache_new_comment']); ?> />
                                                              <p class="description"> Wipe Cache when new comments posted</p>
                                                               </fieldset>
                                                            </td>
                                                        </tr>
                                                        <tr height="50">
                                                            <td  style="vertical-align: top;">
                                                                WebP Images:
                                                            </td>
                                                            <td>
                                                            <fieldset>
                                                               <input type="checkbox" name="cache_webp" id="cache_webp" value="1" <?php checked('1', $settings['cache_webp']); ?> />
                                                               <p class="description">Cache WebP images. See <a href=\"https://optmisation.io/\" target=\"_blank\">Optimisation.io</a> for more info </p>
                                                               </fieldset>
                                                            </td>
                                                        </tr>

                                                        <tr height="50">
                                                            <td style="vertical-align: top;">
                                                                Exclusions :
                                                            </td>
                                                            <td>
                                                            <fieldset>
                                                             <input type="text" name="excl_ids" id="excl_ids" value="<?php echo esc_attr($settings['excl_ids']) ?>" />
                                                             <p class="description">Post or Pages IDs separated by a <code>,</code></p>
                                                             </fieldset>
                                                            </td>
                                                        </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                            <input class="button-primary" type="submit" value="Update" name="submit"/>

                                    </form>
                                </div>
                                <!--            </div>   -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


</div>

<?php require_once('sidebar.php'); ?>
    <div style="clear: both"></div>
    </div>
