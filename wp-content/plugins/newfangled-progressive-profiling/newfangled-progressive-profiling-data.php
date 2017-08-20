<?php
/**
 * Newfangled Progressive Profiling
 *
 * @package   Newfangled Progressive Profiling
 * @author    Newfangled
 * @link      https://bitbucket.org/newfangled_web/newfangled-progressive-profiling
 * @copyright Newfangled 2016
 */

//  Don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

//  Define the settings class
if (!class_exists("NFProfiling_Data")) {

    class NFProfiling_Data {

        /**
         * Define the name of the table
         */
        private static function get_progressiveprofiling_table_name(){
        
            global $wpdb;
            return $wpdb->prefix . "nfprofiling";
        
        }

        /**
         * Define the name of the cookie
         */
        private static function get_progressiveprofiling_cookie_name(){
        
            return 'nfpp';
        }

        /**
         * Get the user's profile_id from the cookie. Then:
         * 
         *     - If the cookie value exists, make sure the profile record
         *     also exists
         *
         *     - If the cookie value exists, and we know the user's
         *     email address, make sure the profile email address 
         *     matches
         *
         *     - If we dont have a cookie value, but we do know the 
         *     email address, see if a profile exists for that email, 
         *     and use that profile_id.
         *
         * 
         * @param  string $verify_email - optional known email address to compare to the profile
         *
         * @return mixed profile_id - the user's profile id
         */
        public static function get_profile_id( $verify_email='' )
        {
            global $wpdb;

            //  Get the cookie name
            $cookie_name = self::get_progressiveprofiling_cookie_name();

            //  Get the table name
            $table_name = self::get_progressiveprofiling_table_name();

            //  Does a cookie value exist for this user?
            if ( $profile_id = $_COOKIE[ $cookie_name ] ) {

                //  Make sure the profile exists
                $profile_exists_email = $wpdb->get_var( 
                    $wpdb->prepare(
                        "SELECT email FROM $table_name WHERE profile_id = %s",
                        $profile_id
                ));

                if (!$profile_exists_email) {
                    $profile_id = null;
                }

                //  If an email address was provided, 
                //  make sure it matches the stored profile
                if ($profile_id && $verify_email && $profile_exists_email) {

                    if ($profile_exists_email != $verify_email) {
                        $profile_id = null;
                    }
                }
            }

            // If no profile id exists and an email 
            // was provided, see if we already have
            // an entry 
            if (!$profile_id && $verify_email) {
                
                //  Does a profile already exist?
                $existing_id = $wpdb->get_var( 
                    $wpdb->prepare(
                        "SELECT profile_id FROM $table_name WHERE email = %s AND profile_id IS NOT NULL ",
                        $verify_email
                ));

                if ($existing_id) {
                    $profile_id = $existing_id;
                }
            }

            //  If still no id, return null
            if (!$profile_id) {
                return null;
            }

            //  Otherwise, return the ID
            else {
                return $profile_id;
            }
        }

        /**
         * Wrapper to generate an unique ID
         *
         * @return string - the unique id
         */
        private static function generate_profile_id(  )
        {
            return self::uuid();
        }

        /**
         * Return a 32-char "unique" id
         * 
         * @return string
         */
        private static function uuid()
        {
            return substr(md5(uniqid(rand(), true)), 0, 31); 
        }

        
        /**
         * Get the email address associated with the 
         * current user's profile_id
         *
         * @return string $email - the email address
         */
        public static function get_profile_email()
        {           
            global $wpdb;

            //  Does the user have a profile id?
            if (!$profile_id = self::get_profile_id()) {
                return array();
            }

            //  Lookup the email
            $table_name = self::get_progressiveprofiling_table_name();
            $email = $wpdb->get_var( 
                $wpdb->prepare(
                    "SELECT email FROM $table_name WHERE profile_id = %s",
                    $profile_id
            ));

            //  Return the email address, if it exists
            if ($email) {
                return $email;
            }  else  {
                return '';
            }
        }

        /**
         * Get the profile array associated with the
         * current user, if it exists
         *
         * @return array $profile - the profile array
         */
        public static function get_profile(){
            
            global $wpdb;

            //  Does the user have a profile id?
            if (!$profile_id = self::get_profile_id()) {
                return array();
            }

            //  Look up the profile
            $table_name = self::get_progressiveprofiling_table_name();
            $profile = $wpdb->get_var( 
                $wpdb->prepare(
                    "SELECT profile FROM $table_name WHERE profile_id = %s",
                    $profile_id
            ));

            //  Return the profile, if it exists
            if ($profile) {
                return maybe_unserialize($profile);
            } else {
                return array();
            }
        }

        /**
         * Update or create the profile entry 
         * for the current user on form submit,
         *
         * @param  [type] $email   [description]
         * @param  [type] $profile [description]
         *
         * @return [type]          [description]
         */
        public static function update_profile($email, $profile){
            
            global $wpdb;

            //  Serialize the profile if not already
            if (is_array($profile)) {
                $profile = serialize($profile);
            }

            //  Does a profile already exist? If so, 
            //  update it
            if ($profile_id = self::get_profile_id( $email )) {
                
                $table_name = self::get_progressiveprofiling_table_name();
                $wpdb->update(
                    $table_name, 
                    array(
                        "email" => $email, 
                        "profile" => $profile), 
                    array(
                        "profile_id" => $profile_id), 
                    array(
                        "%s", "%s"), 
                    array("%s"));
                
                //  Update the user's cookie with the profile
                //  id (in case we had to change it)
                self::set_cookie( $profile_id );
            } 

            //  Otherwise, create a new profile
            else {

                //  Generate a new profile id
                if ( $profile_id = self::generate_profile_id() ) {

                    $table_name = self::get_progressiveprofiling_table_name();
                    $wpdb->insert(
                        $table_name, 
                        array(
                            "profile_id" => $profile_id,
                            "email" => $email, 
                            "profile" => $profile), 
                        array(
                            "%s", 
                            "%s", 
                            "%s"
                    ));

                    //  Set the cookie
                    self::set_cookie( $profile_id );
                }
            }
        }

        /**
         * Set the profile_id cookie, to expire
         * far in the future
         *
         * @param integer $profile_id - the profile id of the user
         */
        private static function set_cookie( $profile_id ){
            
            //  Get the cookie name
            $cookie_name = self::get_progressiveprofiling_cookie_name();

            //  Set the exipirey time
            $expires = time() + (10 * 365 * 24 * 60 * 60);

            //  Set the cookie
            if ($profile_id) {
                SetCookie( $cookie_name, $profile_id, $expires, '/', '', 0, 0 );

                //  Make sure cookie is also available for the current pageload.
                $_COOKIE[$cookie_name] = $profile_id;
            }        
        }

        /**
         * Make sure the tables we need exist
         */
        public static function update_table(){
        
            global $wpdb;
            
            //  Get the table name to verify/create
            $table_name = self::get_progressiveprofiling_table_name();

            //  Get the charset
            if ( ! empty($wpdb->charset) )
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if ( ! empty($wpdb->collate) )
                $charset_collate .= " COLLATE $wpdb->collate";

            //  Define the create SQL
            $sql = "
                CREATE TABLE $table_name (
                id mediumint(8) unsigned not null auto_increment,
                profile_id varchar(32),
                email varchar(255),
                profile longtext,
                PRIMARY KEY  (id),
                UNIQUE KEY profile_id (profile_id)
            )$charset_collate;";
    
            //  Verify/create the table
            require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
            dbDelta($sql);

            print $wpdb->last_error;
        }
            
        /**
         * Remove the tables on plugin delete
         */
        public static function drop_tables(){
        
            global $wpdb;
            $wpdb->query("DROP TABLE IF EXISTS " . self::get_progressiveprofiling_table_name());
        
        }
    }
}
