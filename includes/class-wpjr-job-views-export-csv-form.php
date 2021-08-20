<?php
/**
 * WPJR_Job_Views_Export_CSV_Form
 *
 * @class     WPJR_Job_Views_Export_CSV_Form
 * @version   1.0.0
 * @package   WP_Job_Reports/Admin
 * @category  Class
 * @author   My Site Digital
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPJR_Job_Views_Export_CSV_Form', false ) ) {

    /**
     * WPJR_Job_Views_Export_CSV_Form Class.
     *
     */
    class WPJR_Job_Views_Export_CSV_Form {

        /**
         * Constructor.
         */
        public function __construct() {
            add_action( 'init', [ $this, 'init' ], 999 );
            add_action( 'admin_menu', [ $this, 'add_settings_page_to_jobs_submenu' ], 999 );
        }

        public function init(){
            if ( isset( $_POST[ 'wpjr_job_views_export_csv' ] ) ) {
                $this->download_csv();
            }
        }

        public function download_csv(){
            if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpjr-export-csv' ) ) {
                die( 'Action failed. Please refresh the page and retry.' );
            }

            $domain = explode( ".", parse_url( site_url(), PHP_URL_HOST ) );
            $domain = reset( $domain );
            $filename = 'wp-job-views-report-'.  $domain . '.csv';

            header( 'Content-type: text/csv' );
            header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );

            $file = fopen( 'php://output', 'w' );


            $headings = [
                'job_id',
                'job_title',
                'publish_date',
                'expire_date',
                'url',
                'number_of_views'
            ];


            //populate CSV headings
            fputcsv(
                $file,
                $headings
            );

            global $wpdb;
            $jobs = $wpdb->get_results(
                 'SELECT posts.ID, posts.post_title, posts.post_date, posts.guid, jr_counter_total.postcount 
                FROM ' . $wpdb->prefix . 'posts AS posts
                LEFT JOIN ' . $wpdb->prefix . 'jr_counter_total AS jr_counter_total
                ON jr_counter_total.postnum = posts.ID
                WHERE posts.post_type = "job_listing"
                AND posts.post_status = "publish"
                ORDER BY posts.ID DESC'
            );
            
            
            //populate CSV with a row for each job
            foreach ( $jobs as $job ) {
                $duration = get_post_meta( $job->ID, JR_JOB_DURATION_META, true );
                $job_data_array = [];
                $job_data_array[] = $job->ID;
                $job_data_array[] = $job->post_title;
                $job_data_array[] = appthemes_display_date(strtotime($job->post_date));
                $job_data_array[] = jr_get_expiration_date( $job->post_date, $duration );
                $job_data_array[] = htmlspecialchars_decode($job->guid);
                $job_data_array[] = $job->postcount;
                fputcsv(
                   $file,
                   $job_data_array
                );
            }

            exit();
        }

        public function add_settings_page_to_jobs_submenu(){
            add_submenu_page(
                'edit.php?post_type=job_listing',
                'Job Views Report',
                'Job Views Report',
                'manage_options',
                'job_views_report',
                [ $this, 'output' ]
            );
        }

        public function output(){
            include_once( JOB_REPORTS_PLUGIN_DIR . '/views/html-job-views-export-csv-form.php' );
        }

    }

}

$export_form = new WPJR_Job_Views_Export_CSV_Form();
