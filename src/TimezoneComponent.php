<?php

namespace fiamma06\timezone;

use yii\base\Component;

/**
 * Class TimezoneComponent
 * @package frontend\components
 *
 * For use install php5-geoip module and download GeoIPRegion.dat database
 *
 * @property $name string Timezone name
 * @property $default string Default Timezone
 * @property $changeByIp array List of predefined ip with timezone
 *
 * Code for user:
 *
 * 'bootstrap' => ['timezone'],
 * 'components' => [
 *     'timezone' => [
 *         'class' => 'frontend\components\TimezoneComponent',
 *         'default' => 'UTC',
 *         'changeByIp' => [
 *             '10.192.5.26' => 'Europe/Minsk'
 *         ]
 *     ],
 * ]
 */
class TimezoneComponent extends Component {

    /**
     * Default timezone
     * Use If the timezone is not defined
     *
     * @var string
     */
    public $default = 'UTC';

    /**
     * Set timezone if found ip
     *
     * Example:
     *     ['10.192.5.26' => 'Europe\Minsk']
     *
     * @var array
     */
    public $changeByIp = [];

    /**
     * Init
     */
    public function init()
    {
        /**
         * Define
         */
        $time_zone_name = null;

        /*
         * Check user ip
         */
        $ip = \Yii::$app->request->getUserIP();

        /**
         * Exclude ip
         */
        foreach ($this->changeByIp as $filter => $timezone) {
            if( $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                $time_zone_name = $timezone;
                break;
            }
        }

        /**
         * Find timezone
         */
        if( ! $time_zone_name ) {
            try {
                $country = geoip_country_code_by_name($ip);
                $region  = geoip_region_by_name($ip);

                $time_zone_name = geoip_time_zone_by_country_and_region($country, $region['region']);
            } catch ( \Exception $e ) {}

            if( ! $time_zone_name ) {
                $time_zone_name = $this->default;
            }
        }

        /**
         * Set timezone
         */
        \Yii::$app->setTimeZone($time_zone_name);
    }

}