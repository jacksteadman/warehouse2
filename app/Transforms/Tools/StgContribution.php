<?php

namespace App\Transforms\Tools;

class StgContribution {
    private $fixture_data = [];

    private $output_format = [
        'client_id',
        'stg_contribution_id',
        'contribution_page_id',
        'stg_contribution_recurring_id',
        'mailing_recipient_id',
        'mailing_link_id',
        'outreach_page_id',
        'event_attendee_id',
        'stg_signup_id',
        'cons_group_id',
        'cons_id',
        'match_campaign_id',
        'match_pledge_id',
        'contribution_key',
        'is_retry',
        'email',
        'prefix',
        'firstname',
        'middlename',
        'lastname',
        'suffix',
        'phone',
        'addr1',
        'addr2',
        'city',
        'state_cd',
        'zip',
        'country',
        'employer',
        'employer_addr1',
        'employer_addr2',
        'employer_city',
        'employer_state_cd',
        'employer_zip',
        'employer_country',
        'occupation',
        'honoree_email',
        'honoree_addr',
        'honoree_addr2',
        'honoree_city',
        'honoree_state_cd',
        'honoree_zip',
        'honoree_country',
        'honoree_name',
        'honoree_recipient_name',
        'honoree_in_memoriam',
        'transaction_amt',
        'tax_deductible_amt',
        'intl_currency_symbol',
        'exchange_transaction_amt',
        'exchange_intl_currency_symbol',
        'opt_compliance',
        'cc_number',
        'cc_type_cd',
        'source',
        'subsource',
        'auth_code',
        'auth_rcode',
        'auth_rflag',
        'auth_time',
        'auth_avs',
        'bill_rcode',
        'bill_rflag',
        'bill_reference_num',
        'proc_request_id',
        'proc_response',
        'proc_class',
        'ip_addr',
        'http_referer',
        'contribution_status',
        'is_recurring',
        'is_giftaid',
        'gateway_name',
        'gateway_id',
        'custom1',
        'custom2',
        'custom3',
        'custom_country_field_1',
        'custom_country_field_2',
        'cybersource_decision_status',
        'cybersource_decision_dt',
        'charge_dt',
        'chapter_id',
        'core_upload_file_id',
        'contribution_type',
        'dupe_ok',
        'create_dt',
        'create_app',
        'create_user',
        'create_user_agent',
        'modified_dt',
        'modified_app',
        'modified_user',
        'status',
        'note',
    ];

    private $filters = [
        'contribution_key' => [ 'filterString' ],
        'email' => [ 'filterString' ],
        'prefix' => [ 'filterString' ],
        'firstname' => [ 'filterString' ],
        'middlename' => [ 'filterString' ],
        'lastname' => [ 'filterString' ],
        'suffix' => [ 'filterString' ],
        'phone' => [ 'filterString' ],
        'addr1' => [ 'filterString' ],
        'addr2' => [ 'filterString' ],
        'city' => [ 'filterString' ],
        'state_cd' => [ 'filterString' ],
        'zip' => [ 'filterString' ],
        'country' => [ 'filterString' ],
        'employer' => [ 'filterString' ],
        'employer_addr1' => [ 'filterString' ],
        'employer_addr2' => [ 'filterString' ],
        'employer_city' => [ 'filterString' ],
        'employer_state_cd' => [ 'filterString' ],
        'employer_zip' => [ 'filterString' ],
        'employer_country' => [ 'filterString' ],
        'occupation' => [ 'filterString' ],
        'honoree_email' => [ 'filterString' ],
        'honoree_addr' => [ 'filterString' ],
        'honoree_addr2' => [ 'filterString' ],
        'honoree_city' => [ 'filterString' ],
        'honoree_state_cd' => [ 'filterString' ],
        'honoree_zip' => [ 'filterString' ],
        'honoree_country' => [ 'filterString' ],
        'honoree_name' => [ 'filterString' ],
        'honoree_recipient_name' => [ 'filterString' ],
        'intl_currency_symbol' => [ 'filterString' ],
        'exchange_intl_currency_symbol' => [ 'filterString' ],
        'cc_number' => [ 'filterString' ],
        'cc_type_cd' => [ 'filterString' ],
        'source' => [ 'filterString' ],
        'subsource' => [ 'filterString' ],
        'auth_code' => [ 'filterString' ],
        'auth_rflag' => [ 'filterString' ],
        'auth_time' => [ 'filterString' ],
        'auth_avs' => [ 'filterString' ],
        'bill_rflag' => [ 'filterString' ],
        'bill_reference_num' => [ 'filterString' ],
        'proc_request_id' => [ 'filterString' ],
        'proc_response' => [ 'filterString' ],
        'proc_class' => [ 'filterString' ],
        'ip_addr' => [ 'filterString' ],
        'http_referer' => [ 'filterString' ],
        'gateway_name' => [ 'filterString' ],
        'custom1' => [ 'filterString' ],
        'custom2' => [ 'filterString' ],
        'custom3' => [ 'filterString' ],
        'custom_country_field_1' => [ 'filterString' ],
        'custom_country_field_2' => [ 'filterString' ],
        'charge_dt' => [ 'filterString' ],
        'create_app' => [ 'filterString' ],
        'create_user' => [ 'filterString' ],
        'create_user_agent' => [ 'filterString' ],
        'modified_app' => [ 'filterString' ],
        'modified_user' => [ 'filterString' ],
        'note' => [ 'filterString' ],
    ];

    public function setFixtureData($data) {
        $this->fixture_data = $data;
    }

    public function transform($record) {
        $record = array_merge($this->fixture_data, $record);

        $tx_record = [];
        foreach ($this->output_format as $field) {
            $value = (isset($record[$field]) ? $record[$field] : null);

            if (!empty($this->filters[$field])) {
                foreach ($this->filters[$field] as $filter) {
                    if (method_exists($this, $filter)) {
                        $value = $this->$filter($value);
                    } elseif (is_callable($filter)) {
                        $value = $filter($value);
                    }
                }
            }

            $tx_record[] = $value;
        }

        return $tx_record;
    }

    private function filterString($string) {
        $string = str_replace("\n", '\n', $string);
        $string = substr($string, 0, 500);
        return $string;
    }
}