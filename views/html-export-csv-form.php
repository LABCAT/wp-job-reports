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
                    <td>
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
                    </td>
                    <td>
                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="col">
                                        <label for="job_export_fields">
                                            Job Fields
                                        </label>
                                    </th>
                                    <th scope="col">
                                        <label for="order_export_fields">
                                            Order Fields
                                        </label>
                                    </th>
                                </tr>
                                <tr valign="top">
                                    <td>
                                        <?php $this->job_export_fields(); ?>
                                    </td>
                                    <td>
                                        <?php $this->order_export_fields(); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
