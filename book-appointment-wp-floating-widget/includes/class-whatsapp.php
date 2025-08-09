<?php
if (!defined('ABSPATH')) {
    exit;
}

class BAW_Whatsapp {
    public static function build_message_from_template($template, $data) {
        if (empty($template)) {
            $template = 'New Appointment:%0AName: {name}%0AMobile: {mobile}%0AVisit Time: {visit_time}%0ADetails: {description}';
        }
        $replacements = [];
        foreach ($data as $key => $value) {
            $replacements['{' . $key . '}'] = rawurlencode((string) $value);
        }
        // Ensure placeholders are URL encoded in the final string
        $message = $template;
        foreach ($replacements as $ph => $val) {
            $message = str_replace($ph, $val, $message);
        }
        return $message;
    }

    public static function build_wa_url($phone_number, $message_text) {
        $phone_number = preg_replace('/[^0-9]/', '', (string) $phone_number);
        $base = 'https://wa.me/' . $phone_number;
        if (!empty($message_text)) {
            // If not already encoded, encode now
            $query = 'text=' . (strpos($message_text, '%') !== false ? $message_text : rawurlencode($message_text));
            return $base . '?' . $query;
        }
        return $base;
    }
}


