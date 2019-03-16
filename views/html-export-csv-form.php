<?php
/**
 * Admin View: Download Reports Form
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div class="wrap woocommerce">
    <form method="post" id="mainform" action="" enctype="multipart/form-data">
        <h2> Download Monthly Reports</h2>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="job_report_month">
                            Month
                        </label>
                    </th>
                    <td>
                        <?php $this->month_selector(); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="job_report_year">
                            Year
                        </label>
                    </th>
                    <td>
                        <?php $this->year_selector(); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input name="wpjr_export_csv" class="button-primary save-button" type="submit" value="Download" />
            <?php wp_nonce_field( 'wpjr-export-csv' ); ?>
        </p>
    </form>
</div>
