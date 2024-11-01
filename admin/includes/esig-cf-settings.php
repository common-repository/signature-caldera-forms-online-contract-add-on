<?php

if (!class_exists('ESIG_CF_SETTING')):

    class ESIG_CF_SETTING {

        const ESIG_CF_COOKIE = 'esig-cf-redirect';
        const CF_COOKIE = 'esig-caldera-temp-data';
        const CF_FORM_ID_META = 'esig_caldera_form_id';
        const CF_ENTRY_ID_META = 'esig_caldera_entry_id';

        private static $tempCookie = null;
        private static $tempSingleCookie = null;

        public static function is_caldera_requested_agreement($document_id) {
            $cf_form_id = WP_E_Sig()->meta->get($document_id, self::CF_FORM_ID_META);
            $cf_entry_id = WP_E_Sig()->meta->get($document_id, self::CF_ENTRY_ID_META);
            if ($cf_form_id && $cf_entry_id) {
                return true;
            }
            return false;
        }

        public static function get_signing_logic($processors) {

            if (!is_array($processors)) {
                return false;
            }
            $signing_logic = false;
            foreach ($processors as $processId => $process) {

                if ($process['type'] != "cf_wpesignature") {
                    continue;
                }
                $esig_config = $process['config'];
                $signing_logic = $esig_config['signing_logic'];
                if ($signing_logic == "redirect") {
                    break;
                }
            }
            return $signing_logic;
        }

        public static function get_submit_type($processors) {

            if (!is_array($processors)) {
                return false;
            }
            $submit_type = false;
            foreach ($processors as $processId => $process) {

                if ($process['type'] != "cf_wpesignature") {
                    continue;
                }
                $esig_config = $process['config'];
                $submit_type = $esig_config['submit_type'];
            }
            return $submit_type;
        }

        public static function is_cf_esign_required() {
            if (self::get_temp_settings()) {
                return true;
            } else {
                return false;
            }
        }

        public static function get_temp_settings() {
            
            if (!empty(self::$tempCookie)) {
                return json_decode(stripslashes(self::$tempCookie), true);
            }
            if (ESIG_COOKIE(self::CF_COOKIE)) {
                return json_decode(stripslashes(ESIG_COOKIE(self::CF_COOKIE)), true);
            }
            return false;
        }

        public static function delete_temp_settings() {
            setcookie(self::CF_COOKIE, null, time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
        }

        public static function save_esig_cf_meta($meta_key, $meta_index, $meta_value) {

            $temp_settings = self::get_temp_settings();
            if (!$temp_settings) {
                $temp_settings = array();
                $temp_settings[$meta_key] = array($meta_index => $meta_value);
                // finally save slv settings . 
                self::save_temp_settings($temp_settings);
            } else {

                if (array_key_exists($meta_key, $temp_settings)) {
                    $temp_settings[$meta_key][$meta_index] = $meta_value;
                    self::save_temp_settings($temp_settings);
                } else {
                    $temp_settings[$meta_key] = array($meta_index => $meta_value);
                    self::save_temp_settings($temp_settings);
                }
            }
        }

        public static function save_temp_settings($value) {
            $json = json_encode($value);
            esig_setcookie(self::CF_COOKIE, $json, 600);
            // for instant cookie load. 
            $_COOKIE[self::CF_COOKIE] = $json;
            self::$tempCookie = $json;
        }

        public static function save_invite_url($invite_hash, $document_checksum) {
            
            if(!empty(self::$tempSingleCookie)){
                return false;
            }
            
            $invite_url = WP_E_Invite::get_invite_url($invite_hash, $document_checksum);

            esig_setcookie(self::ESIG_CF_COOKIE, $invite_url, 600);

            $_COOKIE[self::ESIG_CF_COOKIE] = $invite_url;
            self::$tempSingleCookie = $invite_url;
        }

        public static function get_invite_url() {
            return esigget(self::ESIG_CF_COOKIE, $_COOKIE);
        }

        public static function get_temp_invite_url($invite_hash) {
            $document_checksum = WP_E_Sig()->document->document_checksum_by_id(WP_E_Sig()->invite->getdocumentid_By_invitehash($invite_hash));
            return WP_E_Sig()->invite->get_invite_url($invite_hash, $document_checksum);
        }

        public static function remove_invite_url() {
            setcookie(self::ESIG_CF_COOKIE, null, time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
        }

        public static function getCheckbox($values, $display, $option) {

            if (!is_array($values)) {
                return $values;
            }
            $items = '';
            foreach ($values as $key => $item) {
                if ($item) {
                    $items .= '<li><span style="font-size:16px;">&#10003;</span>' . $item . '</li>';
                }
            }
            return "<ul class='esig-checkbox-tick'>$items</ul>";
        }
        
        
        public static function getHtml($field_id,$label,$form, $display, $option){
            
               $html_content=$form['fields'][$field_id]['config']['default'];
               
               if($display=="value"){
                   return $html_content;
               }
               if($display=="label_value"){
                   return $label . ": " . $html_content;
               }
           
               return false;
        }

        /**
         * Generate fields option using form id
         * @param type $form_id
         * @return string
         */
        public static function get_value($document_id, $form, $entry_id, $field_id, $display, $option) {

            $label = $form['fields'][$field_id]['label'];
            $fType = $form['fields'][$field_id]['type'];


            if ($display == "label") {
                return $label;
            }

            $data_query = WP_E_Sig()->meta->get($document_id, 'esig_caldera_submission_value');
            if (!$data_query) {
                $data = Caldera_Forms::get_submission_data($form, $entry_id);
            } else {
                $data = json_decode($data_query, true);
            }
            if ($fType == "html") {
                return self::getHtml($field_id,$label,$form, $display, $option);
            }
            // print_r($data);
            if (is_array($data)) {

                if ($display == "value") {

                    if ($fType == "checkbox" && $option == "check") {
                        return self::getCheckbox($data[$field_id], $display, $option);
                    }
                    $data = isset($data[$field_id]) ? $data[$field_id] : false;
                    return $data;
                } elseif ($display == "label_value") {

                    $value = isset($data[$field_id]) ? $data[$field_id] : false;
                    $result = '';

                    if (is_array($value)) {
                        foreach ($value as $val) {
                            $result .= $val . " ,";
                        }
                        return $label . ": " . substr($result, 0, strlen($result) - 2);
                    }

                    return $label . ": " . $value;
                }
            }
            return false;
        }

        public static function display_value($form, $form_id, $cf_value, $submit_type = "underline") {

            $result = '';
            if ($submit_type == "underline") {
                $result .= '<u>' . $cf_value . '</u>';
            } else {
                $result .= $cf_value;
            }
            return $result;
        }
        
        public static function esigget($name, $array = null) {

            if (!isset($array) && function_exists('ESIG_GET')) {
                return ESIG_GET($name);
            }

            if (is_array($array)) {
                if (isset($array[$name])) {
                    return wp_unslash($array[$name]);
                }
                return false;
            }

            if (is_object($array)) {
                if (isset($array->$name)) {
                    return wp_unslash($array->$name);
                }
                return false;
            }

            return false;
        }

    }

    

    

    

    

    

    

    
endif;