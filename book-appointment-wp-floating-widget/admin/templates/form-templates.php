<?php
if (!defined('ABSPATH')) { exit; }

function baw_get_predefined_templates() {
    return [
        'general' => [
            'label' => __('General Business', 'book-appointment-wp-floating-widget'),
            'fields' => [
                ['key'=>'name','label'=>__('Name','book-appointment-wp-floating-widget'),'type'=>'text','placeholder'=>'','required'=>true,'enabled'=>true],
                ['key'=>'mobile','label'=>__('Mobile Number','book-appointment-wp-floating-widget'),'type'=>'tel','placeholder'=>'+11234567890','required'=>true,'enabled'=>true],
                ['key'=>'visit_time','label'=>__('Visit Time','book-appointment-wp-floating-widget'),'type'=>'datetime-local','placeholder'=>'','required'=>true,'enabled'=>true],
                ['key'=>'description','label'=>__('Details','book-appointment-wp-floating-widget'),'type'=>'textarea','placeholder'=>'','required'=>false,'enabled'=>true],
            ],
        ],
        'hospital' => [
            'label' => __('Hospital', 'book-appointment-wp-floating-widget'),
            'fields' => [
                ['key'=>'name','label'=>__('Patient Name','book-appointment-wp-floating-widget'),'type'=>'text','placeholder'=>'','required'=>true,'enabled'=>true],
                ['key'=>'mobile','label'=>__('Mobile Number','book-appointment-wp-floating-widget'),'type'=>'tel','placeholder'=>'+11234567890','required'=>true,'enabled'=>true],
                ['key'=>'visit_time','label'=>__('Preferred Visit Time','book-appointment-wp-floating-widget'),'type'=>'datetime-local','placeholder'=>'','required'=>true,'enabled'=>true],
                ['key'=>'department','label'=>__('Department','book-appointment-wp-floating-widget'),'type'=>'select','placeholder'=>'Cardiology, Orthopedics, Pediatrics','required'=>true,'enabled'=>true],
                ['key'=>'description','label'=>__('Symptoms / Notes','book-appointment-wp-floating-widget'),'type'=>'textarea','placeholder'=>'','required'=>false,'enabled'=>true],
            ],
        ],
        'vet' => [
            'label' => __('Veterinary Clinic', 'book-appointment-wp-floating-widget'),
            'fields' => [
                ['key'=>'name','label'=>__('Owner Name','book-appointment-wp-floating-widget'),'type'=>'text','placeholder'=>'','required'=>true,'enabled'=>true],
                ['key'=>'mobile','label'=>__('Mobile Number','book-appointment-wp-floating-widget'),'type'=>'tel','placeholder'=>'+11234567890','required'=>true,'enabled'=>true],
                ['key'=>'pet_name','label'=>__('Pet Name','book-appointment-wp-floating-widget'),'type'=>'text','placeholder'=>'','required'=>true,'enabled'=>true],
                ['key'=>'pet_age','label'=>__('Pet Age','book-appointment-wp-floating-widget'),'type'=>'number','placeholder'=>'','required'=>false,'enabled'=>true],
                ['key'=>'visit_time','label'=>__('Visit Time','book-appointment-wp-floating-widget'),'type'=>'datetime-local','placeholder'=>'','required'=>true,'enabled'=>true],
                ['key'=>'description','label'=>__('Issue / Notes','book-appointment-wp-floating-widget'),'type'=>'textarea','placeholder'=>'','required'=>false,'enabled'=>true],
            ],
        ],
        'salon' => [
            'label' => __('Salon', 'book-appointment-wp-floating-widget'),
            'fields' => [
                ['key'=>'name','label'=>__('Name','book-appointment-wp-floating-widget'),'type'=>'text','placeholder'=>'','required'=>true,'enabled'=>true],
                ['key'=>'mobile','label'=>__('Mobile Number','book-appointment-wp-floating-widget'),'type'=>'tel','placeholder'=>'+11234567890','required'=>true,'enabled'=>true],
                ['key'=>'service','label'=>__('Service','book-appointment-wp-floating-widget'),'type'=>'select','placeholder'=>'Haircut, Coloring, Styling','required'=>true,'enabled'=>true],
                ['key'=>'visit_time','label'=>__('Visit Time','book-appointment-wp-floating-widget'),'type'=>'datetime-local','placeholder'=>'','required'=>true,'enabled'=>true],
                ['key'=>'description','label'=>__('Notes','book-appointment-wp-floating-widget'),'type'=>'textarea','placeholder'=>'','required'=>false,'enabled'=>true],
            ],
        ],
        'spa' => [
            'label' => __('Spa Center', 'book-appointment-wp-floating-widget'),
            'fields' => [
                ['key'=>'name','label'=>__('Name','book-appointment-wp-floating-widget'),'type'=>'text','placeholder'=>'','required'=>true,'enabled'=>true],
                ['key'=>'mobile','label'=>__('Mobile Number','book-appointment-wp-floating-widget'),'type'=>'tel','placeholder'=>'+11234567890','required'=>true,'enabled'=>true],
                ['key'=>'package','label'=>__('Package','book-appointment-wp-floating-widget'),'type'=>'select','placeholder'=>'Massage, Facial, Body Scrub','required'=>true,'enabled'=>true],
                ['key'=>'visit_time','label'=>__('Visit Time','book-appointment-wp-floating-widget'),'type'=>'datetime-local','placeholder'=>'','required'=>true,'enabled'=>true],
                ['key'=>'description','label'=>__('Notes','book-appointment-wp-floating-widget'),'type'=>'textarea','placeholder'=>'','required'=>false,'enabled'=>true],
            ],
        ],
    ];
}


