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
                                    <form id="companyID" method="post" action="<?php echo admin_url( 'admin.php?page=optimisationio-update-cdn-enabler' ); ?>">

                                            <div>
                                                <table cellpadding="0" cellspacing="0" border="0" width="600" class="form-table">
                                                    <tbody>
                                                        <tr height="50">
                                                            <td  style="vertical-align: top;">
                                                               CDN Path
                                                            </td>
                                                            <td>
                                                               <fieldset>
                                                                       <input type="text" name="cdn_root_url" id="cdn_root_url" value="<?php echo esc_attr($settings['cdn_root_url']) ?>" />
                                                                </fieldset>
                                                            </td>
                                                        </tr>

                                                        <tr height="50">
                                                            <td  style="vertical-align: top;">
                                                               Image and file extensions
                                                            </td>
                                                            <td>
                                                               <fieldset>
                                                                       <input type="text" name="cdn_file_extensions" id="cdn_file_extensions" value="<?php echo esc_attr($settings['cdn_file_extensions']) ?>" />
                                                                </fieldset>
                                                            </td>
                                                        </tr>
                                                        <tr height="50">
                                                            <td style="vertical-align: top;">
                                                                CSS CDN Path
                                                            </td>
                                                            <td>
                                                            <fieldset>
                                                                <input type="text" name="cdn_css_root_url" id="cdn_css_root_url" value="<?php echo esc_attr($settings['cdn_css_root_url']) ?>" />
                                                             </fieldset>

                                                            </td>
                                                        </tr>
                                                        <tr height="50">
                                                            <td style="vertical-align: top;">
                                                                File Extensions for CSS
                                                            </td>
                                                            <td>
                                                            <fieldset>
                                                                <input type="text" name="cdn_css_file_extensions" id="cdn_css_file_extensions" value="<?php echo esc_attr($settings['cdn_css_file_extensions']) ?>" />
                                                             </fieldset>
                                                            </td>
                                                        </tr>

                                                        <tr height="50">
                                                            <td style="vertical-align: top;">
                                                                JS Files CDN Path
                                                            </td>
                                                            <td>
                                                            <fieldset>
                                                               <input type="text" name="cdn_js_root_url" id="cdn_js_root_url" value="<?php echo esc_attr($settings['cdn_js_root_url']); ?>" />
                                                             </fieldset>
                                                            </td>
                                                        </tr>

                                                        <tr height="50">
                                                            <td style="vertical-align: top;">
                                                             File Extensions for JS
                                                            </td>
                                                            <td>
                                                            <fieldset>
                                                               <input type="text" name="cdn_js_file_extensions" id="cdn_js_file_extensions" value="<?php echo esc_attr($settings['cdn_js_file_extensions']); ?>" />
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
