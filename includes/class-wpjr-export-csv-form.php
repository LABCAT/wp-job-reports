<?php
/**
 * WPJR_Export_CSV_Form
 *
 * @class     WPJR_Export_CSV_Form
 * @version   1.0.0
 * @package   WP_Job_Reports/Admin
 * @category  Class
 * @author   My Site Digital
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WPJR_Export_CSV_Form', false ) ) {

    /**
     * WPJR_Export_CSV_Form Class.
     *
     */
    class WPJR_Export_CSV_Form {

        private $current_month = 1;

        private $current_year = 2000;

        private $job_export_fields = [
            'ID'                 => 'ID',
            'post_title'         => 'Title',
            'post_content'       => 'Description',
            'post_author'        => 'Author',
            'post_date'          => 'Date',
            'post_status'        => 'Status',
            '_Company'           => 'Company',
            '_CompanyURL'        => 'Website',
            '_how_to_apply'      => 'How To Apply',
            'job_type'           => 'Job Type',
            'job_cat'            => 'Job Category',
            'job_salary'         => 'Job Salary',
            'geo_address'        => 'Location',
            '_jr_job_duration'   => 'Job Duration'
        ];

        private $order_export_fields = [
            'OrderID'            => 'Order ID',
            'order_status'       => 'Order Status',
            'order_description'  => 'Order Description',
            'total_price'        => 'Order Total',
            'gateway'            => 'Payment Gateway'
        ];

        /**
         * Constructor.
         */
        public function __construct() {
            add_action( 'init', [ $this, 'init' ], 999 );
            add_action( 'admin_init', [ $this, 'register_settings' ] );
            add_action( 'admin_menu', [ $this, 'add_settings_page_to_payments_submenu' ], 999 );
        }

        public function init(){
            $this->current_month = isset( $_POST[ 'job_report_month' ] ) ? intval( $_POST[ 'job_report_month' ] ) : date( 'm' );
            $this->current_year = isset( $_POST[ 'job_report_year' ] ) ? intval( $_POST[ 'job_report_year' ] ) : date( 'Y' );

            if ( isset( $_POST[ 'wpjr_export_csv' ] ) ) {
                $this->download_csv();
            }
        }

        /**
         * Registers the settings available
         */
        public static function register_settings() {
            register_setting( 'wpjr-settings', 'job_export_fields' );
            if( get_option( 'job_export_fields' ) === false ) {
                update_option( 'job_export_fields', $this->job_export_fields, 'no' );
            }
            register_setting( 'wpjr-settings', 'order_export_fields' );
            if( get_option( 'order_export_fields' ) === false ) {
                update_option( 'order_export_fields', $this->order_export_fields, 'no' );
            }
        }

        public function download_csv(){
            if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wpjr-export-csv' ) ) {
                die( 'Action failed. Please refresh the page and retry.' );
            }

            $domain = explode( ".", parse_url( site_url(), PHP_URL_HOST ) );
            $domain = reset( $domain );
            $filename = 'wp-job-report-'.  $domain . '-' . strtolower( $this->month_name( $this->current_month ) ) . '-' .  $this->current_year . '.csv';

            header( 'Content-type: text/csv' );
            header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );

            $file = fopen( 'php://output', 'w' );

            $export_fields = array_merge( $this->job_export_fields, $this->order_export_fields );

            //populate CSV headings
            fputcsv(
                $file,
                array_values( $export_fields )
            );

            //get job data for particular month
            $args = [
                'post_type' => 'job_listing',
                'post_status' => 'any',
                'posts_per_page' => -1,
                'date_query' => [
                    [
                        'year'  => $this->current_year,
                        'month' => $this->current_month
                    ],
                ],
            ];

            $jobs = get_posts( $args );

            //populate CSV with a row for each job
            foreach ( $jobs as $job ) {
                $job_data_array = [];
                $meta_data = get_post_meta( $job->ID );
                $term_data = $this->get_term_data( $job->ID );
                $order_data = $this->get_order_data( $job->ID );

                foreach ( array_keys( $export_fields ) as $key ) {
                    if( isset( $job->$key ) ){
                        $value = $job->$key;
                        if( $key === 'post_author' ){
                            $value = get_the_author_meta( 'display_name', $job->$key );
                        }
                        $job_data_array[] = $value;
                    }
                    else if( isset( $meta_data[ $key ] ) ){
                        $job_data_array[] = $meta_data[ $key ];
                    }
                    else if( isset( $term_data[ $key ] ) ){
                        $job_data_array[] = $term_data[ $key ];
                    }
                    else if( isset( $order_data[ $key ] ) ){
                        $job_data_array[] = $order_data[ $key ];
                    }
                    else {
                        $job_data_array[] = '';
                    }
                }

                fputcsv(
                   $file,
                   $job_data_array
                );
            }

            exit();
        }

        public function get_term_data( $job_id ){
            $term_data = [];

            $terms = wp_get_post_terms( $job_id, [ 'job_cat', 'job_type', 'job_salary' ] );
            if( ! is_wp_error( $terms ) ){
                foreach( $terms as $term ){
                    $term_data[ $term->taxonomy ] = $term->name;
                }
            }
            return $term_data;
        }

        public function get_order_data( $job_id ){
            $order_data = [];

            $connected = new WP_Query(
                [
                    'connected_to' => $job_id,
                    'connected_type' => APPTHEMES_ORDER_CONNECTION,
                    'connected_query' => ['post_status' => 'any' ],
                    'post_status' => 'any',
                    'nopaging' => true,
                ]
            );

            if ( $connected->post ){
                $order = $connected->post;
                $order_meta = get_post_meta( $order->ID );
                $order_data[ 'OrderID' ] = $order->ID;
                $order_data[ 'order_status' ] = $order->post_status;
                $order_data[ 'order_description' ] = $order->post_title;
                //get first element in array
                $order_data[ 'total_price' ] = reset( $order_meta[ 'total_price' ] );
                $order_data[ 'gateway' ] = reset( $order_meta[ 'gateway' ] );
            }
            return $order_data;
        }

        public function add_settings_page_to_payments_submenu(){
            add_submenu_page(
                'app-dashboard',
                'Job Reports',
                'Job Reports',
                'manage_options',
                'download_reports',
                [ $this, 'output' ]
            );
        }

        public function output(){
            include_once( JOB_REPORTS_PLUGIN_DIR . '/views/html-export-csv-form.php' );
        }

        public function month_selector(){
            ?>
            <select name="job_report_month">
                <?php
                    for ( $i = 0; $i <= 12; $i++ ) {
                        $sel = '';
                        if( $i == $this->current_month ){
                            $sel = ' selected="selected"';
                        }
                        echo '<option value="' . $i . '"' . $sel . '>';
                        echo        $this->month_name( $i );
                        echo '</option>';
                    }
                ?>
            </select>
            <?php
        }

        public function year_selector(){
            ?>
            <select name="job_report_year">
                <?php
                    for ( $i = date( 'Y' ); $i >= 2000; $i-- ) {
                        $sel = '';
                        if( $i == $this->current_year ){
                            $sel = ' selected="selected"';
                        }
                        echo '<option value="' . $i . '"' . $sel . '>';
                        echo        $i;
                        echo '</option>';
                    }
                ?>
            </select>
            <?php
        }

        public function month_name( $month_number ){
            $date = DateTime::createFromFormat( '!m', $month_number );
            return $date->format( 'F' );
        }
    }

}

$export_form = new WPJR_Export_CSV_Form();