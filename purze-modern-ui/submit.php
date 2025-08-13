<?php
// Simple PHP form handler suitable for shared hosting.
// Save this file next to index.html and point JS to submit.php.

header('Content-Type: application/json; charset=UTF-8');

// Basic CORS allowance for same-origin
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['success' => false, 'message' => 'Method not allowed']);
	exit;
}

function resp($ok, $msg, $errors = []){
	echo json_encode(['success'=>$ok,'message'=>$msg,'errors'=>$errors]);
	exit;
}

function sanitize_text($v){ return trim(filter_var($v, FILTER_SANITIZE_STRING)); }
function sanitize_email_addr($v){ return trim(filter_var($v, FILTER_SANITIZE_EMAIL)); }
function normalize_phone($v){ return preg_replace('/[^0-9+\-\s]/','', $v); }
function digits($v){ return preg_replace('/\D+/','', $v); }

// Honeypot
if (!empty($_POST['website'] ?? '')) {
	resp(false, 'Spam detected.');
}

// Rate limit (5/min/IP)
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$rl_dir = __DIR__ . '/_rate';
if (!is_dir($rl_dir)) { @mkdir($rl_dir, 0775, true); }
$rl_file = $rl_dir . '/rl_' . md5($ip) . '.txt';
$hits = 0;
if (file_exists($rl_file)) {
	$raw = @file_get_contents($rl_file);
	$parts = explode('|', $raw);
	$ts = (int)($parts[0] ?? 0);
	$cnt = (int)($parts[1] ?? 0);
	if (time() - $ts < 60) { $hits = $cnt; } else { $hits = 0; }
}
$hits++;
@file_put_contents($rl_file, time() . '|' . $hits, LOCK_EX);
if ($hits > 5) {
	resp(false, 'Too many submissions. Please try later.');
}

$name = sanitize_text($_POST['name'] ?? '');
$email = sanitize_email_addr($_POST['email'] ?? '');
$phone = normalize_phone($_POST['phone'] ?? '');
$service = sanitize_text($_POST['service'] ?? '');
$message = trim($_POST['message'] ?? '');
$human = isset($_POST['human']);

$errors = [];
if ($name === '') { $errors['name'] = 'Name is required.'; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors['email'] = 'Valid email is required.'; }
if ($phone === '' || strlen(digits($phone)) < 7) { $errors['phone'] = 'Valid phone is required (7+ digits).'; }
if ($service === '') { $errors['service'] = 'Please select a service.'; }
if (!$human) { $errors['human'] = 'Please confirm you are human.'; }
if (!empty($errors)) { resp(false, 'Please correct the errors and try again.', $errors); }

// Email settings
$site_name = 'Purze Cleaning Services';
$to = 'your-admin@example.com'; // TODO: change to your recipient(s), comma-separated allowed
$from_name = 'Purze';
$from_email = 'no-reply@example.com'; // Use a domain-verified mailbox on Hostinger
$subject = 'New Lead — ' . $site_name;

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/html; charset=UTF-8';
$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
$headers[] = 'Reply-To: ' . $email;

$body = '<div style="font-family:Arial,sans-serif; font-size:14px;">'
	. '<h2 style="margin:0 0 10px;">' . htmlspecialchars($subject) . '</h2>'
	. '<p><strong>Name:</strong> ' . htmlspecialchars($name) . '</p>'
	. '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>'
	. '<p><strong>Phone:</strong> ' . htmlspecialchars($phone) . '</p>'
	. '<p><strong>Service:</strong> ' . htmlspecialchars($service) . '</p>'
	. ($message !== '' ? '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($message)) . '</p>' : '')
	. '<p style="color:#6b7280; font-size:12px;">' . gmdate('c') . '</p>'
	. '</div>';

$sent = @mail($to, $subject, $body, implode("\r\n", $headers));
if (!$sent) {
	resp(false, 'Unable to send email at the moment. Please try later.');
}

// Optionally store to a local CSV file
$csv_dir = __DIR__ . '/_data';
if (!is_dir($csv_dir)) { @mkdir($csv_dir, 0775, true); }
$csv_file = $csv_dir . '/leads.csv';
$fp = @fopen($csv_file, 'a');
if ($fp) {
	@fputcsv($fp, [gmdate('c'), $name, $email, $phone, $service, $message]);
	@fclose($fp);
}

resp(true, 'Thank you! We will be in touch shortly.');