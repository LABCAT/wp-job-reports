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

            // header( 'Content-type: text/csv' );
            // header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
            // header( 'Pragma: no-cache' );
            // header( 'Expires: 0' );

            $file = fopen( 'php://output', 'w' );


            $headings = [
                'job_title',
                'job_id',
                'number_of_views'
            ];


            //populate CSV headings
            fputcsv(
                $file,
                $headings
            );

            global $wpdb;
            $sql = $wpdb->prepare( 
                'SELECT posts.ID, posts.post_title, jr_counter_total.postcount 
                FROM ' . $wpdb->prefix .'jr_counter_total AS jr_counter_total
                INNER JOIN ' . $wpdb->prefix . 'posts AS posts
                ON jr_counter_total.postnum = posts.ID'
            );

            $results = $wpdb->get_results($sql);

            // echo '<pre>';
            // print_r(count($results));
            // echo '</pre>';
            // die('sdfsdf');
            
            //populate CSV with a row for each job
            foreach ( $jobs as $job ) {

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
