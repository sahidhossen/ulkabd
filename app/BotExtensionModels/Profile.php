<?php

namespace App\BotExtensionModels;

class Profile
{
    private $end_user;

    public function __construct($end_user) {
        $this->end_user = $end_user;
    }

    public function profileData() {

        $data = [
            'page_title' => 'My Profile', // Profile view Title, e.g => My Profile
            'name' => [
                'placeholder' => 'Name', // Name field placeholder
                'value' => '' // User name / null
            ],
            'date_of_birth' => '', // MM DD YYYY
            'gender' => 'male', // male/female
            'email' => [
                'placeholder' => 'Email <username@mail.com>', // email field placeholder
                'values' => '' // email ids / null
            ],
            'phone' => [
                'placeholder' => 'Mobile number', // phone field placeholder
                'values' => ['+8801'] // mobile phone numbers / null
            ],
            'mailing_address' => [
                'street_1' => [
                    'placeholder' => 'Street address, P.O. Box, c/o', // address line one field placeholder
                    'value' => '' // Address line one / null
                ],
                'street_2' => [
                    'placeholder' => 'Apartment, suite, unit, building, floor, etc.', // address line two field placeholder
                    'value' => '' // Address line two / null
                ],
                'city' => [
                    'placeholder' => 'City name', // city field placeholder
                    'value' => 'Dhaka' // city name / null
                ],
                'zip' => [
                    'placeholder' => 'Zip/Postal code', // zip field placeholder
                    'value' => '' // zip / null
                ],
                'country' => 'Bangladesh' // Country name
            ],
            'action_button_title' => 'Confirm' // Action button title
        ];

        $data['name']['value'] = $this->end_user->first_name . ' ' . $this->end_user->last_name;

        $address = json_decode($this->end_user->address, true);
        if ($address && count($address) > 0) {
            if (count($address) > 0) $address_line_one = $address[0];
            if (count($address) > 1) $address_line_two = $address[1];

            if (isset($this->end_user->date_of_birth)) $data['date_of_birth'] = $this->end_user->date_of_birth;
            if (isset($this->end_user->gender)) $data['gender'] = $this->end_user->gender;
            if (isset($this->end_user->emails)) $data['email']['values'] = json_decode($this->end_user->emails, true);
            if (isset($this->end_user->mobile_no)) $data['phone']['values'] = [$this->end_user->mobile_no];
            if (isset($address_line_one)) $data['mailing_address']['street_1']['value'] = $address_line_one;
            if (isset($address_line_two)) $data['mailing_address']['street_2']['value'] = $address_line_two;
            if (isset($this->end_user->city)) $data['mailing_address']['city']['value'] = $this->end_user->city;
            if (isset($this->end_user->zip)) $data['mailing_address']['zip']['value'] = $this->end_user->zip;
            if (isset($this->end_user->country)) $data['mailing_address']['country'] = $this->end_user->country;
            $data['action_button_title'] = 'Confirm';
        }

        return $data;
    }
}
