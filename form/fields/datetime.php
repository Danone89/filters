<?php
/**
 * @package Filters\Fields
 */
class FiltersField_DateTime extends FiltersField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 0.1
     */
    public static $group = 'Date / Time';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 0.1
     */
    public static $type = 'datetime';

    /**
     * Field Type Label
     *
     * @var string
     * @since 0.1
     */
    public static $label = 'Date / Time';

    /**
     * Field Type Preparation
     *
     * @var string
     * @since 0.1
     */
    public static $prepare = '%s';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 0.1
     */
    public function __construct () {

    }

    /**
     * Add options and set defaults to
     *
     * @return array
     *
     * @since 0.1
     */
    public function options () {
        $options = array(
            'datetime_format' => array(
                'label' => __( 'Date Format', 'filters' ),
                'default' => 'mdy',
                'type' => 'pick',
                'data' => array(
                    'mdy' => 'mm/dd/yyyy',
                    'dmy' => 'dd/mm/yyyy',
                    'dmy_dash' => 'dd-mm-yyyy',
                    'dmy_dot' => 'dd.mm.yyyy',
                    'ymd_slash' => 'yyyy/mm/dd',
                    'ymd_dash' => 'yyyy-mm-dd',
                    'ymd_dot' => 'yyyy.mm.dd'
                )
            ),
            'datetime_time_type' => array(
                'label' => __( 'Time Format Type', 'filters' ),
                'default' => '12',
                'type' => 'pick',
                'data' => array(
                    '12' => __( '12 hour', 'filters' ),
                    '24' => __( '24 hour', 'filters' )
                )
            ),
            'datetime_time_format' => array(
                'label' => __( 'Time Format', 'filters' ),
                'default' => 'h_mma',
                'type' => 'pick',
                'data' => array(
                    'h_mm_A' => '1:25 PM',
                    'h_mm_ss_A' => '1:25:00 PM',
                    'hh_mm_A' => '01:25 PM',
                    'hh_mm_ss_A' => '01:25:00 PM',
                    'h_mma' => '1:25pm',
                    'hh_mma' => '01:25pm',
                    'h_mm' => '1:25',
                    'h_mm_ss' => '1:25:00',
                    'hh_mm' => '01:25',
                    'hh_mm_ss' => '01:25:00'
                )
            ),
            'datetime_allow_empty' => array(
                'label' => __( 'Allow empty value?', 'filters' ),
                'default' => 1,
                'type' => 'boolean'
            ),
            'datetime_html5' => array(
                'label' => __( 'Enable HTML5 Input Field?', 'filters' ),
                'default' => apply_filters( 'filters_form_ui_field_html5', 0, self::$type ),
                'type' => 'boolean'
            )
        );
        return $options;
    }

    /**
     * Define the current field's schema for DB table storage
     *
     * @param array $options
     *
     * @return array
     * @since 0.1
     */
    public function schema ( $options = null ) {
        $schema = 'DATETIME NOT NULL default "0000-00-00 00:00:00"';

        return $schema;
    }

    /**
     * Change the way the value of the field is displayed with Filters::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @return mixed|null|string
     * @since 0.1
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        $format = $this->format( $options );

        if ( !empty( $value ) && !in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
            $date = $this->createFromFormat( 'Y-m-d H:i:s', (string) $value );
            $date_local = $this->createFromFormat( $format, (string) $value );

            if ( false !== $date )
                $value = $date->format( $format );
            elseif ( false !== $date_local )
                $value = $date_local->format( $format );
            else
                $value = date_i18n( $format, strtotime( (string) $value ) );
        }
        elseif ( 0 == filters_var( 'datetime_allow_empty', $options, 1 ) )
            $value = date_i18n( $format );

        return $value;
    }

    /**
     * Customize output of the form field
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @since 0.1
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;

        if ( is_array( $value ) )
            $value = implode( ' ', $value );

        // Format Value
        $value = $this->display( $value, $name, $options, null, $pod, $id );

        filters_view( FILTERS_DIR . 'ui/fields/datetime.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param mixed $value
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param object $params
     *
     * @return mixed|string
     * @since 0.1
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        $format = $this->format( $options );

        if ( !empty( $value ) && ( 0 == filters_var( 'datetime_allow_empty', $options, 1 ) || !in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) )
            $value = $this->convert_date( $value, 'Y-m-d H:i:s', $format );
        elseif ( 1 == filters_var( 'datetime_allow_empty', $options, 1 ) )
            $value = '0000-00-00 00:00:00';
        else
            $value = date_i18n( 'Y-m-d H:i:s' );

        return $value;
    }

    /**
     * Customize the Filters UI manage table column output
     *
     * @param int $id
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     *
     * @return mixed|null|string
     * @since 0.1
     */
    public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
        $value = $this->display( $value, $name, $options, $pod, $id );

        if ( 1 == filters_var( 'datetime_allow_empty', $options, 1 ) && in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) )
            $value = false;

        return $value;
    }

    /**
     * Build date/time format string based on options
     *
     * @param $options
     *
     * @return string
     * @since 0.1
     */
    public function format ( $options ) {
        $date_format = array(
            'mdy' => 'm/d/Y',
            'dmy' => 'd/m/Y',
            'dmy_dash' => 'd-m-Y',
            'dmy_dot' => 'd.m.Y',
            'ymd_slash' => 'Y/m/d',
            'ymd_dash' => 'Y-m-d',
            'ymd_dot' => 'Y.m.d'
        );

        $time_format = array(
            'h_mm_A' => 'g:i A',
            'h_mm_ss_A' => 'g:i:s A',
            'hh_mm_A' => 'h:i A',
            'hh_mm_ss_A' => 'h:i:s A',
            'h_mma' => 'g:ia',
            'hh_mma' => 'h:ia',
            'h_mm' => 'g:i',
            'h_mm_ss' => 'g:i:s',
            'hh_mm' => 'h:i',
            'hh_mm_ss' => 'h:i:s'
        );

        $format = $date_format[ filters_var( 'datetime_format', $options, 'ymd_dash', null, true ) ] . ' ';

        if ( 12 == filters_var( 'datetime_time_type', $options ) )
            $format .= $time_format[ filters_var( 'datetime_time_format', $options, 'hh_mm', null, true ) ];
        else
            $format .= str_replace( array( 'h:', 'g:' ), 'H:', $time_format[ filters_var( 'datetime_time_format', $options, 'hh_mm', null, true ) ] );

        return $format;
    }

    /**
     * @param $format
     * @param $date
     *
     * @return DateTime
     */
    public function createFromFormat ( $format, $date ) {
        if ( method_exists( 'DateTime', 'createFromFormat' ) )
            return DateTime::createFromFormat( $format, (string) $date );

        return new DateTime( date_i18n( 'Y-m-d H:i:s', strtotime( (string) $date ) ) );
    }

    /**
     * Convert a date from one format to another
     *
     * @param $value
     * @param $new_format
     * @param string $original_format
     *
     * @return string
     */
    public function convert_date ( $value, $new_format, $original_format = 'Y-m-d H:i:s' ) {
        if ( !empty( $value ) && !in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
            $date = $this->createFromFormat( $original_format, (string) $value );

            if ( false !== $date )
                $value = $date->format( $new_format );
            else
                $value = date_i18n( $new_format, strtotime( (string) $value ) );
        }
        else
            $value = date_i18n( $new_format );

        return $value;
    }
}
