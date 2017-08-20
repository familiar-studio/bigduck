<?php

class AOAPI {
    var $version = "0.1";
    var $errorMessage;
    var $errorCode;

    /**
     * Cache the information on the API location on the server
     */
    var $apiUrl;

    /**
     * Default to a 300 second timeout on server calls
     */
    var $timeout = 300;

    /**
     * Default to a 8K chunk size
     */
    var $chunkSize = 8192;

    /**
     * Cache the user api_key so we only have to log in once per client instantiation
     */
    var $api_key;

    /**
     * Cache the user api_key so we only have to log in once per client instantiation
     */
    var $secure = false;

    /**
     */
    function AOAPI ($username, $password, $client_id, $client_secret) {

        if (!trim($username) ||
            !trim($password) ||
            !trim($client_id) ||
            !trim($client_secret))
        {
            $this->errorMessage = "Invalid credentials";
        }

        $this->secure   = $secure;
        $this->apiUrl   = parse_url("https://restapi.actonsoftware.com");
        $this->api_key  = $apikey;

        $actoncredentials = get_transient( 'actoncredentials' );

        $this->debug( 'Stored auth token', $actoncredentials );

        if ($actoncredentials['refresh_token'] && $actoncredentials['access_token'])
        {
            $this->refresh_token = $actoncredentials['refresh_token'];
            $this->access_token  = $actoncredentials['access_token'];

            if ($this->getAccountInfo())
            {
                return $this;
            }

            else
            {
                delete_transient('actoncredentials');
            }
        }

        $post_vars                  = Array();
        $post_vars['grant_type']    = 'password';
        $post_vars['username']      = $username;
        $post_vars['password']      = $password;
        $post_vars['client_id']     = $client_id;
        $post_vars['client_secret'] = $client_secret;
        $post_data                  = http_build_query($post_vars);

        $url = "https://restapi.actonsoftware.com/token";

        $ch  = curl_init();
        curl_setopt($ch,CURLOPT_URL,            $url);
        curl_setopt($ch,CURLOPT_POST,           count($post_vars));
        curl_setopt($ch,CURLOPT_POSTFIELDS,     $post_data);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch,CURLOPT_TIMEOUT,        20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);

        $response_parts = json_decode($response,1);

        $this->debug( 'Get Auth Token From API', $response_parts );

        if (!$response_parts)
        {
            $this->errorMessage =  $response . "Could not get token";
            $this->errorCode = -999;
            return false;
        }

        if (!trim($response_parts['refresh_token']) ||
            !trim($response_parts['access_token']))
        {
            $this->errorMessage = $response . "Could not get token";
            $this->errorCode = -999;
            return false;
        }

        $this->debug( 'Store auth token', $response_parts );

        set_transient( 'actoncredentials', $response_parts, 1800 );

        $this->refresh_token = $response_parts['refresh_token'];
        $this->access_token  = $response_parts['access_token'];

        return $this;
    }
    function setTimeout($seconds){
        if (is_int($seconds)){
            $this->timeout = $seconds;
            return true;
        }
    }
    function getTimeout(){
        return $this->timeout;
    }
    function useSecure($val){
        if ($val===true){
            $this->secure = true;
        } else {
            $this->secure = false;
        }
    }

    function __call($method, $params) {
    }

    function refreshToken() {

    }

    function debug($desc, $value ) {
    //    print $desc . '::<br/>';
    //    print '<pre>';
    //    print_r($value);
    //    print '</pre>';
    }

    function getAccountInfo() {

        $headr      = Array();
        $headr[]    = 'Authorization: Bearer '. $this->access_token;
        $url        = "https://restapi.actonsoftware.com/api/1/account";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,             $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,  true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,  3);
        curl_setopt($ch, CURLOPT_TIMEOUT,         20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
        curl_setopt($ch, CURLOPT_HTTPHEADER,      $headr);

        $response = curl_exec($ch);

        $response_parts = json_decode($response,1);

        $this->debug( 'Got Account Info From API', $response_parts );

        if (!$response_parts)
        {
            return Array();
        }

        return $response_parts;
    }


    function getForms( $cache=true ) {

        if ($cache)
        {
            if ($forms = get_transient('gf_acton_forms'))
            {
                $this->debug( 'Got Forms From Cache', $forms );
                return $forms;
            }
        }

        $headr      = Array();
        $headr[]    = 'Authorization: Bearer '. $this->access_token;
        $url        = "https://restapi.actonsoftware.com/api/1/form";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,             $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,  true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,  3);
        curl_setopt($ch, CURLOPT_TIMEOUT,         20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
        curl_setopt($ch, CURLOPT_HTTPHEADER,      $headr);

        $response = curl_exec($ch);
        $response_parts = json_decode($response,1);

        $this->debug( 'Got Forms From API', $response_parts );

        if (!$response_parts)
        {
            return Array();
        }

        set_transient( 'gf_acton_forms', $response_parts, 500 );
        return $response_parts;
    }

    function getFormFields( $form_id )
    {
        if ($fields = get_transient('gf_acton_formfields_' . $form_id))
        {
            $this->debug( 'Got Fields From cache', $fields );
            return $fields;
        }

        $headr = Array();
        $headr[] = 'Authorization: Bearer '. $this->access_token;
        $url = "https://restapi.actonsoftware.com/api/1/form/" . $form_id . "/urls";

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,             $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,  true);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,  3);
        curl_setopt($ch,CURLOPT_TIMEOUT,         20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $headr);

        $response = curl_exec($ch);

        $response_parts = json_decode($response,1);

        $this->debug( 'Got Fields From API', $response_parts );

        if (!$response_parts)
        {
            return Array();
        }

        if ($form_url = $response_parts['urls'][0]['url'])
        {
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,             $form_url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,  true);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,  3);
            curl_setopt($ch,CURLOPT_TIMEOUT,         20);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $form_html = curl_exec($ch);

            $form_fields = Array();

            $dom = new DOMDocument();
            if (@$dom->loadHTML($form_html)) {
                // yep, not necessarily valid-html...
                $xpath = new DOMXpath($dom);

                $nodeList = $xpath->query('//input');
                if ($nodeList->length > 0) {
                    for ($i=0 ; $i<$nodeList->length ; $i++) {
                        $node = $nodeList->item($i);
                        $name = $node->getAttribute('name');
                        $type = $node->getAttribute('type');

                        if ($type != 'hidden' &&
                            $type != 'button' &&
                            $name != 'ao_form_neg_cap')
                        {
                            $new_field = Array();
                            $new_field['name'] = $name;
                            $new_field['tag'] = $name;
                            $form_fields[] = $new_field;
                        }
                    }
                }

                $nodeList = $xpath->query('//select');
                if ($nodeList->length > 0) {
                    for ($i=0 ; $i<$nodeList->length ; $i++) {
                        $node = $nodeList->item($i);
                        $name = $node->getAttribute('name');
                        $type = $node->getAttribute('type');

                        if ($type != 'hidden' &&
                            $type != 'button' &&
                            $name != 'ao_form_neg_cap')
                        {
                            $new_field = Array();
                            $new_field['name'] = $name;
                            $new_field['tag'] = $name;
                            $form_fields[] = $new_field;
                        }
                    }
                }

                $nodeList = $xpath->query('//textarea');
                if ($nodeList->length > 0) {
                    for ($i=0 ; $i<$nodeList->length ; $i++) {
                        $node = $nodeList->item($i);
                        $name = $node->getAttribute('name');
                        $type = $node->getAttribute('type');

                        if ($type != 'hidden' &&
                            $type != 'button' &&
                            $name != 'ao_form_neg_cap')
                        {
                            $new_field = Array();
                            $new_field['name'] = $name;
                            $new_field['tag'] = $name;
                            $form_fields[] = $new_field;
                        }
                    }
                }
            }
        }

        set_transient( 'gf_acton_formfields_' . $form_id, $form_fields, 500 );
        return $form_fields;
    }
}
